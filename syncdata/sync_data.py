#!/usr/bin/env python
# -*- coding: utf-8 -*-
import json
import os
import re
import sys
import csv
from datetime import datetime

import DB
import Logger

# Устранение проблем с кодировкой UTF-8
reload(sys)
sys.setdefaultencoding('utf8')

current_path = os.path.dirname(os.path.abspath(__file__)) + '/'
config_file = current_path + 'sync_data.conf.json'

table_in_db = {
    'data':     'data(id, id_datetime, id_value)',
    'dyn_data': 'data_dyn(id, id_value)',
    'logs':     'logs(id_datetime, log_id, log_text)',
}

def read_json_file(file_path):
    try:
        with open(file_path) as f:
            data_from_file = json.load(f)
    except:
        Logger.write("Can't read file  -->  %s" %file_path , Logger.LogType.ERROR)
        return False
    else:
        Logger.write("Read file successfully  -->  %s" % file_path)
        return data_from_file

def read_files_by_type(dir_name, type_files):
    list_files = [one_file for one_file in os.listdir(dir_name) if one_file.endswith(type_files)]
    count_errors = 0
    list_broken_files = []
    data_from_file = []
    for one_file in list_files:
        try:
            data_from_file += (read_dat_file(dir_name, one_file) if type_files == '.dat' else read_log_file(dir_name, one_file))
        except:
            count_errors += 1
            list_broken_files.append(one_file)
    message = "Read " + type_files +" files -->  %d/%d" % (len(list_files) - count_errors, len(list_files))
    message += '' if count_errors == 0 else '; Failed for ' + str(list_broken_files)
    Logger.write(message)
    return data_from_file

def read_log_file(dir_name, file_name):
    with open(dir_name + file_name) as f:
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
            data_from_log.append(log_line)
    return data_from_log

def read_dat_file(dir_name, file_name):
    with open(dir_name + file_name) as f:
        data_from_dat = list(csv.reader(f, delimiter=','))
    for i in range(len(data_from_dat)):
        data_from_dat[i][0] = datetime.strptime(file_name[:-4] + data_from_dat[i][0], '%Y-%m-%d %H%M%S').strftime('%Y-%m-%d %H:%M:%S')
    return data_from_dat

def connect_to_db(server_to_connect):
    message = 'Connect to ' + str(server_to_connect.config.get('host')) + '  -->  '
    try:
        connect_time = server_to_connect.connect()
    except:
        Logger.write(message + str(sys.exc_info()[1]), Logger.LogType.ERROR)
        return False
    else:
        Logger.write(message + '[OK] %4.1fms' % connect_time)
        return True

def upload_data(data, table_with_column):
    message = 'Upload data to ' + table_with_column + '  -->  '
    try:
        if len(data) > 0:
            upload_time = server.replace_many_rows(data, table_with_column)
        else: return False
    except:
        Logger.write(message + str(sys.exc_info()[1]), Logger.LogType.ERROR)
        return False
    else:
        Logger.write(message + '%d rows in %4.1fms' % (len(data), upload_time))
        return True

# =========================================================================================================================================================================================
#  СТАРТ программы
# =========================================================================================================================================================================================
print "START program"

dyn_data = read_json_file(current_path + 'DATA_UNP300/.DynDATA.json')
dat_data = read_files_by_type(current_path + 'DATA_UNP300/', '.dat')
log_data = read_files_by_type(current_path + 'DATA_UNP300/', '.log')
configs_servers = read_json_file(config_file)

for config_server in configs_servers:
    server = DB.Server(**config_server)
    if connect_to_db(server):
        upload_data(dyn_data, table_in_db.get('dyn_data'))
        upload_data(dat_data, table_in_db.get('data'))
        upload_data(log_data, table_in_db.get('logs'))

print "END program"
