#!/usr/bin/env python
# -*- coding: utf-8 -*-

import os
import sys
import time
from datetime import datetime
from datetime import timedelta
import errno
import Config
import DB
import Logger

# Устранение проблем с кодировкой UTF-8
reload(sys)
sys.setdefaultencoding('utf8')


# Create not existed dirs
if not os.path.exists(Config.WORKING_DIR):
    os.mkdir(Config.WORKING_DIR)
if not os.path.exists(Config.MY_LOGS_PATH):
    os.mkdir(Config.MY_LOGS_PATH)
if not os.path.exists(Config.MY_STATS_PATH):
    os.mkdir(Config.MY_STATS_PATH)


# Tables in db with parameters
table_in_db = {
    'data': 'data(id_datetime, id, id_value)',
    'dyn_data': 'data_dyn(id, id_value)',
    'logs': 'logs(id_datetime, log_id, log_text, id_factory)',
    'logs_syncdata': 'logs_syncdata(id_datetime, log_id, log_text, id_factory)',
    'statistics_syncdata': 'statistics_syncdata(id_datetime, '
                                                'host_name, '
                                                'database_name, '
                                                'time_upload_ms, '
                                                'time_connection_ms, '
                                                'count_rows_uploaded, '
                                                'count_files_uploaded, '
                                                'was_error, '
                                                'id_factory)',
}
data_names = ['data', 'logs', 'logs_syncdata', 'statistics_syncdata']

MAX_DATE = datetime(9000, 12, 31, 23, 59)
MIN_DATE = datetime(2000, 1, 1, 0, 0)


def get_table_name(table_in_db_key):
    return table_in_db.get(table_in_db_key).split('(')[0]

def get_filename_without_extension(fullname):
    return fullname.split('.')[0]

def filter_files_by_date(dir_path, type_files, min_date):
    """
    Filter file with dates (in names files) more or equals than min_date.
    There is compering only by string
    :param dir_path: path to dir with files
    :type dir_path: str
    :param type_files: Type of files e.g. '.log' or '.dat'
    :type type_files: str
    :param min_date: Min needed date in format 'Y-m-d HM'
    :type min_date: str
    :return: list of sorted files
    :rtype: list
    """
    files = []
    for one_file in os.listdir(dir_path):
        if one_file.endswith(type_files) and min_date <= get_filename_without_extension(one_file):
            files.append(one_file)
    return sorted(files)


# noinspection PyBroadException
def read_files_by_type(dir_path, type_files, first_date):
    """
    Read data from files with needed type. Now only two types:
        dat with csv data,
        log with my structure.
    Sort files by date (because date in filename) and put data from one day in one list
    :param first_date:
    :type first_date
    :param dir_path: path to dir with file
    :type dir_path: str
    :param type_files: type of files. For example - '.dat'
    :type type_files: str
    :return:
        - list of data;
        - list of dates of successful read data (only data without time)
        - list of broken files (name is only data without time in format 'Y-m-d HM')
    :rtype: tuple[list, list, list]
    """
    list_files = filter_files_by_date(dir_path, type_files, first_date)
    list_broken_files = []
    list_success_files = []
    data_from_files = []
    for one_file in list_files:
        try:
            if type_files == '.log':
                data_from_one_file = Logger.read_log_file(dir_path + one_file)
            else:
                data_from_one_file = Logger.read_csv_file(dir_path + one_file)

        except:
            list_broken_files.append(one_file)
        else:
            data_from_files.append(data_from_one_file)
            list_success_files.append(one_file)

    return data_from_files, list_success_files, list_broken_files


# noinspection PyBroadException
def connect_to_db(server_to_connect):
    """
    Connecting to server via DB module from this project
    :param server_to_connect: Server for connection
    :type server_to_connect: DB.Server
    :return: Result of connection.
    :rtype bool
    """
    try:
        connect_time = server_to_connect.connect()
    except:
        Logger.write('Connect - ' + str(sys.exc_info()[1]), Logger.LogType.ERROR)
        add_row_statistics(time_connection=-1, with_error=True)
        return False
    else:
        add_row_statistics(time_connection=connect_time * 10)
        return True


