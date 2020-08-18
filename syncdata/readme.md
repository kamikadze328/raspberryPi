Описание:
-----------------------
Программа загружает в базы данных информацию из локальных файлов. Каждый успешно считанный и отправленный файл удаляется. 
Если что-то пошло не так, то файл удаляется после трёх неудачных попыток отправки или прочтения. Удаляются ВСЕ свои файлы и немного чужих.

Для запуска программы достаточно Python 2.7 и три доп. библиотеки:
- enum34 (pip install enum34 или sudo apt-get install python-enum34)
- mysqldb (pip install mysqldb или sudo apt-get install python-mysqldb)
- mysql.connector (pip install mysql.connector или sudo apt-get install python-mysql.connector)

В состав программы входят три файла:
- DB.py. 			Предоставляет собой обёртку для работы с базами данных.
- Logger.py. 		Набор функций для записи и чтения в файлы.
- sync_data.py.		Основной исполняемый скрипт. Для корректной работы требуется файл конфигов.

Конфиги:
json файл (default name = "sync_time.conf.json") вида - 
[    
  {  
    "host":"host.name",  
    "user":"user.name",  
    "password":"password",  
    "database": "db.name"  
  },  
  {  
    "host":"host.name1",  
    "user":"user.name1",  
    "password":"password1",  
    "database": "db.name1"  
  }  
]

Программа создаёт для себя:
- last_upload_date.json.		В файле хранятся даты последних загруженных файлов и количетсво попыток их загрузить.
- stats/*.stat.					Файлы со статистикой работы программы.
- logs/*.log.					Файлы с логами.


Как запустить демона.  
----------------------------------------
sudo nano /lib/systemd/system/sync-data.service
Пишем в файл следующее:
  
#####[Unit]  
Description=Sync data with db.  
After=multi-user.target  
Wants=network.target  
  
#####[Service]  
Type=idle  
Restart=always   
RestartSec=60  
RuntimeMaxSec=21600  
ExecStart=/usr/bin/python /home/pi/sk/syncdata/sync_data.py  
WorkingDirectory=/home/pi/sk/syncdata  
  
#####[Install]  
WantedBy=multi-user.target   
  
  
#####Комментарии:  
Type=idle - The effect of this service type is subject to a 5s timeout, after which the service program is invoked anyway.
RuntimeMaxSec=21600 - 6 hours 

sudo systemctl daemon-reload  
sudo systemctl enable sync-data  
sudo reboot  
  
Команда для просмотра статуса демона.  
sudo systemctl status sync-data  
  
Теперь данный система будет запускать демона после выполнения всех остальных демонов(Type=idle)    
https://www.dexterindustries.com/howto/run-a-program-on-your-raspberry-pi-at-startup/  
https://jlk.fjfi.cvut.cz/arch/manpages/man/systemd.service.5#OPTIONS  


Создание директории в ОЗУ:
------------------------------------------------------------------
Пусть будет директория /var/tmp  
  
mkdir /var/tmp  
sudo nano /etc/fstab  
  
Здесь добавляем строчку:  
tmpfs /var/tmp tmpfs defaults 0 0  
Сохраняем  
sudo mount -a  
sudo reboot  
  
Проверка результата:  
df  
   
Должна быть подобная строчка  
tmpfs   474152   36   474116   0%   /var/tmp  

Check lifetime sd card
----------------------
uptime  
cat /sys/block/mmcblk0/stat | awk '{printf "Uptime read: %.3fMiB (%.1f%% I/Os merged) written: %.3f MiB (%.1f%% I/Os merged)\n", $3*512/1048576, $2/$1*100, $7*512/1048576, $6/$5*100}'  
