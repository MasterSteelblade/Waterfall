import psycopg2

class Raven:
    def __init__(self):
        print("initialised raven core")
        self.master = None
        self.status = None
        self.child = None
        
    def factory(self, role, config):
        print(self)
        print(role)
        print(config)
        #role = self.checkForMaster(role, config)
        if role == "content":
            import content
            self.child = content.Content(config)
            print('Content')
        '''if role == "transcode":
            import transcode
            self.child = transcode.Transcode(config)
            print('transcode')'''
        if role == "master":
            import master
            self.child = None
            self.master = master.Master(config)
            print('master')

    def checkForMaster(self, role, config):

        sqlHost = config['DATABASE']['hostname']
        sqlUserName = config['DATABASE']['username']
        sqlPassword = config['DATABASE']['password']
        sqlDatabaseName = config['DATABASE']['database']
        connection = psycopg2.connect(host=sqlHost, user=sqlUserName, password=sqlPassword, database=sqlDatabaseName)
        cursor = connection.cursor()
        cursor.execute("SELECT * FROM raven_servers WHERE current_master = 1")
        rows = cursor.fetchall()
        master = rows[0]
        masterIP = master['serverIP']
        if self.heartbeatMaster(masterIP) is True:
            return role
        else:
            print("Master heartbeat failed. Promoting this host to master.")
            return 'master'

    def heartbeatMaster(self, masterIP):
        pass