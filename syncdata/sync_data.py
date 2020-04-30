#!/usr/bin/env python
# -*- coding: utf-8 -*-
import json
import os
import re
import sys
import csv
from datetime import datetime
from datetime import timedelta


import DB
import Logger

# Устранение проблем с кодировкой UTF-8
reload(sys)
sys.setdefaultencoding('utf8')

current_path = os.path.dirname(os.path.abspath(__file__)) + '/'
config_file = current_path + 'sync_data.conf.json'

table_in_db = {
    'data':     'data(id_datetime, id, id_value)',
    'dyn_data': 'data_dyn(id, id_value)',
    'logs':     'logs(id_datetime, log_id, log_text)',
}

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
        Logger.write("Can't read file  -->  %s" %file_path , Logger.LogType.ERROR)
        return False
    else:
        Logger.write("Read file successfully  -->  %s" % file_path)
        return data_from_file

def read_files_by_type(dir_path, type_files):
    """
    Read data from files with needed type. Now only two types:
        dat with csv data,
        log with my structure.
    :param dir_path: path to dir with file
    :type dir_path: str
    :param type_files: type of files. For example - '.dat'
    :type type_files: str
    :return:
        - list of data;
        - list of dates of data(only data without time)
    :rtype: tuple[list, list]
    """
    list_files = sorted([one_file for one_file in os.listdir(dir_path) if one_file.endswith(type_files)])
    count_errors = 0
    list_broken_files = []
    data_from_files = []
    last_filenames = []
    prev_file_date = ''
    for one_file in list_files:
        try:
            data_from_one_file = read_dat_file(dir_path, one_file) if type_files == '.dat' else read_log_file(dir_path, one_file)
            file_date = one_file.split(' ')[0] if type_files == '.dat' else one_file.split('.')[0]
            if file_date == prev_file_date:
                data_from_files[-1] += data_from_one_file
            else:
                data_from_files.append(data_from_one_file)
                prev_file_date = file_date
                last_filenames.append(one_file)
        except:
            count_errors += 1
            list_broken_files.append(one_file)
    message = "Read " + type_files +" files -->  %d/%d" % (len(list_files) - count_errors, len(list_files))
    message += '' if count_errors == 0 else '; Failed for ' + str(list_broken_files)
    Logger.write(message)
    return data_from_files, last_filenames

def read_log_file(dir_path, file_name):
    """
    Read log data with struct:
        2020-04-20 20:20:20  [------] message
    Log type inside [] is a name of enum item in Logger.LogType
    :param dir_path: path to dir with file
    :type dir_path: str
    :param file_name: name of files.
    :type file_name: str
    :return: list of data
    :rtype: list
    """
    with open(dir_path + file_name) as f:
        data_from_log = []
        for line in f:
            pattern = r'.*?(?P<Datetime>\d{4}\-\d{2}\-\d{2}\s\d{2}\:\d{2}\:\d{2})\s*(?P<LogType>\[.*?\])\s*(?P<Message>.*)'
            log_line = re.split(pattern, line)[1:-1]
            log_type_name = re.search(r'\[(.*?)\s*\]', log_line[1]).group(0)
            log_type_name = str(log_type_name[1:-1]).strip().upper()
            if '-' in log_type_name:
                log_line[1] = Logger.LogType.INFO.value
            else:
                log_line[1] = Logger.LogType[log_type_name].value
            log_line[2] = log_line[2][:200]
            data_from_log.append(log_line)
    return data_from_log

def read_dat_file(dir_path, file_name):
    """
    Read dat with csv struct, where delimiter is ','
    :param dir_path: path to dir with file
    :type dir_path: str
    :param file_name: name of files.
    :type file_name: str
    :return: list of data
    :rtype: list
    """
    with open(dir_path + file_name) as f:
        data_from_dat = list(csv.reader(f, delimiter=','))

    for index in range(len(data_from_dat)):
        data_from_dat[index][0] = datetime.strptime(file_name[:-4] + data_from_dat[index][0], '%Y-%m-%d %H%M%S').strftime('%Y-%m-%d %H:%M:%S')
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
        Logger.write(message + '[OK] %4.1fms' % (connect_time*10))
        return True

def upload_data(data, file_name, table_with_params):
    if 'dyn' in file_name.lower():
        message = 'Upload dyn data to %s  -->  ' % table_with_params
    else:
        message = 'Upload data earlier than %s to %s  -->  ' % (file_name.split(' ')[0] if file_name[-4:] == '.dat' else file_name.split('.')[0], table_with_params)
    try:
        if len(data) > 0:
            full_time = 0
            upload_time = 0
            if 'logs' in table_with_params:
                date1 = file_name.split('.')[0]
                date2 = (datetime.strptime(date1, '%Y-%m-%d') + timedelta(hours=23, minutes=59, seconds=59)).strftime('%Y-%m-%d %H:%M:%S')
                server.delete_between_dates(date1, date2, 'logs', 'id_datetime')
            for one_data in list(divide_list(data, 5000)):
                new_full_time, new_upload_time = server.replace_many_rows(one_data, table_with_params)
                upload_time += new_upload_time
                full_time += new_full_time
        else: return False
    except:
        Logger.write(message + str(sys.exc_info()[1]), Logger.LogType.ERROR)
        return False
    else:
        Logger.write(message + '%d rows in %4.1fms (full time - %4.1fms)' % (len(data), upload_time*10, full_time*10))
        return True


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

def save_failed_date(source_error):
    if 'log' in source_error:
        prev_failed_date = datetime.strptime(server.last_dates_for_send.get('logs'), '%Y-%m-%d').date()
        if prev_failed_date > datetime.strptime(log_filenames[i].split('.')[0], '%Y-%m-%d').date():
            server.last_dates_for_send['logs'] = log_filenames[i].split('.')[0]
    elif 'dat' == source_error:
        prev_failed_date = datetime.strptime(server.last_dates_for_send.get('dat'), '%Y-%m-%d').date()
        if prev_failed_date > datetime.strptime(dat_filenames[i].split(' ')[0], '%Y-%m-%d').date():
            server.last_dates_for_send['dat'] = dat_filenames[i].split(' ')[0]
    elif 'dyn' in source_error:
        server.last_dates_for_send['dyn'] = -1

# =========================================================================================================================================================================================
#  СТАРТ программы
# =========================================================================================================================================================================================
print "START program"


dyn_data = read_json_file(current_path + 'DATA_UNP300/.DynDATA.json')
dat_data, dat_filenames = read_files_by_type(current_path + 'DATA_UNP300/', '.dat')
log_data, log_filenames = read_files_by_type(current_path + 'logs/', '.log')
configs_servers = read_json_file(config_file)

for config_server in configs_servers:
    server = DB.Server(**config_server)
    if connect_to_db(server):
        if not upload_data(dyn_data, 'dyn data', table_in_db.get('dyn_data')):
            save_failed_date('dyn')
        for i, one_dat_file in enumerate(dat_data):
            if not upload_data(one_dat_file, dat_filenames[i], table_in_db.get('data')):
                save_failed_date('dat')
        for i, one_log_file in enumerate(log_data):
            if not upload_data(one_log_file, log_filenames[i], table_in_db.get('logs')):
                save_failed_date('logs')

print "END program"
