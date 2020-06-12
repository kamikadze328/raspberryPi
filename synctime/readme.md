Описание:
Программа запрашивает время сначала с интернета, а после, в случае отсутствия подключение к интернету, опрашивает устройста описанные в конфигурации.
Наиболее разумное время программа устанавливает системе. В приоритете время с интернета.
Программа логгирует свои действия в файл.

Для запуска программы достаточно Python 2.7 и две доп. библиотеки:
- ntplib (pip install ntplib или sudo apt-get install python-ntplib)
- enum34 (pip install enum34 или sudo apt-get install python-enum34)

В состав программы входят три файла:
- ModBusAPI.py. 	Предоставляет функции для работы с протоколом ModBus.
- Logger.py. 		Набор функций для логгирования в файлы.
- get_oven_time.py.	Основной исполняемый скрипт. Для корректной работы требуется файл конфигов.

Конфиги:
json файл (default name = "sync_time.conf.json") вида -
[
    {
        "sModelName": "OVEN-AI-MB210-101",
        "sInterface": "TCP_IP",
        "unit_id": 1,
        "ip_address": [
            "192.168.1.32",
            502
        ]
    },
    {
        "sModelName": "OVEN-DI-MB210-221",
        "sInterface": "TCP_IP",
        "unit_id": 1,
        "ip_address": [
            "192.168.1.34",
            502
        ]
    }
]
При отсутствии файла программа завершится с кодом 1.

Результаты работы программы можно увидеть в файле sync_time.log.

----------------------------------------------------------------------
Как запустить демона.
sudo nano /lib/systemd/system/get-time-oven.service

[Unit]
Description=Getting time from OVEN on the starup.
After=multi-user.target
Wants=network.target

[Service]
Type=idle			
Restart=on-failure
RestartSec=5
ExecStart=/usr/bin/python /home/pi/sk/synctime/get_time_oven.py
WorkingDirectory=/home/pi/sk/synctime

[Install]
WantedBy=multi-user.target


Комментарии:
Type=idle - The effect of this service type is subject to a 5s timeout, after which the service program is invoked anyway.

sudo systemctl daemon-reload
sudo systemctl enable get-time-oven
sudo reboot

Команда для просмотра статуса демона.
sudo systemctl status get-time-oven

Теперь данный система будет запускать демона после выполнения всех остальных демонов(Type=idle)
https://www.dexterindustries.com/howto/run-a-program-on-your-raspberry-pi-at-startup/
https://jlk.fjfi.cvut.cz/arch/manpages/man/systemd.service.5#OPTIONS
