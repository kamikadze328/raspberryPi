import codecs
import json
import os
from datetime import datetime as d

from enum import Enum


class LogType(Enum):
    FATAL   =   1
    ERROR   =   2
    WARN    =   3
    INFO    =   4

def get_current_filepath():
    return os.path.dirname(os.path.abspath(__file__)) + '/' + 'logs/' + d.now().strftime("%Y-%m-%d %H%M") + '.log'

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
        file_path = get_current_filepath()
    if not os.path.exists(file_path):
        with codecs.open(file_path, 'w', encoding='utf-8') as f:
            f.write(message)
    else:
        with codecs.open(file_path, 'a', encoding='utf-8') as f:
            f.write(message)

def write_last_upload(last_upload_date, filename):
    with open(filename, 'w') as outfile:
        json.dump(last_upload_date, outfile, indent=4)
