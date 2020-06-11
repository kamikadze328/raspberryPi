Описание:
Программа загружает в базы данных информацию из локальных файлов. Каждый успешно считанный и отправленный файл удаляется. 
Если что-то пошло не так, то файл удаляется после трёх неудачных попыток отправки или прочтения. Удаляются ВСЕ свои файлы и немного чужих.

Для запуска программы достаточно Python 2.7 и две доп. библиотеки:
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