# noinspection PyBroadException
def get_last_date_from_server(db_server, tablename):
    """
    Get almost last date(average, median of several last rows) from server.
    :param db_server: Server
    :type db_server: DB.Server
    :param tablename: name of table from 'table_in_db'
    :type tablename: str
    :return: Last date of data on the server format (Y-m-d HM) or None if server not available
    :rtype: datetime or None
    """
    try:
        dates = db_server.load_last_date(tablename)

        if len(dates) != 0:
            return dates[len(dates) / 2][0].strftime("%Y-%m-%d %H%M")
        else:
            return datetime(1900, 01, 01, 00, 00).strftime("%Y-%m-%d %H%M")
    except:
        Logger.write('Can`t check server data -  ' + str(sys.exc_info()[1]), Logger.LogType.ERROR)
        return datetime(2000, 01, 01, 00, 00).strftime("%Y-%m-%d %H%M")


# noinspection PyBroadException
def upload_data(list_data, list_filenames, table_with_params):
    """
    Upload data to server.

    :param list_data: A list of lists. Each small list send one by one. (usually lists contains data from one file)
    :type list_data: list[list[str]]
    :param list_filenames: list of name of uploading files(with dot and format)
    :type list_filenames: list[str]
    :param table_with_params: value of table from 'table_in_db'
    :type table_with_params: str
    :return:
        - Index of first broken file or length of list_data.
        - Number of errors.
    :rtype: tuple[int, int]
    """

    if len(list_data) > 0:
        upload_time = 0
        index = 0
        number_rows = 0
        with_error = False
        table_name = table_with_params.split('(')[0]
        try:
            # Stop when get exception
            for one_data in list_data:
                if len(one_data) > 0:
                    # Prepare table for logs and statistics because its doesn't have a primary key.
                    # So I delete all of data of this minute (I get needed minute from name of file)
                    if table_with_params.startswith('logs') or table_with_params.startswith('statistics_syncdata'):
                        date1 = datetime.strptime(get_filename_without_extension(list_filenames[index]), '%Y-%m-%d %H%M')
                        date2 = date1 + timedelta(seconds=59)
                        new_upload_time = server.delete_and_replace(date1, date2, table_name, 'id_datetime', one_data, table_with_params, Config.FACTORY_ID)
                    else:
                        new_upload_time = server.replace_many_rows(one_data, table_with_params)
                    upload_time += new_upload_time
                    number_rows += len(one_data)
                    index += 1

        except:
            with_error = True
            Logger.write('Upload %s to %s - %s' % (list_filenames[index], table_name, str(sys.exc_info()[1])), Logger.LogType.ERROR)

        # Save statistics
        if number_rows > 0:
            add_row_statistics(time_upload=upload_time*10, count_rows=number_rows, count_files=index, with_error=with_error)
        else:
            add_row_statistics(time_upload=-1, with_error=True)

        return index, number_rows



def add_row_statistics(time_upload=None, time_connection=None, count_rows=None, count_files=None, with_error=False):
    """
    Save one row of statistics.
    Get host name from current server parameter (from outside the function).
    Save row with only one from time_upload or time_connection (second param will be null)
     because to make in db rows with connection stat and upload stat different.
    :param count_files: count of successfully uploaded files
    :type count_files: int
    :param count_rows: count of rows in successfully uploaded files
    :type count_rows: int
    :param time_upload: Time of uploading in ms or if upload failed -1.
    :type time_upload: float
    :param time_connection: Time of connection in ms or if connection failed -1.
    :type time_connection: float
    :param with_error: Was an error in this operation?
    :type with_error: bool
    """
    one_row_stat = {
        'id_datetime': datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
        'host_name': server.config.get('host'),
        'database_name': server.config.get('database'),
        'time_upload_ms': round(time_upload, 3) if time_upload else None,
        'time_connection_ms': round(time_connection, 2) if time_connection else None,
        'count_rows_uploaded': count_rows,
        'count_files_uploaded': count_files,
        'was_error':int(with_error)
    }
    statistics_rows.append(one_row_stat)

