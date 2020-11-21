#!/usr/bin/env python
# -*- coding: utf-8 -*-
import sys

import Config
import Logger
import DB

reload(sys)
sys.setdefaultencoding('utf8')
table_in_db = {
    'tags': 'tags(ID, ID_NAME, ID_DEVICE, SAVE_INTERVAL, VALUE_MIN, VALUE_MAX, TAG_TYPE, ID_FACTORY)',
    'devices': 'devices(BASE_ADDRESS, id_name_device, id_name_full, interface_type, INTERFACE_IP_ADDRESS, interface_ip_port, addr_modbus, id_factory)',
}

conf_devices_from_file = Logger.read_json_file(Config.CONF_DEVICE_PATH)
conf_tags_from_file = Logger.read_json_file(Config.CONF_TAG_PATH)
if conf_devices_from_file is None or conf_tags_from_file is None or len(conf_tags_from_file) == 0 or len(conf_tags_from_file) ==0:
    raise Exception('device or tag file not exists')

def prepare_devices(devices):
    prepared_devices = list()
    for device in devices:
        prepared_devices.append( [
            str(device.get('iBaseAddrTeg')),
            device.get('sModelName'),
            device.get('sModuleInfo'),
            device.get('sInterface'),
            device.get('ip_adress'),
            str(device.get('ip_port')),
            str(device.get('iAdrMODBUS')),
            str(Config.FACTORY_ID)
        ])
    return prepared_devices

def prepare_tags(tags):
    prepared_tags = list()
    for tag in tags:
        prepared_tags.append( [
            str(tag.get('iTegAddr')),
            tag.get('sTegInfo'),
            str(tag.get('iBaseAddrTeg')),
            str(tag.get('iTime_Save')),
            str(tag.get('iMinValue')),
            str(tag.get('iMaxValue')),
            tag.get('sTegType'),
            str(Config.FACTORY_ID)
        ])
    return prepared_tags

configs_servers = Logger.read_json_file(Config.SERVERS_CONFIG_PATH)
statistics_rows = []
if configs_servers:
    conf_tags = prepare_tags(conf_tags_from_file)
    conf_devices = prepare_devices(conf_devices_from_file)
    print 'inside config servers'
    print 'tags - ' + str(len(conf_tags))
    print 'devices - ' + str(len(conf_devices))
    # noinspection PyBroadException

    for config_server in configs_servers:
        try:
            print 'try - ', config_server.get('host')
            server = DB.Server(**config_server)
            connect_time = server.connect()
            server.delete_by_factory_id_and_replace(conf_devices, table_in_db.get('devices'), Config.FACTORY_ID)
            server.delete_by_factory_id_and_replace(conf_tags, table_in_db.get('tags'), Config.FACTORY_ID)
            print 'Success?'
        except:
            print sys.exc_info()[0]
            print sys.exc_info()[1]
            Logger.write('sync tag_dev - ' +str(sys.exc_info()[1]), Logger.LogType.ERROR)
            raise
    print 'End'
else:
    Logger.write('Server`s config is empty', Logger.LogType.WARN)
