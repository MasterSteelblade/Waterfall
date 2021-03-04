import RavenManager
import psutil
import platform 
from PIL import Image
from colorthief import ColorThief
import hashlib 
import datetime
import random
import asyncio
import ffmpeg
import os
from werkzeug.utils import secure_filename
import psycopg2 
import json
import psutil
import pyvidia
import time
from multiprocessing import Pool 
from shutil import copyfile

class Content(RavenManager.Raven):
    def __init__(self, config):
        # Make folders. 
        self.baseDir = config['SERVER']['baseDir']
        if not os.path.exists('tmp'):
            os.makedirs('tmp')
        if not os.path.exists(self.baseDir + 'images'):
            os.makedirs(self.baseDir + 'images')
        if not os.path.exists(self.baseDir + 'audio'):
            os.makedirs(self.baseDir + 'audio')
        if not os.path.exists(self.baseDir + 'videos'):
            os.makedirs(self.baseDir + 'videos')
        if not os.path.exists(self.baseDir + 'avatars'):
            os.makedirs(self.baseDir + 'avatars')
        self.config = config
        self.type = 'content'
        self.fast_storage = None
        self.cpu_count = psutil.cpu_count()
        self.max_transcodes = int(self.cpu_count / 4) - 1
        self.transcode_threads = 4
        if any(platform.win32_ver()):
            disk = psutil.disk_usage('C:')
        else:
            disk = psutil.disk_usage(self.baseDir)
        self.freeDisk = disk.free / 1024  # Should be KB, in theory
        self.transcodingCount = 0
        self.acceptingwork = 0
        self.prepare()
        self.sqlUserName = self.config['DATABASE']['username']
        self.sqlHost = self.config['DATABASE']['hostname']
        self.sqlPassword = self.config['DATABASE']['password']
        self.sqlDatabaseName = self.config['DATABASE']['database']
        self.connection = psycopg2.connect(host=self.sqlHost, user=self.sqlUserName, password=self.sqlPassword, database=self.sqlDatabaseName)
        self.cursor = self.connection.cursor()
        self.failedList = []
        if self.cursor.connection:
            self.connectionWorks = True
        else:
            self.connectionWorks = False
            print("Connection failed. Exiting.")
            exit(0)
    

    def __del__(self):
        print('Destroying content node.')
        self.cursor.close()
        self.connection.close()

    def updateFreeDiskSpace(self):
        if any(platform.win32_ver()):
            disk = psutil.disk_usage('C:')
        else:
            disk = psutil.disk_usage(self.baseDir)
        self.freeDisk = disk.free / 1024
        self.totalDisk = disk.total / 1024
        sql = "UPDATE raven_servers SET storage_available = %s, storage_total = %s WHERE id = %s"
        self.cursor.execute(sql, (self.freeDisk, self.totalDisk, self.config['SERVER']['serverID']))


    #####################################################
    ################### IMAGES ##########################
    #####################################################


    def getColourPalette(self, path):
        """
        Gets a list of the four most dominant colours in the image.
        """
        image = ColorThief(path)
        try:
            image = ColorThief(path)
            palette = image.get_palette(color_count=4)
            del image
            return palette
        except:
            del image
            return [[0,0,0], [255,0,0], [0,255,0], [0,0,255]]

    def getDiskStatus(self):
        if any(platform.win32_ver()):
            disk = psutil.disk_usage('C:')
        else:
            disk = psutil.disk_usage('/')
        status = {'total': disk.total, 'used': disk.used, 'free': disk.free, 'percent': disk.percent}
        return status
     

    def processImage(self, size, imFile, isAvatar, imgData, baseDir, directory, datehash):
        image = Image.open(imFile)
        imgDat = {}
        imgDat['url'] = {}
        thisTime = time.time()
        if image.format == 'GIF' or image.format == 'gif':
            resTime = time.time()
            # allFrames = self.extractAndResizeFrames(imFile, size)
            imgDat['resize'] = (time.time() - resTime)
            retName = 'images/%s/waterfall_%s_%s.gif' % (directory, datehash, size[0])
            thumbName = baseDir + retName
            copyfile(imFile, thumbName)
            image.close()

            
            imgDat['url']['legacy'] = retName
            imgDat['url']['modern'] = retName
            with open(thumbName, 'rb') as fh:
                m = hashlib.md5()
                while True:
                    data = fh.read(8192)
                    if not data:
                        break
                    m.update(data)
                imgDat['md5'] = m.hexdigest()
                imgDat['md5p'] = m.hexdigest()
                fh.close()
                
        else:
            resTime = time.time()
            if image.mode== 'CMYK' and image.mode != 'RGBA':
                image = image.convert('RGBA')
            image.thumbnail(size)

            imgDat['resize'] = (time.time() - resTime)
            retName = 'images/%s/waterfall_%s_%s.webp' % (directory, datehash, size[0])
            thumbName = baseDir + retName
            image.save(thumbName, quality=100)
            retNameP = 'images/%s/waterfall_%s_%s.png' % (directory, datehash, size[0])
            thumbNameP = baseDir + retNameP
            image.save(thumbNameP, quality=100)

            image.close()

            imgDat['url']['legacy'] = retNameP
            imgDat['url']['modern'] = retName

            with open(thumbName, 'rb') as fh:
                m = hashlib.md5()
                while True:
                    data = fh.read(8192)
                    if not data:
                        break
                    m.update(data)
                imgDat['md5'] = m.hexdigest()
                fh.close()

            with open(thumbNameP, 'rb') as fh:
                m = hashlib.md5()
                while True:
                    data = fh.read(8192)
                    if not data:
                        break
                    m.update(data)
                imgDat['md5p'] = m.hexdigest()
                fh.close()
            
        retSize = size[0] 
        
        imgDat['time'] = (time.time() - thisTime)
        imgData[int(retSize)] = imgDat

    def prepare(self):
        try:
            if pyvidia.get_nvidia_device() is None:
                self.hasGPU = 0
                print("No GPU found")
            else:
                self.hasGPU = 1
                print("GPU found")
        except:
            self.hasGPU = 0
            print("Not on Linux, skipping GPU check")
        self.hasGPU = 0
        self.acceptingWork = 1 

    def startDraining(self):
        self.acceptingWork = 0

    def stopDraining(self):
        self.acceptingWork = 1

    def getStatus(self):
        data = {}
        data['acceptingWork'] = self.acceptingWork
        data['transcodingCount'] = self.transcodingCount
        data['failedList'] = self.failedList 
        return data


    #####################################################
    ################### VIDEOS ##########################
    #####################################################
        
    def markVideoInProgress(self, videoID):
        link = psycopg2.connect(host=self.sqlHost, user=self.sqlUserName, password=self.sqlPassword, database=self.sqlDatabaseName)
        linkCursor = link.cursor()
        if linkCursor.connection:
            pass
        else:
            self.failedList.append(videoID)
            return
        try:
            sql = "UPDATE video SET servers = %s, transcode_status = %s WHERE id = %s"
            linkCursor.execute(sql, ('{' + self.config['SERVER']['serverID'] + '}', 'in_progress', videoID))
            link.commit()
            linkCursor.close()
            link.close()
        except:
            pass 
            # We can probably get away with it...
        self.transcodingCount = self.transcodingCount + 1

    def markVideoFailed(self, videoID, tempFile):
        link = psycopg2.connect(host=self.sqlHost, user=self.sqlUserName, password=self.sqlPassword, database=self.sqlDatabaseName)
        linkCursor = link.cursor()
        if linkCursor.connection:
            pass
        else:
            self.failedList.append(videoID)
            return
        try:
            sql = "UPDATE video SET servers = %s, transcode_status = %s WHERE id = %s"
            linkCursor.execute(sql, ('{' + self.config['SERVER']['serverID'] + '}', 'failed', videoID))
            link.commit()
            linkCursor.close()
            link.close()
        except:
            self.failedList.append(videoID)
        finally:
            self.transcodingCount = self.transcodingCount - 1
            os.remove('tmp/' + tempFile)




    def probeVideo(self, tempFile):
        try:
            probe = ffmpeg.probe('tmp/' + tempFile)
            video_stream = next((stream for stream in probe['streams'] if stream['codec_type'] == 'video'), None)
            audio_stream = next((stream for stream in probe['streams'] if stream['codec_type'] == 'audio'), None)
            if audio_stream is not None:
                audio_present = True
            else:
                audio_present = False
            width = (int(video_stream['width']) / 2)
            height = (int(video_stream['height']) /2)
            try: 
                bitrate = int(video_stream['bit_rate'])
            except:
                bitrate = 3000
            data = {}
            data['video_stream'] = video_stream 
            data['audio_stream'] = audio_stream 
            data['audio_present'] = audio_present
            data['width'] = width 
            data['height'] = height 
            data['bitrate'] = bitrate
            # Maybe something can go here in future to filter out unsuitable qualities. 
            return data
        except ffmpeg.Error:
            return False

    def getScaleData(self, data, quality):
        baseWidth = data['width']
        baseHeight = data['height']
        baseBitrate = data['bitrate']
        width = baseWidth
        height = baseHeight
        bitrate = baseBitrate

        crf = 14
        if quality == 'sq':
            if width > 720:
                multiplier = baseWidth / 720
                height = int(height / multiplier)
                width = 720
            bitrate = int(baseBitrate / 1.5)
            crf = 17
            if bitrate > 5000:
                bitrate = 5000
        elif quality == 'lq':
            if width > 480:
                multiplier = baseWidth / 480
                height = int(height / multiplier)
                width = 480
            bitrate = int(baseBitrate / 2.25)
            crf = 23
            if bitrate > 2500:
                bitrate = 2500
        if (width % 2) != 0:
            width = width + 1
        if (height % 2) != 0:
            height = height + 1
        if bitrate > 10000:
            bitrate = 10000
        data = {}
        data['width'] = width
        data['height'] = height 
        data['bitrate'] = bitrate 
        data['crf'] = crf 
        return data

    def resetTranscodeCount(self):
        self.transcodingCount = 0

    def processTranscode(self, tempFile, scaleData, outName, videoInfo):
        audioPresent =  videoInfo['audio_present']
        bufSize = scaleData['bitrate'] * 2
        if audioPresent == True:
            inputFile = ffmpeg.input('tmp/' + tempFile)
            videoStream = inputFile.video #.filter('scale', scaleData['width'], scaleData['height'])
            audioStream = inputFile.audio 
            joined = ffmpeg.concat(videoStream, audioStream, v=1, a=1).node
            video = joined[0]
            audio = joined[1]
            try:
                ffmpeg.output(video, audio, outName, crf=scaleData['crf'], threads=self.transcode_threads, pix_fmt='yuv420p', acodec='aac',  vcodec='libx264', preset="veryfast").run(capture_stdout=True)
                return True
            except ffmpeg.Error as e:
                print(e.stderr)
                return False
        else: 
            inputFile = ffmpeg.input('tmp/' + tempFile)
            videoStream = inputFile.video #.filter('scale', scaleData['width'], scaleData['height'])
            joined = ffmpeg.concat(videoStream, v=1).node
            video = joined[0]
            try:
                ffmpeg.output(video, outName, crf=scaleData['crf'], threads=self.transcode_threads, pix_fmt='yuv420p', acodec='aac', vcodec='libx264', preset="veryfast").run(capture_stdout=True)
                return True
            except ffmpeg.Error as e:
                return False

    def transcodeVideo(self, tempFile, videoID, skipUpdate, postType):
        print(videoID)
        while self.transcodingCount >= self.max_transcodes:
            time.sleep(random.randint(15,30))
        self.markVideoInProgress(videoID)
        date = datetime.datetime.now()
        datehash = hashlib.md5(str(date).encode('utf-8')).hexdigest()
        dirwithint = datehash + str(random.randrange(1, 999999))
        directory = hashlib.md5(str(dirwithint).encode('utf-8')).hexdigest()
        os.mkdir(self.baseDir + 'videos/' + directory) 
        try:
            videoInfo = self.probeVideo(tempFile)
        except:
            self.markVideoFailed(videoID, tempFile)
            return
        qualityList = ['lq', 'sq', 'hq'] # We'll do them in this order.
        pathList = {}
        for quality in qualityList:
            if quality == 'sq':
                qualityStr = ''
            else:
                qualityStr = '_' + quality
            returnName = 'videos/%s/waterfall_%s%s.mp4'% (directory, datehash, qualityStr)
            outName = self.baseDir + returnName
            try:
                scaleData = self.getScaleData(videoInfo, quality)
                size = scaleData['height']
            except:
                continue
            processed = self.processTranscode(tempFile, scaleData, outName, videoInfo)
            if processed == True:
                pathList[quality] = {}
                with open(outName, 'rb') as fh:
                        m = hashlib.md5()
                        while True:
                            data = fh.read(8192)
                            if not data:
                                break
                            m.update(data)
                        pathList[quality]['md5'] = m.hexdigest()
                pathList[quality]['path'] = returnName
                pathList[quality]['size'] = size
            else: 
                pass
        if len(pathList) == 0:
            self.markVideoFailed(videoID, tempFile)
            return
        else:
            self.markVideoComplete(tempFile, videoID, pathList, skipUpdate, postType)
            return

    def markVideoComplete(self, tempFile, videoID, pathList, skipUpdate, postType):
        link = psycopg2.connect(host=self.sqlHost, user=self.sqlUserName, password=self.sqlPassword, database=self.sqlDatabaseName)
        linkCursor = link.cursor()
        print('Post Type ' + postType)
        if linkCursor.connection:
            pass
        else:
            self.failedList.append(videoID)
            return
        try:
            sql = "UPDATE video SET servers = %s, paths = %s, transcode_status = %s WHERE id = %s"
            linkCursor.execute(sql, ('{' + self.config['SERVER']['serverID'] + '}', json.dumps(pathList), 'complete', videoID))
            link.commit()
            if skipUpdate is False:
                print("Not skipping post update")
                print(videoID)
                sql = "UPDATE posts SET post_status = %s, timestamp = %s WHERE video_id = %s"
                linkCursor.execute(sql, ('posted', datetime.datetime.utcnow(), videoID))
                link.commit()
            for video in list(self.failedList):
                sql = "UPDATE video SET servers = %s, paths = %s, transcode_status = %s WHERE id = %s"
                linkCursor.execute(sql, ('{' + self.config['SERVER']['serverID'] + '}', '{}', 'failed', video))
                link.commit()
                self.failedList.remove(video)

            linkCursor.close()
            link.close()
        except:
            return
        finally:
            self.transcodingCount = self.transcodingCount - 1
            os.remove('tmp/' + tempFile)

    def markAudioComplete(self, audioID, path, md5):
        link = psycopg2.connect(host=self.sqlHost, user=self.sqlUserName, password=self.sqlPassword, database=self.sqlDatabaseName)
        linkCursor = link.cursor()
        if linkCursor.connection:
            pass
        else:
            return
        try:
            sql = "UPDATE audio SET servers = %s, paths = %s, md5 = %s WHERE id = %s"
            linkCursor.execute(sql, ('{' + self.config['SERVER']['serverID'] + '}', '"' +path +'"', md5, audioID))
            sql = "UPDATE posts SET post_status = %s, timestamp = %s WHERE audio_id = %s"
            linkCursor.execute(sql, ('posted', datetime.datetime.utcnow(), audioID))
            link.commit()
        except:
            return
        finally:
            linkCursor.close()
            link.close()