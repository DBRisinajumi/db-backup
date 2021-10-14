##Install

Add the repository to composer.json
```php
    "repositories": [
    {
      "type": "git",
      "url": "https://github.com/DBRisinajumi/db-backup.git"
    }
  ],
```

Add to require section
```php
"require": {
   "dbrisinajumi/dbbackup": "dev-master"
},
```
##Database dumper
### Usage via Yii2 console command and .env
Set the DB access variables in .env file
```php
DB_ENV_MYSQL_DATABASE=dbname
DB_ENV_MYSQL_USER=dbname_php
DB_ENV_MYSQL_PASSWORD=secret
BACKUP_DUMP_FILE_NAME_FORMAT=weekday-number
```
Add the controller to config/console.php
```php
'controllerMap' => [
        'backup' => [
            'class' => 'DbBackup\controllers\yii2\BackupController',
        ],
```
Example cron command
```php
0 4 * * *  /usr/bin/php [SITE PATH]/yii backup/dump -i=daily >> [SITE PATH]/vendor/dbrisinajumi/dbbackup/runtime/log/mysqldump-daily.log 2>&1
```

### Usage via custom script

```php
use DbBackup\Dumper;

// include your own config files with access constants and autoload;
require dirname(__FILE__, 2) . '/config.inc';
require CMS_VENDOR_PATH . 'autoload.php';

// Run the Dumper with necessary params
$dumper = new Dumper(
    [
        'dbUser' => CMS_DBUSER,               // Database user
        'dbPassword' => CMS_DBPASS,           // Database password
        'dbName' => CMS_DBASE,                // Database name
        'dbHost' => CMS_DBSERVER,             // Database host
        'interval' => Dumper::INTERVAL_DAILY, // Interval name (used for dump file and backup folder naming)
        'cronPath' => __FILE__,               // Path for cron (used for generated cron command example)  
        
        // Optional params
        'dumpFileName' => Dumper::FILE_NAME_WEEKDAY_NUMBER, // Set the dump file names to rewritable (weekday numbers like 1.sql, 2.sql) to reduce disk space
        'dumperApp' => parent::DUMPER_MYDUMPER,             // Set mydumper as alternative (may not work correctly yet)
    ]
);
echo $dumper->run();
```

## Upload Backups to remote server
### Usage via Yii2 console command and .env

Set the backup server access variables in .env file

```php
BACKUP_DUMP_FILE_NAME_FORMAT = weekday-number
BACKUP_SERVER_HOST=backups.example.com
BACKUP_SERVER_USERNAME=backupuser
BACKUP_SERVER_PASSWORD=secretpassword
BACKUP_SERVER_PATH=my.website.com
```

Add the controller to config/console.php

```php
'controllerMap' => [
        'backup' => [
            'class' => 'DbBackup\controllers\yii2\BackupController',
        ],
```

Example cron command
```php
0 4 * * *  /usr/bin/php [SITE PATH]/yii backup/sync -i=daily >> [SITE PATH]/vendor/dbrisinajumi/dbbackup/runtime/log/mysqldump-daily.log 2>&1
```

### Usage via custom script

```php
use DbBackup\Sync;

// include your own config files with access constants and autoload;
require dirname(__FILE__, 2) . '/config.inc';
require CMS_VENDOR_PATH . 'autoload.php';

$sync = new Sync([
    'host' => BACKUP_SERVER_HOST,
    'user' => BACKUP_SERVER_USERNAME,
    'password' => BACKUP_SERVER_PASSWORD,
    'remotePath' => BACKUP_SERVER_PATH,
    'cronPath' => __FILE__,
]);
echo $sync->run();
```