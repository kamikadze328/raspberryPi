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


def list_to_sql(data_list, factory_id=None):
    sql = ''
    for one_data in data_list:
        sql += '('
        for param in one_data:
            param = None if "" else param
            sql += '\'' + _mysql.escape_string(param) + '\',' if param else 'NULL,'
        sql = sql[:-1] + (',' + _mysql.escape_string(factory_id) if factory_id is not None else '') + '),'
    return sql


class Server(object):
    """
    Class for work with db
    """
    def __init__(self, host, user, password, database):
        self.config = {
            'host': host,
            'user': user,
            'password': password,
            'database': database,
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
        cursor = self.__get_cursor()
        start_time_execute = time.time()
        self.__replace_many_rows_without_commit(data, table_name_with_params, cursor)
        self.__con.commit()
        cursor.close()
        return time.time() - start_time_execute

    def __replace_many_rows_without_commit(self, data, table_name_with_params, cursor, factory_id=None):
        if not self.__is_in_table_whitelist(table_name_with_params.split('(')[0]):
            raise connector.Error('table %s doesn`t exist' % table_name_with_params.split('(')[0].upper())
        sql = 'REPLACE INTO %s VALUES' % table_name_with_params
        if isinstance(data[0], list):
            sql += list_to_sql(data, factory_id)
        else:
            sql += dict_to_sql(data)

        sql = sql[:-1] + ';'
        cursor.execute(sql)


    def delete_between_dates(self, date1, date2, table_name, data_column):
        cursor = self.__get_cursor()
        self.__delete_between_dates_without_commit(date1, date2, table_name, data_column, cursor)
        self.__con.commit()
        cursor.close()

    def __delete_between_dates_without_commit(self, date1, date2, table_name, data_column, cursor):
        if not self.__is_in_table_whitelist(table_name.split('(')[0]):
            raise connector.Error('table %s doesn`t exist' % table_name.upper())
        date1 = '\'' + date1.strftime('%Y-%m-%d %H:%M:%S') + '\''
        date2 = '\'' + date2.strftime('%Y-%m-%d %H:%M:%S') + '\''
        sql = 'DELETE from %s where %s between %s and %s;' % (table_name, data_column, date1, date2)
        cursor.execute(sql)

    def delete_and_replace(self, date1, date2, table_name, data_column, data, table_name_with_params, factory_id=None):
        start_time_execute = time.time()
        self.__con.autocommit = False
        cursor = self.__get_cursor()
        try:
            self.__delete_between_dates_without_commit(date1, date2, table_name, data_column, cursor)
            self.__replace_many_rows_without_commit(data, table_name_with_params, cursor, factory_id)
            self.__con.commit()
            cursor.close()
            return time.time() - start_time_execute
        except connector.Error as e:
            self.__con.rollback()
            raise e

    def load_last_date(self, table_name):
        if not self.__is_in_table_whitelist(table_name.split('(')[0]):
            raise connector.Error('table %s doesn`t exist' % table_name.upper())
        cursor = self.__get_cursor()
        sql = 'SELECT id_datetime from %s ORDER BY id_datetime desc LIMIT 200;' % table_name
        cursor.execute(sql)
        rows = cursor.fetchall()
        cursor.close()
        return rows

