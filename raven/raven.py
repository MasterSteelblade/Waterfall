import psycopg2
import configparser
import socket
import platform
import psutil
import requests
import pyvidia
import uuid
import RavenManager
import logging
import sentry_sdk
from sentry_sdk.integrations.logging import LoggingIntegration
from flask import Flask
from flask import request
from flask import Response
from sentry_sdk.integrations.flask import FlaskIntegration
from PIL import Image
import hashlib 
import datetime
import os
import random
import asyncio
import ffmpeg
from werkzeug.utils import secure_filename
from waitress import serve
import multiprocessing
import copy 
import time

def checkHardwareScore():
    # Generate an arbitrary score for hardware.
    # Start with RAM. Returns in bytes.
    mem = psutil.virtual_memory()
    memory = mem[0] / (1024*1024)
    # Memory is now MB.
    cpufreq = psutil.cpu_freq()
    maxfreq = cpufreq.max
    cpuScore = psutil.cpu_count() * maxfreq  # Includes threads, add logical=False as arg if we change our mind
    tempScore = cpuScore + memory
    load = psutil.getloadavg()
    if load[2] == 0.0:
        loadVal = 10
    else:
        loadVal = load[2]
    loadScore = tempScore / loadVal  # Uses load averaged over 15 mins. If we're overloaded, we don't want to risk this being master
    return loadScore

def allow(request):
    # Checks if the Raven user header is present.
    if request.headers['User-Agent'] == 'Raven' and request.headers['Verify-Key'] == verifyKey:
        return True
    else:
        abort(404)

configFile = ['ravenconf.ini']
config = configparser.ConfigParser()
configData = config.read(configFile)
if len(configData) != len(configFile): 
    # Assume first run.
    print("No config detected - assuming new server.")
    config['SERVER'] = {}
    config['DATABASE'] = {}
    config['SENTRY'] = {}
    while True:
        sqlHost = input("SQL IP/Hostname:")
        if not sqlHost:
            print("No hostname entered")
            continue
        else:
            break
    while True:
        sqlDatabaseName = input("Database Name:")
        if not sqlDatabaseName:
            print("no name entered")
            continue
        else:
            break
    while True:
        sqlUserName = input("userName:")
        if not sqlUserName:
            print("no name entered")
            continue
        else:
            break
    while True:
        sqlPassword = input("SQL Password:")
        if not sqlPassword:
            print("no password")
            continue
        else:
            break
    connection = psycopg2.connect(host=sqlHost, user=sqlUserName, password=sqlPassword, database=sqlDatabaseName)
    cursor = connection.cursor()
    if cursor.connection:
        connectionWorks = True
    else:
        connectionWorks = False
        print("Connection failed. Exiting.")
        exit(0)
    if connectionWorks:
        config['DATABASE']['username'] = str(sqlUserName)
        config['DATABASE']['password'] = str(sqlPassword)
        config['DATABASE']['hostname'] = str(sqlHost)
        config['DATABASE']['database'] = str(sqlDatabaseName)
        print("What type of server is this?")
        print("1. Content")
        print("2. Transcode [LEGACY, DO NOT USE]")
        print("9. Master [LEGACY, DO NOT USE]")
        print("Note: If designated master role, if a master already exists in the pool, this will just be idle as a backup")
        inp = int(input("Enter a number: "))
        if inp == 1:
            role = "content"
        elif inp == 9:
            role = "master"
        else:
            print("Invalid input. Assuming content server.")
            role = "content"
        if role == "content":
            serverBaseDir = input("Enter the base directory for content, with trailing slash: [/var/www/content/]")
            if not serverBaseDir:
                serverBaseDir = "/var/www/content/"
            config['SERVER']['baseDir'] = serverBaseDir
        config['SERVER']['serverRole'] = role
        serverIP = input("IP address: ")
        serverPort = input("IP detected as "+ serverIP + ", specify port: [6968]")
        if not serverPort:
            serverPort = 6968
        sentryKey = input("Enter Sentry DSN:")
        if not sentryKey:
            print("ENTER A SENTRY DSN BEFORE LAUNCHING PROPERLY.")
            sentryKey = 'no'
        config['SENTRY']['DSN'] = sentryKey

        verifyKey = uuid.uuid4()
        config['SERVER']['verifyKey'] = str(verifyKey)
        config['SERVER']['serverIP'] = serverIP
        config['SERVER']['serverPort'] = str(serverPort)
        cursor.execute("SELECT * FROM raven_servers")
        rows = cursor.fetchall()
        serverID = len(rows) + 1
        print("This server's ID is " + str(serverID))
        config['SERVER']['serverID'] = str(serverID)
        serverName = input("Name to refer to this server as: [" + role + str(serverID) + "]")
        if not serverName:
            serverName = str(role + str(serverID))
        config['SERVER']['serverName'] = str(serverName)
        if any(platform.win32_ver()):
            config['SERVER']['serverOS'] = 'Windows'
        else:
            config['SERVER']['serverOS'] = 'Linux'
        with open('ravenconf.ini', 'w') as configurationFile:
            config.write(configurationFile)
        print("Config file written. Attempting database entry.")
        hwScore = checkHardwareScore()
        print("By the way, hardware score is " + str(hwScore))
        if any(platform.win32_ver()):
            disk = psutil.disk_usage('C:')
        else:
            disk = psutil.disk_usage(serverBaseDir)
        freeDisk = disk.free / 1024  # Should be KB, in theory
        totalDisk = disk.total / 1024
        sql = "INSERT INTO raven_servers (id, server_name, server_ip, server_port, server_role, storage_available, storage_total, hardware_score, verify_key) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)"
        cursor.execute(sql, (serverID, serverName, serverIP, serverPort, role, freeDisk, totalDisk, hwScore, str(verifyKey)))
        connection.commit()
        print("Database entry written. You can now relaunch Raven.")
        cursor.close()
        connection.close()
        exit(0)

