#!/usr/bin/env python
# -*- coding: utf-8 -*-
import csv
import json
import os
import re
import sys
from datetime import datetime
from datetime import timedelta

import DB
import Logger

# Устранение проблем с кодировкой UTF-8
reload(sys)
sys.setdefaultencoding('utf8')

current_path = os.path.dirname(os.path.abspath(__file__)) + '/'
config_path = current_path + 'sync_data.conf.json'
my_logs_path = current_path +  'logs/'

data_path = '/home/pi/sk/syncdata/DATA_UNP300/'
dyn_data_path = data_path + '.DynDATA.json'

table_in_db = {
    'data': 'data(id_datetime, id, id_value)',
    'dyn_data': 'data_dyn(id, id_value)',
    'logs': 'logs(id_datetime, log_id, log_text)',
    'logs_syncdata': 'logs_syncdata(id_datetime, log_id, log_text)'
}

last_upload_filename = 'last_upload_date.json'


def read_json_file(file_path):
    """
    Read json data from file
    :param file_path: path to file
    :type file_path: str
    :return: data from file
    :rtype: list or None
    """
    try:
        with open(file_path) as f:
            data_from_file = json.load(f)
    except:
        Logger.write("Can't read file  -->  %s" % file_path, Logger.LogType.ERROR)
        return False
    else:
        return data_from_file


def filter_files_by_date(dir_path, type_files, first_date):
    files = []
    for one_file in os.listdir(dir_path):
        if one_file.endswith(type_files) and first_date <= datetime.strptime(one_file.split('.')[0], '%Y-%m-%d %H%M'):
            files.append(one_file)
    return sorted(files)


def read_files_by_type(dir_path, type_files, first_date):
    """
    Read data from files with needed type. Now only two types:
        dat with csv data,
        log with my structure.
    Sorted file on data(because date in filename) and put data from one day in one list
    :param dir_path: path to dir with file
    :type dir_path: str
    :param type_files: type of files. For example - '.dat'
    :type type_files: str
    :return:
        - list of data;
        - list of dates of data(only data without time)
    :rtype: tuple[list, list]
    """
    list_files = filter_files_by_date(dir_path, type_files, first_date)
    list_broken_files = []
    list_success_files = []
    data_from_files = []
    for one_file in list_files:
        try:
            data_from_one_file = read_dat_file(dir_path + one_file) if type_files == '.dat' else read_log_file(
                dir_path + one_file)
            data_from_files.append(data_from_one_file)
            list_success_files.append(one_file)
        except:
            list_broken_files.append(one_file)
    if len(list_broken_files) > 0:
        Logger.write('Can`t read %d  -->  %s' % (len(list_broken_files), list_broken_files), Logger.LogType.WARN)
    return data_from_files, list_success_files, list_broken_files


def read_log_file(file_path):
    """
    Read log data with struct:
        2020-04-20 20:20:20  [------] message
    Log type inside [] is a name of enum item in Logger.LogType
    Cut message to 200 chars.
    :param file_path: absolute path to file
    :type file_path: str
    :return: list of data
    :rtype: list
    """
    with open(file_path) as f:
        data_from_log = []
        for line in f:
            pattern = r'.*?(?P<Datetime>\d{4}\-\d{2}\-\d{2}\s\d{2}\:\d{2}\:\d{2})\s*(?P<LogType>\[.*?\])\s*(?P<Message>.*)'
            log_line = re.split(pattern, line)[1:-1]
            if len(log_line) == 3:
                log_type_name = re.search(r'\[(.*?)\s*\]', log_line[1]).group(0)
                log_type_name = str(log_type_name[1:-1]).strip().upper()
                if '-' in log_type_name:
                    log_line[1] = Logger.LogType.INFO.value
                else:
                    log_line[1] = Logger.LogType[log_type_name].value
                log_line[2] = log_line[2][:200]
                data_from_log.append(log_line)
    return data_from_log


def read_dat_file(file_path):
    """
    Read dat with csv struct, where delimiter is ','
    :param file_path: absolute path to file
    :type file_path: str
    :return: list of data
    :rtype: list
    """
    with open(file_path) as f:
        data_from_dat = list(csv.reader(f, delimiter=','))

    return data_from_dat


def connect_to_db(server_to_connect):
    """
    Connecting to server via DB module in this project
    :param server_to_connect: Server for connection
    :type server_to_connect: DB.Server
    :return: Result of connection.
    :rtype bool
    """
    message = 'Connect to ' + str(server_to_connect.config.get('host')) + '  -->  '
    try:
        connect_time = server_to_connect.connect()
    except:
        Logger.write(message + str(sys.exc_info()[1]), Logger.LogType.ERROR)
        return False
    else:
        Logger.write(message + '%4.1fms' % (connect_time * 10))
        return True


