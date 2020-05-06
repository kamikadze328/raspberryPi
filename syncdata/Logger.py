import codecs
import csv
import json
import os
from datetime import datetime as d

from enum import Enum

current_path = os.path.dirname(os.path.abspath(__file__)) + '/'
last_upload_path = current_path + 'last_upload_date.json'

class LogType(Enum):
    FATAL   =   1
    ERROR   =   2
    WARN    =   3
    INFO    =   4

def get_logs_filepath():
    return current_path + '/' + 'logs/' + d.now().strftime("%Y-%m-%d %H%M") + '.log'


def get_current_time():
    return unicode(d.now().strftime('%Y-%m-%d %H:%M:%S'))

def make_message(message, log_type=None, time=None):
    if time is None:
        time = get_current_time()
    if isinstance(time, d):
        time = time.strftime('%Y-%m-%d %H:%M:%S')
    log_type = '[------]' if log_type is None else '[{:6}]'.format(log_type.name)
    return unicode(time +'  ' + log_type + ' ' + message + '\n')

def write(message, log_type=None, file_path=None):
    message = make_message(message, log_type)
    if file_path is None:
        file_path = get_logs_filepath()
    if not os.path.exists(file_path):
        with codecs.open(file_path, 'w', encoding='utf-8') as f:
            f.write(message)
    else:
        with codecs.open(file_path, 'a', encoding='utf-8') as f:
            f.write(message)

def write_last_upload(last_upload_date, filename):
    with open(filename, 'w') as outfile:
        json.dump(last_upload_date, outfile, indent=4)



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
        write("Can't read file  -->  %s" % file_path, LogType.ERROR)
        return False
    else:
        return data_from_file

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
            data_from_log.append([line[:19], line[22:28], line[29:229]])
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


def set_last_data(db_server, tablename, last_upload_date):
    pattern_json_data = [{
        'host': db_server.config.get('host'),
        'data': '1990-01-01 00:00',
        'logs': '1900-01-01 00:00',
        'logs_syncdata': '1900-01-01 00:00',
        tablename: last_upload_date
    }]
    if not os.path.exists(last_upload_path):
        with open(last_upload_path, 'w') as f:
            json.dump(pattern_json_data, f, indent=4)

    else:
        with open(last_upload_path, 'r') as f:
            new_upload_data = json.load(f)

        if len([host for host in new_upload_data if host.get('host') == db_server.config.get('host')]) > 0:
            for host in new_upload_data:
                if host.get('host') == db_server.config.get('host'):
                    host[tablename] = last_upload_date
        else:
            new_upload_data.append(pattern_json_data[0])

        with open(last_upload_path, 'w') as f:
            json.dump(new_upload_data, f)
