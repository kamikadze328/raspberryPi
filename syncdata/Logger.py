import codecs
import csv
import json
import os
from datetime import datetime as d

from enum import Enum

current_path = os.path.dirname(os.path.abspath(__file__)) + '/'
last_upload_path = current_path + 'last_upload_date.json'
statistics_path = current_path + 'statistics.json'


class LogType(Enum):
    """
    Enum with type of message in log
    """
    FATAL = 3
    ERROR = 2
    WARN = 1
    INFO = 0


def write(message, log_type=None, file_path=None):
    message = __make_message(message, log_type)
    if file_path is None:
        file_path = __get_logs_filepath()
    if not os.path.exists(file_path):
        with codecs.open(file_path, 'w', encoding='utf-8') as f:
            f.write(message)
    else:
        with codecs.open(file_path, 'a', encoding='utf-8') as f:
            f.write(message)


def __get_logs_filepath():
    if not os.path.exists(current_path + 'logs/'):
        os.mkdir(current_path + 'logs/')
    return current_path + 'logs/' + d.now().strftime("%Y-%m-%d %H%M") + '.log'

def __get_stat_filepath():
    if not os.path.exists(current_path + 'stats/'):
        os.mkdir(current_path + 'stats/')
    return current_path + 'stats/' + d.now().strftime("%Y-%m-%d %H%M") + '.stat'


def __make_message(message, log_type=None, time=None):
    if time is None:
        time = unicode(d.now().strftime('%Y-%m-%d %H:%M:%S'))
    elif isinstance(time, d):
        time = time.strftime('%Y-%m-%d %H:%M:%S')
    log_type = '[------]' if log_type is None else '[{:6}]'.format(log_type.name)
    return unicode(time + '  ' + log_type + ' ' + message + '\n')


def read_json_file(file_path, do_check_file=False):
    """
    Read json data from file
    :param do_check_file: Do with check whether the file exists or not?
        Value is 'False' (the default)
    :type do_check_file: bool
    :param file_path: path to file
    :type file_path: str
    :return: data from file
    :rtype: list or None
    """
    try:
        if do_check_file:
            if not os.path.exists(file_path):
                with open(file_path, 'w') as f:
                    f.write(str([]))

        with open(file_path) as f:
            data_from_file = json.load(f)
    except:
        write("Can't read file  -->  %s" % file_path, LogType.WARN)
        return None
    else:
        return data_from_file


def read_log_file(file_path):
    """
    Read log data with struct:
        2020-04-20 20:20:20  [------] message
    Log type inside [] is a name of enum item in Logger.LogType
    Cut message to 200 chars.
    :param file_path: absolute path to file.
    :type file_path: str
    :return: list of data
    :rtype: list
    """
    with open(file_path) as f:
        data_from_log = []
        for line in f:
            if len(line) > 30:
                log_type = LogType[line[22:28].strip().upper()] if line[22:28] != '------' else LogType.INFO
                data_from_log.append([line[:19], str(log_type.value), line[29:229]])
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


def save_last_upload_dates(host_name, tablename, last_upload_date):
    pattern_json_data = [{
        'host': host_name,
        'data': '1990-01-01 00:00',
        'data_counter': 0,
        'logs': '1900-01-01 00:00',
        'logs_counter': 0,
        'logs_syncdata': '1900-01-01 00:00',
        'logs_syncdata_counter': 0,
        'statistics_syncdata':'1900-01-01 00:00',
        'statistics_syncdata_counter':0,
        tablename: last_upload_date
    }]
    if not os.path.exists(last_upload_path):
        with open(last_upload_path, 'w') as f:
            json.dump(pattern_json_data, f, indent=4)

    else:
        with open(last_upload_path, 'r') as f:
            new_upload_data = json.load(f)

        if len([host for host in new_upload_data if host.get('host') == host_name]) > 0:
            for host in new_upload_data:
                if host.get('host') == host_name:
                    counter = host.get(tablename + '_counter')
                    if counter is not None:
                        if last_upload_date == host.get(tablename):
                            counter = int(counter) + 1
                            host[tablename + '_counter'] = counter
                        else:
                            host[tablename + '_counter'] = 0
                    host[tablename] = last_upload_date
        else:
            new_upload_data.append(pattern_json_data[0])

        with open(last_upload_path, 'w') as f:
            json.dump(new_upload_data, f, indent=4)


def save_stat(statistics, headers):
    """
    Save statistics with rewriting existing statistics
    :param headers:
    :param statistics: Statistics in json format
    """
    stat_path = __get_stat_filepath()
    if not os.path.exists(stat_path):
        with open(stat_path, 'w'):pass
    with open(stat_path, 'a') as f:
        writer = csv.DictWriter(f, fieldnames=headers)
        for row in statistics:
            writer.writerow(row)