else:
    # File exists.
    serverID = config['SERVER']['serverID']
    serverName = config['SERVER']['serverName']
    serverRole = config['SERVER']['serverRole']
    serverIP = config['SERVER']['serverIP']
    serverPort = config['SERVER']['serverPort']
    verifyKey = config['SERVER']['verifyKey']
    baseDir = config['SERVER']['baseDir']
    sentry_logging = LoggingIntegration(
        level=logging.INFO,        # Capture info and above as breadcrumbs
        event_level=logging.WARNING  # Send errors as events
    )
    sentry_sdk.init(
        dsn=config['SENTRY']['DSN'],
        integrations=[FlaskIntegration()],
        traces_sample_rate=1.0
    )
    serverHWScore = checkHardwareScore()
    ravenObj = RavenManager.Raven()
    ravenObj.factory(serverRole, config)
    #Raven.factory(serverRole, config)
    from flask import Flask
    from flask import abort
    from flask import jsonify
    app = Flask(__name__)

    # ==============================
    # ==== RAVEN CORE FUNCTIONS ====
    # ==============================
    @app.route('/raven')
    @app.route('/raven/test')
    def index():
        if allow(request):
            return "yay"

    @app.route('/health')
    def health():
        if allow(request):
            return jsonify(
                status='Alive'
            )
        else:
            abort(404)

    @app.route('/raven/status/map')
    def serverMapStatus():
        if request.headers['User-Agent'] == 'ModPanelRavenPingAgentAYYYY' and request.headers['Verify-Key'] == verifyKey:
            return jsonify(
                status=ravenObj.status,
                role=serverRole,
                name=serverName,
                ID=serverID,
                IP=serverIP,
                port=serverPort,
                hwScore=checkHardwareScore()
            )
        else:
            abort(404)

    @app.route('/raven/status/master')
    def serverMasterStatus():
        if request.headers['User-Agent'] == 'RavenMasterPingAgentAYYYY' and request.headers['Verify-Key'] == verifyKey:
            return jsonify(
                status=ravenObj.status,
                role=serverRole,
                name=serverName,
                ID=serverID,
                IP=serverIP,
                port=serverPort,
                hwScore=checkHardwareScore()
            )
        else:
            abort(404)

    @app.route('/raven/status')
    def getStatus():
        if (request.headers['User-Agent'] == 'ModPanelRavenPingAgentAYYYY' and request.headers['Verify-Key'] == verifyKey) or 1 == 1:
            proc = psutil.Process()
            return jsonify(
                hwScore=checkHardwareScore(),
                role = serverRole,
                name = serverName,
                ID=serverID,
                IP=serverIP,
                port=serverPort,
                roleData = ravenObj.child.getStatus(),
                openFiles = proc.open_files()
            )
        else:
            abort(404)
    
    @app.route('/raven/reset')
    def resetStatus():
        if (request.headers['User-Agent'] == 'ModPanelRavenPingAgentAYYYY' and request.headers['Verify-Key'] == verifyKey) or 1 == 1:
            if ravenObj.child.type == 'content':
                ravenObj.child.resetTranscodeCount()
                return jsonify(
                    success=True
                )
        else:
            abort(404)

    @app.route('/raven/getrole')
    def getRole():
        if ravenObj.child is not None:
            return jsonify(
                role =ravenObj.child.type
            )
        elif ravenObj.master is not None:
            return jsonify(
                role = ravenObj.master.type
            )
        else:
            return jsonify(
                role = 'None'
            )

    # ==============================
    # === RAVEN MASTER FUNCTIONS ===
    # ==============================



    # ======================================
    # === RAVEN CONTENT SERVER FUNCTIONS ===
    # ======================================
    @app.route('/image/add', methods=['POST'])
    def addNewImage():
        start_time = time.time()
        if ravenObj.child is not None:
            if ravenObj.child.type == 'content':
                for file in request.files.getlist('images'):
                    st = time.time()
                    sname = ''
                    for _ in range(25):
                        # Considering only upper and lowercase letters
                        random_integer = random.randint(97, 97 + 26 - 1)
                        flip_bit = random.randint(0, 1)
                        # Convert to lowercase if the flip bit is on
                        random_integer = random_integer - 32 if flip_bit == 1 else random_integer
                        # Keep appending random characters using chr(x)
                        sname += (chr(random_integer))
                    file.save('tmp/' + sname)
                    
                    imFile = 'tmp/' + sname
                    image = Image.open(imFile)

                    try: 
                        image.verify()
                    except:
                        image.close()
                        return jsonify(
                            status = 'failure',
                            reason = "Not a valid image"
                        )

                    tmpSize = image.size 
                    imgSize = (copy.deepcopy(tmpSize[0]), copy.deepcopy(tmpSize[1]))
                    image.close()
                    begin_time = (time.time() - st)
                    manager = multiprocessing.Manager()
                    jobs = []
                    try:
                        imgData = manager.dict()
                        imgDat = {}
                        imgDat['url'] = {}
                        imgDat['legacy'] = {}
                        date = datetime.datetime.now()
                        datehash = hashlib.md5(str(date).encode('utf-8')).hexdigest()
                        dirwithint = datehash + str(random.randrange(1, 999999))
                        directory = hashlib.md5(str(dirwithint).encode('utf-8')).hexdigest()
                        os.mkdir(baseDir + 'images/' + directory)
                        isArt = request.form.get("isArt")
                        isAudio = request.form.get("isAudio")
                        widths = []
                        isAvatar = request.form.get("isAvatar")
                        if isAvatar == '1':
                            widths = [(16,16), (32,32), (64,64), (128,128), (256,256), (512,512)]
                        elif isAudio == '1':
                            widths = [(50,50), (100,100), (200,200)]
                        elif isArt == '1':
                            artWidth, artHeight = imgSize
                            if artWidth > 8192 or artHeight > 8192:
                                artSize = (8192,8192)
                            else:
                                artSize = (artWidth, artHeight)
                            
                            widths.append(artSize)
                        else:
                            if imgSize[0] < 1280 and imgSize[0] > 740:
                                widths.append((imgSize[0], imgSize[1]))
                            else:
                                widths = []
                        for widthTest in [(1280, 4096), (810, 4096), (540, 4096), (300, 4096)]:
                            if (imgSize[0] > widthTest[0] or len(widths) == 0) and isAvatar != '1' and isAudio != '1':
                                widths.append(widthTest)
    
                        del imgSize
                        for size in widths:
                            p = multiprocessing.Process(target=ravenObj.child.processImage, args=(size, imFile, isAvatar, imgData, baseDir, directory, datehash)) 
                            jobs.append(p)
                            p.start()
                        for proc in jobs:
                            proc.join()    

                    except Exception as e:
                        pass
                    finally:
                        os.remove('tmp/' + sname)

                ravenObj.child.updateFreeDiskSpace()
                return jsonify(
                    status = 'success',
                    onServer = serverID,
                    imgData = imgData.copy(),
                    executionTime = (time.time() - start_time),
                    beginTime = begin_time
                )
            else:
                return jsonify(
                    status = 'failed',
                    error = 'Not a content server'
                )
        else:
            return jsonify(
                status = 'failed',
                error = 'Not initialised'
            )




    @app.route('/video/add', methods=['POST'])
    def addNewVideo():
        if ravenObj.child is not None:
            if ravenObj.child.type == 'content':
                
                for file in request.files.getlist('video'):
                    videoID = request.form.get('videoID')
                    skip = False
                    if request.form.get('doNotUpdatePost') is not None:
                        skip = True
                    sname = ''
                    if request.form.get('postType') is not None:
                        postType = request.form.get('postType')
                    else: 
                        postType = 'posted'
                    for _ in range(25):
                        # Considering only upper and lowercase letters
                        random_integer = random.randint(97, 97 + 26 - 1)
                        flip_bit = random.randint(0, 1)
                        # Convert to lowercase if the flip bit is on
                        random_integer = random_integer - 32 if flip_bit == 1 else random_integer
                        # Keep appending random characters using chr(x)
                        sname += (chr(random_integer))
                    file.save('tmp/' + sname)
                    response = jsonify(
                        onServer = serverID,
                        status = 'success'
                    )
                    @response.call_on_close
                    def on_close():
                        ravenObj.child.transcodeVideo(sname, videoID, skip, postType)
                        ravenObj.child.updateFreeDiskSpace()

                return response
    
    @app.route('/audio/add', methods=['POST'])
    def addNewAudio():
        if ravenObj.child is not None:
            if ravenObj.child.type == 'content':
                for file in request.files.getlist('audio'):
                    audioID = request.form.get('audioID')

                    sname = ''
                    for _ in range(25):
                        # Considering only upper and lowercase letters
                        random_integer = random.randint(97, 97 + 26 - 1)
                        flip_bit = random.randint(0, 1)
                        # Convert to lowercase if the flip bit is on
                        random_integer = random_integer - 32 if flip_bit == 1 else random_integer
                        # Keep appending random characters using chr(x)
                        sname += (chr(random_integer))
                    file.save('tmp/' + sname)
                    date = datetime.datetime.now()
                    datehash = hashlib.md5(str(date).encode('utf-8')).hexdigest()
                    dirwithint = datehash + str(random.randrange(1, 999999))
                    directory = hashlib.md5(str(dirwithint).encode('utf-8')).hexdigest()
                    try:
                        probe = ffmpeg.probe('tmp/' + sname)
                        audio_stream = next((stream for stream in probe['streams'] if stream['codec_type'] == 'audio'), None)
                        duration = audio_stream['duration']
                        codecType = audio_stream['codec_name'] # If MP3, could copy wholesale
                    except:
                        print(':(')
                        return jsonify(
                            status = 'failed',
                        )    
                    if audio_stream is not None:
                        os.mkdir(baseDir + 'audio/' + directory)
                        retName = 'audio/%s/waterfall_%s.mp3'% (directory, datehash)
                        out_name = baseDir + retName
                        stream = ffmpeg.input('tmp/' + sname)
                        response = jsonify(
                            status = 'success',
                            duration = duration
                        )
                        @response.call_on_close
                        def on_close():
                            try:
                                ffmpeg.output(stream, out_name).run()
                            except:
                                print(':(')
                                return jsonify(
                                    status = 'failed',
                                )

                            os.remove('tmp/' + sname)
                            path = retName
                            with open(baseDir + retName, 'rb') as fh:
                                m = hashlib.md5()
                                while True:
                                    data = fh.read(8192)
                                    if not data:
                                        break
                                    m.update(data)
                                md5 = m.hexdigest()
                            ravenObj.child.updateFreeDiskSpace()
                            ravenObj.child.markAudioComplete(audioID, path, md5)
                return response
                   

if __name__ == "__main__":
    serve(app, host='0.0.0.0', port=6968, threads=250, connection_limit=250)
