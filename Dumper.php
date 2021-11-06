<?php

namespace DbBackup;

use Exception;

/**
 * Class Dumper
 * @package DbBackup
 */
class Dumper extends Backup
{
    protected $dumperApp = self::DUMPER_MYSQLDUMP;
    protected $dbHost;
    protected $dbPort;
    protected $dbUser;
    protected $dbPassword;
    protected $dbName;
    protected $dumpFileName = self::FILE_NAME_FULL;
    
    public const DUMPER_MYSQLDUMP = 'mysqldump';
    public const DUMPER_MYDUMPER = 'mydumper';
    
    public const FILE_NAME_FULL = 'full';
    public const FILE_NAME_WEEKDAY_NUMBER = 'weekday-number';
    
    /**
     * @param array $vars
     * @param string|null $envPath
     * @throws \Exception
     */
    public function setAuthFromEnv(array $vars = [], ?string $envPath = null): void
    {
        $vars = [
            'dbName' => 'DB_ENV_MYSQL_DATABASE',
            'dbUser' => 'DB_ENV_MYSQL_USER',
            'dbPassword' => 'DB_ENV_MYSQL_PASSWORD',
            'dumpFileName' => 'BACKUP_DUMP_FILE_NAME_FORMAT',
            'dumperApp' => 'BACKUP_DUMPER_APP'
        ];
        parent::setAuthFromEnv($vars);
    }
    
    public function run()
    {
        echo 'Running DB Dumper....' . PHP_EOL;
        echo '[Dumper App] ' . $this->dumperApp . PHP_EOL;
        echo '[Interval] ' . $this->getInterval() . PHP_EOL;
        echo '[Database] ' . $this->dbName . PHP_EOL;
        echo '[Dump Path] ' . $this->getDumpFilePath() . PHP_EOL;
        echo '[Cron command] ' . $this->getCronCommand() . PHP_EOL;
        echo '[Cron Log Path] ' . $this->getLogPath() . PHP_EOL;
    
        return parent::run();
    }
    
    /**
     * @return string
     */
    public function getDumpFilename(): string
    {
        switch ($this->dumpFileName) {
            case self::FILE_NAME_WEEKDAY_NUMBER:
                $date = new \DateTime();
                $filename = $date->format('N') . '.sql';
                break;
            default:
                $filename = date('d.m.Y-H-i') . '.sql';
        }
        return $filename;
    }
    
    /**
     * @param string|null $path
     */
    public function getDumpFilePath(string $path = null): string
    {
        return self::DUMPER_MYDUMPER === $this->dumperApp
            ? dirname(__FILE__, 1) . '/runtime/backup/' . $this->dbEngine . '/' . $this->getInterval()
            : dirname(__FILE__, 1) . '/runtime/backup/' . $this->dbEngine . '/' . $this->getInterval() . '/' . $this->getDumpFilename();
    }
    
    /**
     * @return string
     */
    public function getExecCommand(): string
    {
        return self::DUMPER_MYDUMPER === $this->dumperApp
            //TODO - add option for app params e.g. -c -C -t 1 -r 5000
            // default param --lock-all-tables to avoid " Access denied; you need (at least one of) the RELOAD privilege(s)" error.  See: https://coderedpanda.wordpress.com/2020/04/11/mydumper-taking-consistent-backups/
            ? '/usr/bin/mydumper -u ' . $this->dbUser . ' -p ' . $this->dbPassword . ' -B ' . $this->dbName . ' -o ' . $this->getDumpFilePath() . ' -c -C -t 1 -r 5000 --lock-all-tables'
            : 'mysqldump -u ' . $this->dbUser . ' -p' . $this->dbPassword . ' ' . $this->dbName . ' > ' . $this->getDumpFilePath();
    }
    
    /**
     * @return string
     */
    public function getLogPath(): string
    {
        return $this->logPath ?? dirname(__FILE__, 1) . '/runtime/log/' . $this->dumperApp . '-' . $this->getInterval() . '.log';
    }
    
    /**
     * @return string
     */
   /* public function getCronPath(): string
    {
        return $this->cronPath ?? __FILE__;
    }*/
    
    
    /**
     * @param string $interval
     * @return string
     */
    public function getCronCommand(): ?string
    {
        if (!$this->cronPath) {
            echo 'Cron path not set';
            return null;
        }
        $interval = $this->getInterval();
        return $this->getCronTimerDef($interval) . ' /usr/bin/php ' . $this->getCronPath() . ' >> ' . $this->getLogPath() . ' 2>&1';
    }
}