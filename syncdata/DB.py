#! /usr/bin/env python
# coding=utf-8
import time
from datetime import datetime as d

import _mysql
import mysql.connector as connector

def dict_to_sql(data_dict):
    sql = ''
    for one_data in data_dict:
        sql += '('
        one_data = one_data.items()
        for (key, param) in one_data:
            if isinstance(param, str):
                sql+= '\'' + _mysql.escape_string(param) + '\','
            else:
                sql += str(param) + ','
        sql = sql[:-1] + '),'
    return sql

def list_to_sql(data_list):
    sql = ''
    for one_data in data_list:
        sql += '('
        for param in one_data:
            if isinstance(param, str):
                sql+= '\'' + _mysql.escape_string(param) + '\','
            else:
                sql += str(param) + ','
        sql = sql[:-1] + '),'
    return sql


class Server(object):

    def __init__(self, host, user, password, database):
        self.config = {
            'host': host,
            'user': user,
            'password': password,
            'database': database,
        }
        self.__con = None

    def connect(self):
        start_time = time.time()
        self.__con = connector.connect(**self.config)
        return time.time() - start_time

    def __get_cursor(self):
        if self.__con.is_connected():
            return self.__con.cursor()
        else:
            self.connect()
            if self.__con.is_connected():
                return self.__con.cursor()
        raise connector.Error(str(self.config.get('host')) + ' is not available now')

    def replace_many_rows(self, data, table_name):
        start_time = time.time()
        cursor = self.__get_cursor()
        if cursor is not None:
            sql = 'REPLACE INTO %s VALUES' % table_name
            if isinstance(data[0], dict):
                sql += dict_to_sql(data)
            else:
                sql += list_to_sql(data)
            sql = sql[:-1] + ';'
            cursor.execute(sql)
            self.__con.commit()
            cursor.close()
            return time.time() - start_time
        else:
            raise Exception('No connection to db')

    def load_data(self):
        try:
            cursor = self.__get_cursor()
            if cursor is not None:
                sql = 'SELECT * from DATA'
                cursor.execute(sql)
                rows = cursor.fetchall()
                # if rows is not None:
                # for row in rows:
                # print(row)
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
            cursor = self.__get_cursor()
            if cursor is not None:
                sql = 'INSERT INTO DATA VALUES (%s, %s, %s)'
                cursor.executemany(sql, data)
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
