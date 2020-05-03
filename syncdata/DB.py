#! /usr/bin/env python
# coding=utf-8
import time

import _mysql
import mysql.connector as connector


def dict_to_sql(data_dict):
    sql = ''
    for one_data in data_dict:
        sql += '('
        one_data = one_data.items()
        for (key, param) in one_data:
            if isinstance(param, str):
                sql += '\'' + _mysql.escape_string(param) + '\','
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
                sql += '\'' + _mysql.escape_string(param) + '\','
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
        #Позже брать из файла
        self.last_upload_date = {
            'host':host,
            'last_connection':'2000-01-01',
            'dat':'0001-01-01',
            'logs':'00001-01-01',
            'logs_syncdata':'00001-01-01',
            'dyn':-1,
        }
        self.__con = None
        self.whitelist = []

    def connect(self):
        start_time = time.time()
        self.__con = connector.connect(**self.config)
        stop_time = time.time()
        self.whitelist = [str(table) for (table,) in self.__get_whitelist_table()]
        return stop_time - start_time

    def __get_whitelist_table(self):
        cursor = self.__get_cursor()
        cursor.execute('show tables;')
        return cursor.fetchall()

    def __get_cursor(self):
        if self.__con.is_connected():
            return self.__con.cursor()
        else:
            self.connect()
            if self.__con.is_connected():
                return self.__con.cursor()
        raise connector.Error(str(self.config.get('host')) + ' is not available now')

    def __is_in_table_whitelist(self, table_name):
        return table_name in self.whitelist

    def replace_many_rows(self, data, table_name_with_params):
        start_time = time.time()
        if not self.__is_in_table_whitelist(table_name_with_params.split('(')[0]):
            raise connector.Error('table %s doesn`t exist' % table_name_with_params.split('(')[0].upper())
        cursor = self.__get_cursor()
        sql = 'REPLACE INTO %s VALUES' % table_name_with_params
        if isinstance(data[0], dict):
            sql += dict_to_sql(data)
        else:
            sql += list_to_sql(data)
        sql = sql[:-1] + ';'
        start_time_execute = time.time()
        cursor.execute(sql)
        self.__con.commit()
        cursor.close()
        return time.time() - start_time, time.time() - start_time_execute

    def delete_between_dates(self, date1, date2, table_name, data_column):
        if not self.__is_in_table_whitelist(table_name.split('(')[0]):
            raise connector.Error('table %s doesn`t exist' %table_name.upper())
        cursor = self.__get_cursor()
        if isinstance(date1, str):
            date1 = '\'' + _mysql.escape_string(date1) + '\''
        else:
            date1 = '\'' + date1.strftime('%Y-%m-%d %H:%M:%S') + '\''
        if isinstance(date2, str):
            date2 = '\'' + _mysql.escape_string(date2) + '\''
        else:
            date2 = '\'' + date2.strftime('%Y-%m-%d %H:%M:%S') + '\''
        sql =  'DELETE from %s where %s between %s and %s;' %(table_name, data_column, date1, date2)
        cursor.execute(sql)
        self.__con.commit()
        cursor.close()

    def load_last_data(self, table_name):
        if not self.__is_in_table_whitelist(table_name.split('(')[0]):
            raise connector.Error('table %s doesn`t exist' %table_name.upper())
        cursor = self.__get_cursor()
        sql = 'SELECT id_datetime from %s ORDER BY id_datetime desc LIMIT 200;' % table_name
        cursor.execute(sql)
        rows = cursor.fetchall()
        cursor.close()
        return rows

# def load_data(self):
#     try:
#         cursor = self.__get_cursor()
#         if cursor is not None:
#             sql = 'SELECT * from DATA'
#             cursor.execute(sql)
#             rows = cursor.fetchall()
#             # if rows is not None:
#             # for row in rows:
#             # print(row)
#             pass
#             cursor.close()
#     except connector.Error as e:
#         print e
#


# def insert_many(self, data):
#     try:
#         cursor = self.__get_cursor()
#         if cursor is not None:
#             sql = 'INSERT INTO DATA VALUES (%s, %s, %s)'
#             cursor.executemany(sql, data)
#             self.__con.commit()
#             cursor.close()
#     except connector.Error as e:
#         print e
#
# def delete_all(self):
#     try:
#         cursor = self.__get_cursor()
#         if cursor is not None:
#             sql = 'TRUNCATE TABLE DATA'
#             cursor.execute(sql)
#             self.__con.commit()
#             cursor.close()
#     except connector.Error as e:
#         print e.message
