from jproperties import Properties

def read_config_file(file_path):
    configs = Properties()

    with open(file_path, 'rb') as config_file:
        configs.load(config_file)

    items_view = configs.items()
    db_configs_dict = {}

    for item in items_view:
        db_configs_dict[item[0]] = item[1].data
    return db_configs_dict

NEW_CONFIG_FILE = '/var/www/html/config/configs.properties'
CURRENT_CONFIGS = read_config_file(NEW_CONFIG_FILE)
#Paths to my files
SERVERS_CONFIG_PATH = CURRENT_CONFIGS.get('SERVERS_CONFIGS_FILE')
WORKING_DIR = CURRENT_CONFIGS.get('SYNCDATA_DATA_DIR') + '/'

#my files
MY_LOGS_PATH = WORKING_DIR + 'logs/'
MY_STATS_PATH = WORKING_DIR + 'stats/'
LAST_UPLOAD_PATH = WORKING_DIR + 'last_upload_date.json'
STATISTICS_PATH = WORKING_DIR + 'statistics.json'

#not my files
DATA_DIR = CURRENT_CONFIGS.get('DATA_DIR') + '/'
DYN_DATA_PATH = CURRENT_CONFIGS.get('DYN_DATA_FILE')
DYN_DATA_NAME = DYN_DATA_PATH.split('/')[-1]


FACTORY_ID=CURRENT_CONFIGS.get('FACTORY_ID')


#files for sync tags and device
CONF_DEVICE_PATH=CURRENT_CONFIGS.get('CONF_DEVICE_FILE')
CONF_TAG_PATH=CURRENT_CONFIGS.get('CONF_TAG_FILE')
