import csv
import json
import os
from datetime import datetime as d
from enum import Enum

import Config

pattern_last_data = [{
    'data': '2000-01-01 0000',
    'data_counter': 0,
    'logs': '2000-01-01 0000',
    'logs_counter': 0,
    'logs_syncdata': '2000-01-01 0000',
    'logs_syncdata_counter': 0,
    'statistics_syncdata':'2000-01-01 0000',
    'statistics_syncdata_counter':0,
    'host': ''
}]

class LogType(Enum):
    """
    Enum with type of message in log
    """
    FATAL = 10
    ERROR = 6
    WARN  = 3
    ALARM = 3
    INFO  = 0
    HIDDEN= -1


def write(message, log_type=None, file_path=None):
    """
    Write a row in a log.
    :param message: message of log.
    :type message: str
    :param log_type: Type og log from enum. Default LogType.INFO.
    :type log_type: int or LogType
    :param file_path: Path to log file. Not necessary param.
    :type file_path: str
    """
    message = __make_message(message, log_type)
    print message
    if log_type is not None:
        if file_path is None:
            file_path = __get_logs_filepath()
        if not os.path.exists(file_path):
            with open(file_path, 'w') as f:
                f.write(message)
        else:
            with open(file_path, 'a') as f:
                f.write(message)


def __get_logs_filepath():
    """
    Get default absolute name of current log file.
    :return: default filepath to logs.
    :rtype: str
    """
    if not os.path.exists(Config.MY_LOGS_PATH):
        os.mkdir(Config.MY_LOGS_PATH)
    return Config.MY_LOGS_PATH + d.now().strftime("%Y-%m-%d %H%M") + '.log'

def __get_stat_filepath():
    """
     Get default absolute name of current statistics file.
    :return: default filepath to statistics files.
    :rtype: str
    """
    if not os.path.exists(Config.MY_STATS_PATH):
        os.mkdir(Config.MY_STATS_PATH)
    return Config.MY_STATS_PATH + d.now().strftime("%Y-%m-%d %H%M") + '.stat'


def __make_message(message, log_type=None, time=None):
    """
    Make message in right log format.
    :param message: message of log.
    :type message: str
    :param log_type: Type og log from enum. Default LogType.INFO.
    :type log_type: int or LogType
    :param time: Time of logs. Default current time. Format Y-m-d H:M:S.
    :type time: str
    :return: message in log format.
    :rtype: str
    """
    if time is None:
        time = unicode(d.now().strftime('%Y-%m-%d %H:%M:%S'))
    elif isinstance(time, d):
        time = time.strftime('%Y-%m-%d %H:%M:%S')
    log_type = '[------]' if log_type is None else '[{:6}]'.format(log_type.name)
    return unicode(time + '  ' + log_type + ' ' + message + '\n')


# noinspection PyBroadException
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

                # Get only 200 symbols from message and delete the last '\n'.
                log_message = line[30:230]
                if log_message.endswith('\n'): log_message = log_message[0:-1]

                data_from_log.append([line[:19], str(log_type.value), log_message])
    return data_from_log


def read_csv_file(file_path):
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

def init_last_upload_date(servers):
    if not os.path.exists(Config.LAST_UPLOAD_PATH):
        with open(Config.LAST_UPLOAD_PATH, 'w') as f:
            for server in servers:
                last_dates = list(pattern_last_data)
                last_dates[0]['host'] = server.get('host') + '@' + server.get('database')
                json.dump(last_dates, f, indent=4)

    else:
        with open(Config.LAST_UPLOAD_PATH, 'r') as f:
            servers_last_dates = json.load(f)

        for server in servers:
            host_and_db = server.get('host') + '@' + server.get('database')
            is_saved = False
            for server_last_date in servers_last_dates:
                if host_and_db == server_last_date.get('host'):
                    is_saved = True
                    break
            if not is_saved:
                last_dates = pattern_last_data[0]
                last_dates['host'] = host_and_db
                servers_last_dates.append(last_dates)

        with open(Config.LAST_UPLOAD_PATH, 'w') as f:
            json.dump(servers_last_dates, f, indent=4)


# noinspection PyBroadException
def save_last_upload_dates(host_name, database_name, table_name, last_upload_date):
    """
    Save last upload dates. It is necessary for uploading not all files and deleting already uploaded files.
    :param database_name: Name of database.
    :type database_name: str
    :param host_name: Name of host.
    :type host_name: str
    :param table_name: name of table in db
    :type table_name: str
    :param last_upload_date: Date in format 'Y-m-d HM'
    :type last_upload_date: str
    """

    last_dates_current = list(pattern_last_data)
    host_name = host_name + '@' + database_name
    last_dates_current[0]['host'] = host_name
    last_dates_current[0][table_name] = last_upload_date

    try:
        if not os.path.exists(Config.LAST_UPLOAD_PATH):
            with open(Config.LAST_UPLOAD_PATH, 'w') as f:
                json.dump(last_dates_current, f, indent=4)

        else:
            with open(Config.LAST_UPLOAD_PATH, 'r') as f:
                new_upload_data = json.load(f)

            if len([host for host in new_upload_data if host.get('host') == host_name]) > 0:
                for host in new_upload_data:
                    if host.get('host') == host_name:
                        counter = host.get(table_name + '_counter')
                        if counter is not None:
                            if last_upload_date == host.get(table_name):
                                counter = int(counter) + 1
                                host[table_name + '_counter'] = counter
                            else:
                                host[table_name + '_counter'] = 0
                        host[table_name] = last_upload_date
            else:
                new_upload_data.append(last_dates_current[0])

            with open(Config.LAST_UPLOAD_PATH, 'w') as f:
                json.dump(new_upload_data, f, indent=4)
    except:
        os.remove(Config.LAST_UPLOAD_PATH)
        with open(Config.LAST_UPLOAD_PATH, 'w') as f:
            json.dump(last_dates_current, f, indent=4)

def get_last_date(db_server, table_name):
    """
    Get last date of upload data.
    It gets from file or if file broken(?) from server
    :param db_server: The server
    :type db_server: DB.Server
    :param table_name: The name of table from 'table_in_db'
    :type table_name: str
    :return: Last date of data format (Y-m-d HM) or None if file broken and server not available
    :rtype: str
    """
    last_upload_data = read_json_file(Config.LAST_UPLOAD_PATH, True)
    if last_upload_data and len(last_upload_data) > 0:
        for host in last_upload_data:
            if host.get('host') == db_server.config.get('host') + '@' + db_server.config.get('database'):
                return str(host.get(table_name))
    else:
        return None

def save_stat(statistics, headers):
    """
    Save rows statistics.
    :param statistics: Statistics in json format
    :type statistics: list[dict]
    :param headers: headers of dictionary
    :type headers: list[str]
    """
    stat_path = __get_stat_filepath()
    if not os.path.exists(stat_path):
        with open(stat_path, 'w'):pass
    with open(stat_path, 'a') as f:
        writer = csv.DictWriter(f, fieldnames=headers)
        for row in statistics:
            writer.writerow(row)