def upload_data(list_data, list_filenames, table_with_params):
    table_name = table_with_params.split('(')[0]
    if dyn_data_path in list_filenames:
        message = 'Upload dyn data to %s  -->  ' % table_name.upper()
    else:
        message = 'Upload data since %s to %s  -->  ' % (list_filenames[0].split('.')[0], table_name.upper())

    if len(list_data) > 0:
        full_time = 0
        upload_time = 0
        count_rows = 0
        for index, one_data in enumerate(list_data):
            if 'logs' in table_with_params:
                date1 = datetime.strptime(list_filenames[index].split('.')[0], '%Y-%m-%d %H%M')
                date2 = date1 + timedelta(seconds=59)
                server.delete_between_dates(date1, date2, table_name, 'id_datetime')
            try:
                new_full_time, new_upload_time = server.replace_many_rows(one_data, table_with_params)
            except:
                Logger.write(message + str(sys.exc_info()[1]), Logger.LogType.ERROR)
            else:
                upload_time += new_upload_time
                full_time += new_full_time
                count_rows += len(one_data)
        if count_rows > 0:
            Logger.write(message + '%d rows in %d files in %4.1fms (fulltime - %4.1fms)' % (
            count_rows, len(list_data), upload_time * 10, full_time * 10))


def get_last_data(tablename):
    try:
        dates = server.load_last_data(tablename)
        if len(dates) == 0:
            return datetime.min
        else:
            return dates[len(dates) / 2][0].replace(second=0, microsecond=0)
    except:
        Logger.write('Can`t check server data -->  ' + str(sys.exc_info()[1]), Logger.LogType.ERROR)
        return False


def divide_list(divided_list, size_of_chunk):
    """
    Divide list on chunks of needed size
    Divider have a ``Yields`` section instead of a ``Returns`` section.
    :param divided_list: list
    :type divided_list: list
    :param size_of_chunk: length of chunks
    :type size_of_chunk: int
    :return generator
    """
    for counter in range(0, len(divided_list), size_of_chunk):
        yield divided_list[counter:counter + size_of_chunk]


# def save_last_date(source_error):
#     if 'log' in source_error:
#         prev_date = datetime.strptime(server.last_upload_date.get('logs'), '%Y-%m-%d').date()
#         if prev_date < datetime.strptime(log_filenames[i].split('.')[0], '%Y-%m-%d').date():
#             server.last_upload_date['logs'] = log_filenames[i].split('.')[0]
#     elif 'dat' == source_error:
#         prev_date = datetime.strptime(server.last_upload_date.get('dat'), '%Y-%m-%d').date()
#         if prev_date < datetime.strptime(dat_filenames[i].split(' ')[0], '%Y-%m-%d').date():
#             server.last_upload_date['dat'] = dat_filenames[i].split(' ')[0]
#     elif 'dyn' in source_error:
#         server.last_upload_date['dyn'] = 0

# =========================================================================================================================================================================================
#  СТАРТ программы
# =========================================================================================================================================================================================
print "START program"

configs_servers = read_json_file(config_path)

for config_server in configs_servers:
    server = DB.Server(**config_server)
    if connect_to_db(server):
        server.last_upload_date['last_connection'] = datetime.now().strftime("%Y-%m-%d %H:%M:%S")

        #upload dynamic data
        dyn_data = read_json_file(dyn_data_path)
        upload_data([dyn_data], dyn_data_path, table_in_db.get('dyn_data'))

        #upload data
        last_date = get_last_data(table_in_db.get('data').split('(')[0])
        if last_date:
            last_data, last_filenames, count_files = read_files_by_type(data_path, '.dat', last_date)
            upload_data(last_data, last_filenames, table_in_db.get('data'))

        #upload logs
        last_date = get_last_data(table_in_db.get('logs').split('(')[0])
        if last_date:
            last_data, last_filenames, count_files = read_files_by_type(data_path, '.log', last_date)
            upload_data(last_data, last_filenames, table_in_db.get('logs'))

        #upload my logs
        last_date = get_last_data(table_in_db.get('logs_syncdata').split('(')[0])
        if last_date:
            last_data, last_filenames, count_files = read_files_by_type(my_logs_path, '.log', last_date)
            upload_data(last_data, last_filenames, table_in_db.get('logs_syncdata'))

print "END program"
