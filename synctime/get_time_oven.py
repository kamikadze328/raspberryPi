#!/usr/bin/env python
# -*- coding: utf-8 -*-

import datetime
import json
import os
import sys
import time

import ntplib

import Logger
import ModBusAPI

# Устранение проблем с кодировкой UTF-8
reload(sys)
sys.setdefaultencoding('utf8')

#log and config files
current_path = os.path.dirname(os.path.abspath(__file__))
log_file = current_path + "/sync_time.log"
config_file = current_path + "/sync_time.conf.json"

number_bytes = 15
time_register = [0xF0, 0x80]
number_time_register = 3
function_write_time = 0x03

times_from_units= []

# Прилетает время в дебильном формате Дата/Время в секундах с 1 января 2000 г.
# + смещение от гринвича в минутах отдельными двумя байтами (180 для москвы)
# 256*256*256*byte_data[11] + 256*256*byte_data[12] + 256*byte_data[9] + byte_data[10]
# Собираем смещение 256*byte_data[13] + byte_data[14]
def calculate_oven_time(time_arr):
    date_time_seconds = 256 * 256 * 256 * time_arr[2] + 256 * 256 * time_arr[3] + 256 * time_arr[0] + time_arr[1] \
                        + (256 * time_arr[4] + time_arr[5]) * 60
    return datetime.datetime(2000, 1, 1) + datetime.timedelta(seconds=date_time_seconds)

def get_ethernet_time():
    """
    Try to get time from network
    :return: Unix time in seconds if success
        or None
    :rtype: float
    """
    ntp_client = ntplib.NTPClient()
    time_info_str = Logger.time() + u'Checking network time ==> '
    try:
        start_check_time = time.time()
        response = ntp_client.request('pool.ntp.org')
        Logger.write(log_file, time_info_str + u'[OK] %4.1fms' % ((time.time() - start_check_time) * 1000), with_time=False)
        return response.tx_time
    except (ntplib.NTPException, Exception):
        Logger.write(log_file, time_info_str + u'[WARNING]: No internet connection', with_time=False)


def set_system_time(new_time, name_time_source):
    """
    If difference between new and system time more than 1s, set new time(sudo date -s %Y-%m-%d %H:%M:%S)
    :param new_time: Setting time
    :type new_time: datetime.datetime or int or float
    :param name_time_source: The name of source of time
    :type name_time_source: unicode or str
    """
    if isinstance(new_time, int) or isinstance(new_time, float):
        new_time = datetime.datetime.fromtimestamp(new_time)

    if abs(datetime.datetime.now() - new_time).total_seconds() < 1:
        Logger.write(log_file, u'The system time is correct')
    else:
        new_time = new_time.strftime('%Y-%m-%d %H:%M:%S')
        Logger.write(log_file, (u'Set system time from %s = ' % name_time_source) + new_time)
        os.system('sudo date -s "' + new_time + '"')

def send_request_to_units(units):
    """
    Make packet and send it via protocol ModBus.
    :param units: list of unit from json config
    :type units: list
    :return: list units, which had error during getting time
    :rtype: list
    """
    error_codes = []
    for unit in units:
        interface = ModBusAPI.InterfaceType[str(unit.get('sInterface'))]

        if interface == ModBusAPI.InterfaceType.TCP_IP:
            data_send_packet = {
                'unit_id': unit.get('unit_id'),
                'function_code': function_write_time,
                'register_hi': time_register[0],
                'register_lo': time_register[1],
                'number_reg_lo': number_time_register,
            }
            buff_send_arr, transact_id_str = ModBusAPI.make_send_packet_tcp(**data_send_packet)

            meta_send_packet = {
                'ip_address': unit.get('ip_address'),
                'type_checksum': ModBusAPI.Checksum.Length,
                'expected_number_bytes': number_bytes,
                'send_byte_arr': buff_send_arr,
                'transact_id': transact_id_str,
            }
            error_code, byte_data, info_str = ModBusAPI.send_packet_tcp(**meta_send_packet)
            Logger.write(log_file, info_str, with_time=False)
            error_codes.append(error_code)

            if error_code is not -1:
                Logger.write(log_file, u'request  (%d bytes) => (HEX)' % len(buff_send_arr)
                             + ''.join(' {:02X}'.format(x) for x in buff_send_arr))
                Logger.write(log_file, u'response (%d bytes) => (HEX)' % len(byte_data)
                             + ''.join(' {:02X}'.format(x) for x in byte_data))

            if error_code == 0:
                unit_time = calculate_oven_time(byte_data[-6:])
                times_from_units.append(unit_time)
                Logger.write(log_file, u'The decoding time of %s = ' % unit.get('sModelName')
                             + unit_time.strftime('%Y-%m-%d %H:%M:%S'))
        elif interface==ModBusAPI.InterfaceType.RS_485:
            # Надо дописать методы в ModBusAPI для работы с этим протоколом. И объединить код с tcp
            pass
    return [unit_with_error for index, unit_with_error in enumerate(units) if error_codes[index] < 0]

def median(array):
    n = len(array)
    return sorted(array)[n / 2 + n % 2]

# -------------------------------------------------------------------------------------------------
time.sleep(1)
Logger.write(log_file, u'Start')
try:
    #cheching network time
    network_time = get_ethernet_time()
    if network_time:
        Logger.write(log_file, u'The network time = '  + datetime.datetime.fromtimestamp(network_time).strftime('%Y-%m-%d %H:%M:%S'))
        set_system_time(network_time, u'network')

    elif os.path.isfile(config_file):
        with open(config_file) as json_file:
            units_from_json = json.load(json_file)

        units_with_error = send_request_to_units(units_from_json)

    # repeat request to wrong units
        if len(units_with_error) != 0:
            time.sleep(1)
            Logger.write(log_file, u'Sending requests to units with error:')
            send_request_to_units(units_with_error)

        if len(times_from_units) > 0:
            set_system_time(median(times_from_units), u'controller')
        else:
            Logger.write(log_file, u'There isn`t time... ')
    else:
        Logger.write(log_file, u'There isn`t config file: %s' % config_file)
        sys.exit(1)


except:
    Logger.write(log_file, u'[ERROR] ' + unicode(sys.exc_info()))
finally:
    Logger.write(log_file, u'END')

