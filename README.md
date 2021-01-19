# raspberryPi
Some web and python projects
 - syncdata, synctime - python projects for raspberry pi 3.
 - syncdata_web - pure html, js, css and PHP
 - graph_web, web_electricityConsumption, web_Auth, web_FactoryTemprature - Vue + PHP

### На raspberry на 16.01.2021 есть 5 сервисов на systemd
- get-time-oven - синхронизоция времени с соседними устройствами или с интерентом при запуске raspberry. (запускает synctime)
- sync-data - запускает программу syncdata раз в пару минут вечно. (запускает syncdata)
- sync-tag-dev - синхронизация устройст для коробки с базой данных при запуске. (запускает sync_tags_devices из syncdata)
- unp300 - запускает основную рабочую программу (нет в репе, писал не я)
- unp-upload - закачивает некоторые динамические данные из файлов на хостинг  
Все сервисы можно настроить, использую конфигурационный файлик, лежащий локально на утройстве.