def prepare_dyn_data(dynamic_data):
    """
    Prepare dyn data to uploading to db because file and db have different format of data.
    :param dynamic_data: dynamic data from file.
    :type dynamic_data: list
    :return: prepared to uploading dynamic data.
    :rtype: list[list[str]]
    """
    new_dyn_data = []
    for one_data in dynamic_data:
        new_dyn_data.append([str(one_data[0]), str(one_data[1])])
    return [new_dyn_data]

def delete_files_by_date(dir_path, type_files, max_date):
    """
    Delete file with dates (in names files) less or equals than max_date.
    There is compering only by string
    :param dir_path: path to dir with files
    :type dir_path: str
    :param type_files: Type of files e.g. '.log' or '.dat'
    :type type_files: str
    :param max_date: Max deleting date in format 'Y-m-d HM'
    :type max_date: str
    """
    count = 0
    for one_file in os.listdir(dir_path):
        if one_file.endswith(type_files) and max_date >= get_filename_without_extension(one_file):
            os.remove(dir_path + one_file)
            count += 1
    return count

def clean():
    """
    Delete old or broken files. The file is broken if the program can't read the file more than 3 times.
    If the file was read successfully he will delete after 30 minutes.
    """
    number_deleted_files = 0
    try:
        min_last_dates = {
            'data': MAX_DATE,
            'logs': MAX_DATE,
            'logs_syncdata': MAX_DATE,
            'statistics_syncdata': MAX_DATE,
        }
        dates_and_numbers = Logger.read_json_file(Config.LAST_UPLOAD_PATH, do_check_file=True)
        if dates_and_numbers:
            broken_files_for_delete = []
            # Define minimal dates.
            for one_server in dates_and_numbers:
                for data_name in data_names:
                    counter = one_server.get(data_name + '_counter')
                    server_date = one_server.get(data_name)
                    if counter > 3:
                        path_dir, file_type = get_path_and_type_for_name(data_name)
                        broken_file = path_dir + server_date + file_type
                        if broken_file not in broken_files_for_delete:
                            broken_files_for_delete.append(broken_file)

                    min_last_dates[data_name] = min(min_last_dates.get(data_name), datetime.strptime(server_date, '%Y-%m-%d %H%M') - timedelta(minutes=10))

            # Delete files.
            for data_name in data_names:
                paths, type_files = get_path_and_type_for_name(data_name)
                number_deleted_files += delete_files_by_date(paths, type_files, min_last_dates.get(data_name).strftime("%Y-%m-%d %H%M"))

            for broken_file in broken_files_for_delete:
                if delete_by_name(str(broken_file)):
                    number_deleted_files += 1
            Logger.write('Deleted %d files' % number_deleted_files)

    except IOError, e:
        if e.errno == errno.ENOSPC:
            number_deleted_files += clean_no_space()
        Logger.write(('Deleted %d files.' % number_deleted_files if number_deleted_files else '') + 'Can`t delete files - '+ str(sys.exc_info()[1]), Logger.LogType.ERROR)

