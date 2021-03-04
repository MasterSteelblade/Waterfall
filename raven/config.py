'''import requests
import pymysql
import configparser
import psutil

configFile = ['raven.ini']
config = configparser.ConfigParser()
configData = config.read(configFile)
if len(configData) != len(configFile):
    # Assume first run.
    print("No config detected - assuming new server.")
    config['SERVER'] = {}
    config['DATABASE'] = {}
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
    connection = pymysql.connect(sqlHost, sqlUserName, sqlPassword, sqlDatabaseName)
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
        print("1. Web")
        print("2. Content")
        print("3. Transcode")
        print("9. Master")
        print("Note: If designated master role, if a master already exists in the pool, this will just be idle as a backup")
        inp = int(input("Enter a number: "))
        if inp == 1:
            role = "web"
        elif inp == 2:
            role = "content"
        elif inp == 3:
            role = "transcode"
        elif inp == 9:
            role = "master"
        else:
            print("Invalid input. Assuming content server.")
            role = "content"
        config['SERVER']['serverRole'] = role
        serverIP = requests.get('https://api.ipify.org').text

        config['SERVER']['serverIP'] = serverIP
        cursor.execute("SELECT * FROM RavenServers")
        rows = cursor.fetchall()
        serverID = len(rows) + 1
        print("This server's ID is " + str(serverID))
        config['SERVER']['serverID'] = str(serverID)
        serverName = input("Name to refer to this server as: [" + role + str(serverID) + "]")
        if not serverName:
            serverName = str(role + str(serverID))
        config['SERVER']['serverName'] = str(serverName)
        with open('raven.ini', 'w') as configurationFile:
            config.write(configurationFile)
        print("Config file written. Attempting database entry.")
        hwScore = checkHardwareScore()
        print("By the way, hardware score is " + str(hwScore))
        disk = psutil.disk_usage('C:')
        freeDisk = disk.free / 1024  # Should be KB, in theory
        sql = "INSERT INTO RavenServers (`ID`, `serverName`, `serverIP`, `serverRole`, `storageAvailable`, `hardwareScore`) VALUES (%s, %s, %s, %s, %s, %s)"
        cursor.execute(sql, (serverID, serverName, serverIP, role, freeDisk, hwScore))
        connection.commit()
        print("Database entry written. You can now relaunch Raven.")

        exit(0)
'''