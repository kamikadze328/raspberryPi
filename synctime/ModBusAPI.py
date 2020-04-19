#!/usr/bin/env python
# -*- coding: utf-8 -*-
import socket
import time
from random import randint
from enum import Enum

import Logger

class InterfaceType(Enum):
    """
    Enumeration of interface type for ModBus protocol
    """
    RS_485 = 1
    TCP_IP = 2
class Checksum(Enum):
    """
    Enumeration of checksum type
    """
    Length = 1
    CRC = 2
    All = 3


def make_send_packet_tcp(unit_id, function_code, register_hi, register_lo, number_reg_lo, number_reg_hi=0x00):
    """ Function make a packet for sending data.
    There is a limit on total number of registers from 0 to 2 bytes,
    because I don't want to calculate length.
    All parameters should less or equal then 1 byte.

    :param unit_id: The id of unit. From 0 to 247.
    :type unit_id: int

    :param function_code: Code for reading or writing.
        Read documentation of ModBus.
    :type function_code: int or hexadecimal

    :param register_hi: High byte of register.
    :type register_hi: int or hexadecimal

    :param register_lo: Low byte of register.
     :type register_lo: int or hexadecimal

    :param number_reg_hi:High byte of register's total number.
        value is ''0x00'' (the default)
     :type number_reg_hi: int or hexadecimal

    :param number_reg_lo: Low byte of register's total number.
     :type number_reg_lo: int or hexadecimal

    :returns:
        - byte_arr - the sending packet
        - transact_id -  the transactional id.
    :rtype: tuple[bytearray, tuple[int, int]]

    """
    byte_arr = bytearray()

    # MBAP Header ( 7 bytes )
        # Random transactional id - 2 bytes.
    transact_id_hi = randint(0, 255)
    transact_id_lo = randint(0, 255)
    byte_arr.append(transact_id_hi)
    byte_arr.append(transact_id_lo)

        # Protocol id - 2 bytes. Always 00 00 for ModBus
    byte_arr.append(0x00)
    byte_arr.append(0x00)

        # Length - 2 bytes. Length of message from unit id to end
    byte_arr.append(0x00)
    byte_arr.append(0x06)

        # unit Id (Unit id) - 1 byte.
    byte_arr.append(unit_id)

    #Protocol Data Unit
    byte_arr.append(function_code)
    byte_arr.append(register_hi)
    byte_arr.append(register_lo)
    byte_arr.append(number_reg_hi)
    byte_arr.append(number_reg_lo)

    transact_id = (transact_id_hi , transact_id_lo)
    return byte_arr, transact_id



def send_packet_tcp(ip_address, send_byte_arr, transact_id, expected_number_bytes, type_checksum=Checksum.Length):
    """
    Send the packet to the unit.
    There is a timeout on answer from host in 0.1s.
    It checks transactional id and checksum.

    :param ip_address: Host and port
     :type ip_address: tuple[str, int]
    :param send_byte_arr: A sending packet. Usually gets from :func:'ModBusAPI.send_packet'
     :type send_byte_arr: bytearray
    :param transact_id: Transactional id. Usually gets from :func:'ModBusAPI.send_packet'
     :type transact_id: tuple[int, int]
    :param expected_number_bytes: The number of bytes in a response
     :type expected_number_bytes: int
    :param type_checksum: Type of checksum from enum
     :type type_checksum: Checksum
    :return:
            - err_code -
                         0 if all is ok,
                        -1 if the socket didn`t open,
                        -2 if a response isn't received
                        -3 if something wrong with a response
            - received_byte_arr - received byte array
            - connect_info_str - Connection info  with time for logging.
                                One line for each info message.
                                There is also info about error
    :rtype: tuple[int, bytearray, str]
    """
    if not isinstance(ip_address, tuple):
        ip_address = tuple(ip_address)
    timeout = 0.1
    err_code = -1
    received_byte_arr = bytearray()
    time_socket_open = time.time()

    connect_info_str = Logger.time() + (u'Connection to (host: %s, port: %d) ==> '% ip_address)
    ip_socket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    ip_socket.settimeout(timeout)
    socket_error = ip_socket.connect_ex(ip_address)

    if socket_error == 0:
        connect_info_str += u'[OK] %4.1fms' % ((time.time() - time_socket_open) * 1000)
        try:
            ip_socket.send(send_byte_arr)
            err_code = -2
            time.sleep(timeout)
            data = ip_socket.recv(128)
        except socket.error:
            connect_info_str += '\n' + Logger.time() +  u'ERROR: Socket Timeout'
        else:
            err_code = -3
            for byte in data:
                received_byte_arr.append(byte)
            if transact_id != (received_byte_arr[0], received_byte_arr[1]):
                connect_info_str += '\n' + Logger.time() +  u'ERROR: Response has wrong transactional id'
            elif type_checksum == Checksum.Length:
                if len(received_byte_arr) != expected_number_bytes:
                    connect_info_str += '\n' + Logger.time() +  u'ERROR: Wrong CRC'
                else: err_code = 0
    else:
        connect_info_str += u'[ERROR]: Socket didn`t open'
    ip_socket.close()
    return err_code, received_byte_arr, connect_info_str

def make_send_packet(**data):
    """
    Manage calling in accordance with a type of interface
    :param data: params to calling method
    :return: return something from calling function
    """
    interface_type = data.pop('interface_type')
    if interface_type == InterfaceType.TCP_IP:
         return make_send_packet_tcp(**data)

def send_packet(**data):
    """
    Manage calling in accordance with a type of interface
    :param data: params to calling method
    :return: return something from calling function
    """
    interface_type = data.pop('interface_type')
    if interface_type == InterfaceType.TCP_IP:
        return send_packet_tcp(**data)