def clean_no_space():
    number_deleted_files = 0
    for data_name in data_names:
        paths, type_files = get_path_and_type_for_name(data_name)
        files = filter_files_by_date(paths, type_files, MIN_DATE)
        for i in xrange(len(files) // 10):
            if delete_by_name(files[i]):
                number_deleted_files += 1
    return number_deleted_files


# noinspection PyBroadException
def delete_by_name(filepath):
    try:
        os.remove(filepath)
        return True
    except:
        return False

def get_path_and_type_for_name(data_name):
    if data_name == 'data':
        return Config.DATA_DIR, '.dat'
    if data_name == 'logs':
        return Config.DATA_DIR, '.log'
    elif data_name == 'logs_syncdata':
        return Config.MY_LOGS_PATH, '.log'
    elif data_name == 'statistics_syncdata':
        return Config.MY_STATS_PATH, '.stat'

# =========================================================================================================================================================================================
#  START program
# =========================================================================================================================================================================================
print "START SYNCDATA program"
configs_servers = Logger.read_json_file(Config.SERVERS_CONFIG_PATH)
statistics_rows = []
if configs_servers:
    Logger.init_last_upload_date(configs_servers)
    # noinspection PyBroadException
    try:
        for config_server in configs_servers:
            server = DB.Server(**config_server)
            # Try to connect
            if connect_to_db(server):
                full_number_rows = 0
                full_number_files = 0
                broken_files = []

                start_time = time.time()

                # upload dynamic data
                dyn_data = Logger.read_json_file(Config.DYN_DATA_PATH)
                if dyn_data:
                    current_number_files, current_number_rows = upload_data(prepare_dyn_data(dyn_data), [Config.DYN_DATA_PATH], table_in_db.get('dyn_data'))
                    full_number_files += current_number_files
                    full_number_rows += current_number_rows
                else:
                    broken_files.append(Config.DYN_DATA_NAME)
                dyn_data = None
                # Upload data in other tables.
                for table in data_names:
                    path, files_type = get_path_and_type_for_name(table)

                    last_date = Logger.get_last_date(server, get_table_name(table))
                    if last_date is None:
                        last_date = get_last_date_from_server(server, get_table_name(table))
                    last_data_from_files, filenames, list_unread_files = read_files_by_type(path, files_type, last_date)

                    first_broken_read_file = None
                    if len(list_unread_files):
                        broken_files += list_unread_files
                        first_broken_read_file = list_unread_files[0]

                    number_success_upld_files = 0

                    # Upload if read more than nothing
                    if len(last_data_from_files):
                        number_success_upld_files, current_number_rows = upload_data(last_data_from_files, filenames, table_in_db.get(table))
                        full_number_files += number_success_upld_files
                        full_number_rows += current_number_rows

                    # Define last upload or last read date. Save min from this dates.
                    last_date_file = last_date
                    if len(filenames):
                        last_date_file = filenames[-1]
                    if not first_broken_read_file and number_success_upld_files != len(filenames):
                        last_date_file = filenames[number_success_upld_files]
                    if first_broken_read_file:
                        last_date_file = min(first_broken_read_file, last_date_file)

                    last_date_file = get_filename_without_extension(last_date_file)
                    Logger.save_last_upload_dates(server.config.get('host'), server.config.get('database'), get_table_name(table), last_date_file)

                Logger.write(server.config.get('host') + ' -> %d rows in %d files in %4.1fs' %
                             (full_number_rows, full_number_files, time.time() - start_time))

                if len(broken_files):
                    logger_str = "Can't read "
                    logger_str += 'dyndata' if len(broken_files) == 1 and broken_files[0] == Config.DYN_DATA_NAME else ("files  -  %s" % broken_files)
                    Logger.write(logger_str, Logger.LogType.ALARM)

    except:
        Logger.write(str(sys.exc_info()[1]), Logger.LogType.ERROR)

    finally:
        # Save statistics
        # noinspection PyBroadException
        try:
            Logger.save_stat(statistics_rows, table_in_db.get('statistics_syncdata').split('(')[1][:-1].split(", ")[:-1])
        except:
            Logger.write(str(sys.exc_info()[1]), Logger.LogType.ERROR)
        # Delete files
        clean()
else:
    Logger.write('Server`s config is empty', Logger.LogType.WARN)

print "END SYNCDATA program"
