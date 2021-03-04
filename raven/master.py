import RavenManager
import psycopg2
import requests

class Master(RavenManager.Raven):
    def __init__(self, config):
        self.type = 'master'
        self.config = config
        print("initialised as master node")
        self.status = None
        sqlUserName = self.config['DATABASE']['username']
        sqlHost = self.config['DATABASE']['hostname']
        sqlPassword = self.config['DATABASE']['password']
        sqlDatabaseName = self.config['DATABASE']['database']
        self.connection = psycopg2.connect(host=sqlHost, user=sqlUserName, password=sqlPassword, database=sqlDatabaseName)
        self.cursor = self.connection.cursor()
        if self.cursor.connection:
            self.connectionWorks = True
        else:
            self.connectionWorks = False
            print("Connection failed. Exiting.")
            exit(0)

    def __del__(self):
        print('Destroying master node.')
        self.cursor.close()
        self.connection.close()

    def getLifeStatus(self):
        ''' 
        Checks if something is up and if it is, refreshes the timestamp
        in the database so it can be selected.
        '''
        try:
            sql = "SELECT * FROM raven_servers"
            self.cursor.execute(sql)
            servers = self.cursor.fetchall()
            for row in servers:
                # Get 
                server_id = row[0]
                ip = row[2]
                port = row[3]
                ping_url = 'http://' + ip + ':' + port
                r = requests.head(ping_url)
                
                # Update status
                if r.status_code == 200:
                    update_query = "UPDATE raven_servers SET last_heartbeat = NOW() WHERE id = %s"
                    self.cursor.execute(update_query, (server_id))
                    self.connection.commit()
        except:
            print("Oh no")

    def balanceContent(self, count=3):
        '''
        Balances content across nodes. Aims to get a number of copies around the place
        as specified using count. 
        '''
        pass 

    def verifyContent(self):
        pass 

    def updateAllStatus(self):
        '''
        Was this meant to be different from getLifeStatus()?
        '''
        self.getLifeStatus() 
    
    def retireNode(self, server_id):
        '''
        Retires a content node. Marks it as unavailable/retiring, and moves 
        its content elsewhere.
        '''
        pass