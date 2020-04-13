#! /usr/bin/env python
# coding=utf-8
from random import randint

import DB
import datetime as d

server1 = DB.Server('VH246.spaceweb.ru', 'carbondvru_DB',
                 '12345678Db', 'carbondvru_DB')
server1.load_data()
server1.delete_all()
data_1 = (randint(1, 100), d.datetime.now(), randint(1000, 1000000))
data_2 = (randint(1, 100), d.datetime.now(), randint(1000, 1000000))
data_3 = (randint(1, 100), d.datetime.now(), randint(1000, 1000000))
print ""
server1.load_data()
print ""
server1.insert_data(data_1)
server1.load_data()
print ""

data = [
    (randint(1, 100), d.datetime.now(), randint(1000, 1000000)),
    (randint(1, 100), d.datetime.now(), randint(1000, 1000000)),
    (randint(1, 100), d.datetime.now(), randint(1000, 1000000)),
]
server1.insert_many(data)

server1.load_data()
print ""
#server1.insert_and_load(data_3)


