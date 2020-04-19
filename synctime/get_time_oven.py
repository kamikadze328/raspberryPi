#!/usr/bin/env python
# -*- coding: utf-8 -*-

# Устранение проблем с кодировкой UTF-8
import json
import sys
import time, os, datetime
import Logger
import ModBusAPI
reload(sys)
sys.setdefaultencoding('utf8')


number_bytes = 15
time_register = [0xF0, 0x80]
number_time_register = 3
function_write_time = 0x03
log_file = "/home/pi/sk/synctime/sync_time.log"

def calculate_time():
    date_time_seconds = 256 * 256 * 256 * byte_data[11] + 256 * 256 * byte_data[12] + 256 * byte_data[9] \
                        + byte_data[10] + (256 * byte_data[13] + byte_data[14]) * 60
    return datetime.datetime(2000, 1, 1) + datetime.timedelta(seconds=date_time_seconds)


#-------------------------------------------------------------------------------------------------
Logger.write(log_file, u'Start')

with open('sync_time.conf.json') as json_file:
    units = json.load(json_file)

for unit in units:
    interface = ModBusAPI.InterfaceType[str(unit.get('sInterface'))]
    data_send_packet = {
        'unit_id': unit.get('unit_id'),
        'function_code': function_write_time,
        'register_hi': time_register[0],
        'register_lo': time_register[1],
        'number_reg_lo': number_time_register,
        'interface_type': interface,
    }
    buff_send_arr, transact_id_str = ModBusAPI.make_send_packet(**data_send_packet)
    meta_send_packet = {
        'ip_address': unit.get('ip_address'),
        'type_checksum': ModBusAPI.Checksum.Length,
        'expected_number_bytes': number_bytes,
        'send_byte_arr': buff_send_arr,
        'transact_id': transact_id_str,
        'interface_type': interface,
    }
    error_code, byte_data, info_str = ModBusAPI.send_packet(**meta_send_packet)
    Logger.write(log_file, info_str, with_time=False)
    if error_code is not -1:
        Logger.write(log_file, u'request  (%d bytes) => (HEX)' % len(buff_send_arr) + ''.join(' {:02X}'.format(x) for x in buff_send_arr))
        Logger.write(log_file, u'response (%d bytes) => (HEX)' % len(byte_data) + ''.join(' {:02X}'.format(x) for x in byte_data))

    if error_code == 0:
        unit_time = calculate_time()
        Logger.write(log_file, u'The decoding system time of %s = ' % unit.get('sModelName') + unit_time.strftime("%Y-%m-%d %H:%M:%S"))

    # установить время системы из питона примерно так
    # sDateTimeSet = dtOven.strftime('"%Y-%m-%d %H:%M:%S"')
    # os.system('sudo date -s ' + sDateTimeSet)

# ---------------------------------------------------------------------------------------------------------------------------------------------------------------------#'
Logger.write(log_file, u'END')


# Прилетает время в дебильном формате Дата/Время в секундах с 1 января 2000 г.
# + смещение от гринвича в минутах отдельными двумя байтами (180 для москвы)
# 256*256*256*byte_data[11] + 256*256*byte_data[12] + 256*byte_data[9] + byte_data[10]
# Собираем смещение 256*byte_data[13] + byte_data[14]

