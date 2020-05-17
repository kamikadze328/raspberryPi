#!/usr/bin/env python
# -*- coding: utf-8 -*-

import os
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
my_logs_path = current_path + 'logs/'

data_path = '/var/www/html/DATA_UNP300/'
#data_path = '/home/pi/sk/syncdata/DATA_UNP300/'
dyn_data_name = '.DynDATA.json'
stat_path = 'stats/'

table_in_db = {
    'data': 'data(id_datetime, id, id_value)',
    'dyn_data': 'data_dyn(id, id_value)',
    'logs': 'logs(id_datetime, log_id, log_text)',
    'logs_syncdata': 'logs_syncdata(id_datetime, log_id, log_text)',
    'statistics': 'statistics(id_datetime, host_name, time_upload_ms, time_connection_ms)'
}


def filter_files_by_date(dir_path, type_files, first_date):
    files = []
    for one_file in os.listdir(dir_path):
        if one_file.endswith(type_files) and first_date <= one_file.split('.')[0]:
            files.append(one_file)
    return sorted(files)


def read_files_by_type(dir_path, type_files, first_date):
    """
    Read data from files with needed type. Now only two types:
        dat with csv data,
        log with my structure.
    Sorted file on data(because date in filename) and put data from one day in one list
    :param first_date:
    :type first_date
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
            data_from_one_file = Logger.read_dat_file(
                dir_path + one_file) if type_files == '.dat' else Logger.read_log_file(
                dir_path + one_file)
        except:
            list_broken_files.append(one_file)
        else:
            data_from_files.append(data_from_one_file)
            list_success_files.append(one_file)
    if len(list_broken_files) > 0:
        Logger.write('Can`t read %d files  -->  %s' % (len(list_broken_files), list_broken_files), Logger.LogType.WARN)
    return data_from_files, list_success_files, list_broken_files[0] if len(list_broken_files) and len(
        list_success_files) > 0 else None


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
        add_row_statistics(time_connection=-1, with_error=True)
        return False
    else:
        Logger.write(message + '%4.1fms' % (connect_time * 10))
        add_row_statistics(time_connection=connect_time * 10)
        return True


def get_last_data_from_server(db_server, tablename):
    """

    :param db_server:
    :param tablename:
    :return: Last date of data on the server
    :rtype: str or None
    """
    try:
        dates = db_server.load_last_data(tablename)
        if len(dates) == 0:
            return datetime(1900, 01, 01, 00, 00).strftime("%Y-%m-%d %H%M")
        else:
            return dates[len(dates) / 2][0].strftime("%Y-%m-%d %H%M")
    except:
        Logger.write('Can`t check server data -->  ' + str(sys.exc_info()[1]), Logger.LogType.ERROR)
        return None


def get_last_date(db_server, tablename):
    last_upload_data = Logger.read_json_file(Logger.last_upload_path, True)
    if last_upload_data and len(last_upload_data) > 0:
        for host in last_upload_data:
            if host.get('host') == db_server.config.get('host'):
                return datetime.strptime(str(host.get(tablename)), '%Y-%m-%d %H:%M').strftime("%Y-%m-%d %H%M")

    else:
        return get_last_data_from_server(db_server, tablename)


def upload_data(list_data, list_filenames, table_with_params):
    table_name = table_with_params.split('(')[0]
    if dyn_data_name in list_filenames:
        message = 'Upload dyn data to %s  -->  ' % table_name.upper()
    else:
        message = 'Upload data since %s to %s  -->  ' % (list_filenames[0].split('.')[0], table_name.upper())

    if len(list_data) > 0:
        full_time = 0
        upload_time = 0
        count_rows = 0
        index = 0
        with_error = False
        try:
            for one_data in list_data:
                if 'logs' in table_with_params:
                    date1 = datetime.strptime(list_filenames[index].split('.')[0], '%Y-%m-%d %H%M')
                    date2 = date1 + timedelta(seconds=59)
                    server.delete_between_dates(date1, date2, table_name, 'id_datetime')

                new_full_time, new_upload_time = server.replace_many_rows(one_data, table_with_params)
                upload_time += new_upload_time
                full_time += new_full_time
                count_rows += len(one_data)
                index += 1

        except:
            with_error = True
            Logger.write(message + str(sys.exc_info()[1]), Logger.LogType.ERROR)

        if count_rows > 0:
            Logger.write(message + '%d rows in %d files in %4.1fms (fulltime - %4.1fms)' %
                         (count_rows, index, upload_time * 10, full_time * 10))
            add_row_statistics(time_upload=upload_time*10, with_error=with_error)
        else:
            add_row_statistics(time_upload=-1, with_error=True)
        return index


def add_row_statistics(time_upload=None, time_connection=None, with_error=False):
    one_row_stat = {
        'id_datetime': datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
        'host_name': server.config.get('host'),
        'time_upload_ms': round(time_upload, 3) if time_upload else None,
        'time_connection_ms': round(time_connection, 2) if time_connection else None,
        'is_error':with_error
    }
    statistics_rows.append(one_row_stat)

def send_statistics():
    # last_date_stat = get_last_date(server, 'stat')
    # old_statistics_rows = Logger.read_stat()
    # TODO записывать статистику по файлам и сохранять последние отправленные
    if old_statistics_rows and len(old_statistics_rows) > 0:
        try:
            server.upload_stat(old_statistics_rows)
        except:
            pass

def prepare_dyn_data(dynamic_data):
    keys = ['iTegAddr', 'iTegValue']
    new_dyn_data = []
    for one_data in dynamic_data:
        new_dyn_data.append([str(one_data.get(key)) for key in keys])
    return [new_dyn_data]
# =========================================================================================================================================================================================
#  СТАРТ программы
# =========================================================================================================================================================================================
print "START program"

configs_servers = Logger.read_json_file(config_path)
old_statistics_rows = Logger.read_stat()
statistics_rows = []

for config_server in configs_servers:
    server = DB.Server(**config_server)
    if connect_to_db(server):

        # Если добавился новый сервер, следует самому добавить его в статистику

        send_statistics()
        # upload dynamic data
        dyn_data = Logger.read_json_file(data_path + dyn_data_name)
        if dyn_data:
            upload_data(prepare_dyn_data(dyn_data), data_path + dyn_data_name, table_in_db.get('dyn_data'))

        for (table, type_file, path) in [('data', '.dat', data_path), ('logs', '.log', data_path),
                                         ('logs_syncdata', '.log', my_logs_path)]:

            last_date = get_last_date(server, table_in_db.get(table).split('(')[0])
            last_data_from_files, filenames, first_broken_read_file = read_files_by_type(path, type_file, last_date)
            if len(last_data_from_files):
                index_broken_or_last_upload_file = upload_data(last_data_from_files, filenames, table_in_db.get(table))
                if first_broken_read_file or index_broken_or_last_upload_file:
                    if index_broken_or_last_upload_file:
                        index_broken_or_last_upload_file -= 1
                        last_date_file = min(first_broken_read_file, filenames[index_broken_or_last_upload_file]) \
                            if first_broken_read_file else filenames[index_broken_or_last_upload_file]
                    else:
                        last_date_file = first_broken_read_file

                    last_date_file = last_date_file.split('.')[0]
                    Logger.save_last_upload_dates(server.config.get('host'), table_in_db.get(table).split('(')[0],
                                                  datetime.strptime(last_date_file, '%Y-%m-%d %H%M').strftime("%Y-%m-%d %H:%M"), last_date)
        # calculate_stat()
Logger.save_stat(statistics_rows)
print "END program"
