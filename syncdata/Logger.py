import codecs
import os
from datetime import datetime as d

from enum import Enum

class LogType(Enum):
    FATAL   =   1
    ERROR   =   2
    WARN    =   3
    INFO    =   4

__current_path = os.path.dirname(os.path.abspath(__file__)) + '/'
__log_file = __current_path + d.now().strftime("%Y-%m-%d") + '.log'

def get_current_time():
    return unicode(d.now().strftime('%Y-%m-%d %H:%M:%S'))

def make_message(message, log_type=None, time=get_current_time()):
    if isinstance(time, d):
        time = time.strftime('%Y-%m-%d %H:%M:%S')
    log_type = '[------]' if log_type is None else '[{:6}]'.format(log_type.name)
    return unicode(time +'  ' + log_type + ' ' + message + '\n')

# noinspection DuplicatedCode
def write(message, log_type=None, file_path=__log_file):
    message = make_message(message, log_type)

    if not os.path.exists(file_path):
        with codecs.open(file_path, 'w', encoding='utf-8') as f:
            f.write(message)
    else:
        with codecs.open(file_path, 'a', encoding='utf-8') as f:
            f.write(message)
