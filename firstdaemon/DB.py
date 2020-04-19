#! /usr/bin/env python
# coding=utf-8

from datetime import datetime as d

import mysql.connector as connector


def check_time(data):
    if isinstance(data, str):
        try:
            d.strptime(data, '%Y-%m-%d %H:%M:%S')
            return True
        except ValueError:
            return False
    else: return False


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
            return connection
        except connector.Error as e:
            #write to logfile
            print e

    def __get_cursor(self):
        try:
            if self.__con.is_connected():
                return self.__con.cursor()
            else:
                self.__connect()
                if self.__con.is_connected():
                    return self.__con.cursor()
            raise connector.Error(str(self.__config.get('host')) + ' is not available now')
        except connector.Error as e:
            print e

    def load_data(self):
        try:
            cursor = self.__get_cursor()
            if cursor is not None:
                sql = 'SELECT * from DATA'
                cursor.execute(sql)
                rows = cursor.fetchall()
                #if rows is not None:
                    #for row in rows:
                        #print(row)
                pass
                cursor.close()
        except connector.Error as e:
            print e

    def insert_data(self, data):
        try:
            cursor = self.__get_cursor()
            if cursor is not None:
                sql = 'INSERT INTO DATA VALUES (%s, %s, %s)'
                cursor.execute(sql, data)
                self.__con.commit()
                cursor.close()
        except connector.Error as e:
            print e

    def insert_many(self, data):
        try:
            cursor =  self.__get_cursor()
            if cursor is not None:
                sql = 'INSERT INTO DATA VALUES (%s, %s, %s)'
                cursor.executemany(sql, data)
                self.__con.commit()
                cursor.close()
        except connector.Error as e:
            print e

    def insert_many_str(self, data):
        try:
            cursor =  self.__get_cursor()
            if cursor is not None:
                sql = 'INSERT INTO DATA VALUES '
                for one_data in data:
                    if not check_time(one_data[1]):
                        one_data[1] = d.now()
                    sql += str(one_data) + ','
                sql = sql[:-1] + ';'
                cursor.execute(sql)
                self.__con.commit()
                cursor.close()
        except connector.Error as e:
            print e

    def delete_all(self):
        try:
            cursor = self.__get_cursor()
            if cursor is not None:
                sql = 'TRUNCATE TABLE DATA'
                cursor.execute(sql)
                self.__con.commit()
                cursor.close()
        except connector.Error as e:
            print e.message



