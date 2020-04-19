import codecs
import os
from datetime import datetime as d


def time():
    return unicode(d.now().strftime('%Y-%m-%d %H:%M:%S')+ ':\t')

def write(file_path, message, with_time=True):
    if with_time:
        message = unicode(time() + message + '\n')
    else: message = unicode(message + '\n')
    if not os.path.exists(file_path):
        with codecs.open(file_path, 'w', encoding='utf-8') as f:
            f.write(message)
    else:
        with codecs.open(file_path, 'a', encoding='utf-8') as f:
            f.write(message)
