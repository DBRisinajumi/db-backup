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
    
    public const DUMPER_MYSQLDUMP = 'mysqldump';
    public const DUMPER_MYDUMPER = 'mydumper';
    
    /**
     * @throws \Exception
     */
    /*public function initFromEnv(?string $envPath = null, array $vars): void
    {
        $this->dbUser = DB_ENV_MYSQL_USER;
        $this->dbPassword = DB_ENV_MYSQL_PASSWORD;
        $this->dbName = DB_ENV_MYSQL_DATABASE;
        $this->dbPort = DB_PORT_3306_TCP_PORT;
        $this->dbHost = DB_PORT_3306_TCP_ADDR;        
    }*/
    
    /**
     *
     */
    public function init(): void
    {
        echo 'Running DB Dumper....' . PHP_EOL;
        echo '[Dumper App] ' . $this->dumperApp . PHP_EOL;
        echo '[Database] ' . $this->dbName . PHP_EOL;
        echo '[Dump Path] ' . $this->getDumpFilePath() . PHP_EOL;
        echo '[Exec] ' . $this->getExecCommand() . PHP_EOL;
        echo '[Cron command] ' . $this->getCronCommand() . PHP_EOL;
        echo '[Cron Log Path] ' . $this->getLogPath() . PHP_EOL;
    }
    
    /**
     * @param string|null $path
     */
    public function getDumpFilePath(string $path = null): string
    {
        return self::DUMPER_MYDUMPER === $this->dumperApp
            ? dirname(__FILE__, 1) . '/runtime/backup/' . $this->dbEngine . '/' . $this->getInterval()
            : dirname(__FILE__, 1) . '/runtime/backup/' . $this->dbEngine . '/' . $this->getInterval() . '/' . date('d.m.Y-H-i') . '.sql';
    }
    
    /**
     * @return string
     */
    public function getExecCommand(): string
    {
        return self::DUMPER_MYDUMPER === $this->dumperApp
            ? 'mydumper -u ' . $this->dbUser . ' -p ' . $this->dbPassword . ' -B ' . $this->dbName . '    -o ' . $this->getDumpFilePath() . ' -c -C -t 1 -r 5000'
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
    public function getCronPath(): string
    {
        return $this->cronPath ?? __FILE__;
    }
    
    /**
     * @return string|null
     */
    public function getInterval(): ?string
    {
        global $argv;
        //var_dump($argv);
        return $argv[1] ?? self::INTERVAL_LATEST;
    }
    
    /**
     * @param string $interval
     * @return string
     */
    public function getCronCommand(): string
    {
        $interval = $this->getInterval();
        return $this->getCronTimerDef($interval) . ' www-data /usr/bin/php ' . $this->getCronPath() . " -i '" . $this->getInterval() . "'  > " . $this->getLogPath();
    }
}