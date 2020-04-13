#! /usr/bin/env python
# coding=utf-8

import mysql.connector as connector

class Server(object):

    def __init__(self, host, user, password, database):
        self.__config = {
            'host':host,
            'user':user,
            'password':password,
            'database':database,
        }
        self.__con = self.__connect()

    def __connect(self):
        try:
            connection = connector.connect(**self.__config)
            #write in log
            return connection
        except connector.Error as e:
            #write to logfile
            print e.message

    def __get_cursor(self):
        try:
            if self.__con.is_connected():
                return self.__con.cursor()
            else:
                self.__connect()
                if self.__con.is_connected():
                    return self.__con.cursor()
        except connector.Error as e:
            print e.message

    def load_data(self):
        try:
            if self.__con.is_connected():
                cursor = self.__get_cursor()
                sql = 'SELECT * from DATA'
                if cursor.execute(sql) > 0:
                    for row in cursor.fetchall():
                        print(row)
                else:
                    print('Nothing here')
                cursor.close()
        except connector.Error as e:
            print e.message

    def insert_data(self, data):
        try:
            if self.__con.is_connected():
                cursor = self.__get_cursor()
                sql = 'INSERT INTO DATA VALUES (%s, %s, %s)'
                cursor.execute(sql, data)
                self.__con.commit()
                cursor.close()
        except connector.Error as e:
            print e.message

    def insert_many(self, data):
        try:
            if self.__con.is_connected():
                cursor =  self.__get_cursor()
                sql = 'INSERT INTO DATA VALUES (%s, %s, %s)'
                cursor.executemany(sql, data)
                self.__con.commit()
                cursor.close()
        except connector.Error as e:
            print e.message

    def insert_and_load(self, data):
        try:
            if self.__con.is_connected():
                cursor = self.__get_cursor()
                sql = """
                    INSERT INTO DATA VALUES (%s, %s, %s);
                    SELECT * FROM DATA;
                    """
                cursor.execute(sql, data)
                for row in cursor.fetchall():
                    print row
                self.__con.commit()
        except connector.Error as e:
            print e.message

    def delete_all(self):
        try:
            if self.__con.is_connected():
                cursor = self.__get_cursor()
                sql = 'TRUNCATE TABLE DATA'
                cursor.execute(sql)
                self.__con.commit()
                cursor.close()
        except connector.Error as e:
            print e.message




