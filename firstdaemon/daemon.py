#! /usr/bin/env python
# coding=utf-8
import os.path
import time
from datetime import datetime as d
from random import randint
import DB


count = 10
j = 0
isStr = False
while True:
    log = []
    start_time = d.now()
    server1 = DB.Server('VH246.spaceweb.ru', 'carbondvru_DB',
                        '12345678Db', 'carbondvru_DB')
    end_time = d.now()
    log.append((end_time - start_time))

    server1.delete_all()
    log.append(d.now() - end_time)
    end_time = d.now()

    data = [(randint(1, 100000000), ('kekekek'), randint(1000, 1000000)), ]
    for i in range(count):
        data.append((i, (d.now()).strftime('%Y-%m-%d %H:%M:%S'), randint(1000, 1000000)))

    log.append(d.now() - end_time)
    end_time = d.now()
    if isStr:
        server1.insert_many_str(data)
    else:
        server1.insert_many(data)
    log.append(d.now() - end_time)
    end_time = d.now()

    server1.load_data()
    log.append(d.now() - end_time)

    file_path = "/home/pi/sk/log2.txt"
    if not os.path.exists(file_path):
        with open(file_path, 'w') as f:
            f.writelines('connected,      deleted all,    data completed, inserted,       selected,       count\n')
            for ele in log:
                f.write(str(ele) + ', ')
            f.write(str(count) + ', '+ str(isStr) + ',  ' + d.now().strftime('%Y-%m-%d %H:%M:%S') +'\n')
    else:
        with open(file_path, 'a') as f:
            for ele in log:
                 f.write(str(ele) + ', ')
            f.write(str(count) + ', '+ str(isStr) + ',  ' + d.now().strftime('%Y-%m-%d %H:%M:%S') +'\n')

    if j > 8:
        count *=10
        isStr = False
        j = 0

    if j > 3:
        isStr = True
    j = j + 1
    time.sleep(1)
