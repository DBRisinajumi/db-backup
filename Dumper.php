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
            'dumperApp' => 'BACKUP_DUMP_ENGINE'
        ];
        parent::setAuthFromEnv($vars);
    }
    
    public function run()
    {
        echo 'Running DB Dumper: ' . date('d.m.Y H:i') . PHP_EOL;
        echo '[Dumper App] ' . $this->dumperApp . PHP_EOL;
        echo '[Interval] ' . $this->getInterval() . PHP_EOL;
        echo '[Database] ' . $this->dbName . PHP_EOL;
        echo '[Dump Path] ' . $this->getDumpFilePath() . PHP_EOL;
        echo '[Cron command] ' . $this->getCronCommand() . PHP_EOL;
        echo '[Cron Log Path] ' . $this->getLogFilePath() . PHP_EOL;
    
        $this->ensureSavePaths();
        
        return parent::run();
    }
    
    protected function ensureSavePaths()
    {
        parent::ensureSavePaths();
        
        $dumpSavePath = $this->getDumpSavePath();
        
        if (!is_dir($dumpSavePath)) {
            if (!mkdir($dumpSavePath, 0775, true)) {
                throw new Exception('Cannot create dump save directory: ' . $dumpSavePath);
            }
        }
    }
    
    /**
     * @return string
     */
    public function getDumpFilename(): string
    {
        switch ($this->dumpFileName) {
            case self::FILE_NAME_WEEKDAY_NUMBER:
                $date = new \DateTime();
                
                $filename = self::DUMPER_MYDUMPER === $this->dumperApp ? $date->format('N') . '/' : $date->format('N') . '.sql';
                break;
            default:
                $filename = self::DUMPER_MYDUMPER === $this->dumperApp ? date('d.m.Y-H-i') . '/' : date('d.m.Y-H-i') . '.sql';
        }
        
        return $filename;
    }
    
    /**
     * @param string|null $path
     */
    public function getDumpSavePath(string $path = null): string
    {
        return $this->getRuntimePath() . '/' . self::BACKUP_DIR_NAME . '/' . $this->dbEngine . '/' . $this->getInterval();
    }

    public function getDumpFilePath(string $path = null): string
    {
        return $this->getDumpSavePath() . '/' . $this->getDumpFilename();
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
    public function getLogFilePath(): string
    {
        return $this->getLogPath() . '/' . $this->dumperApp . '-' . $this->getInterval() . '.log';
    }
    
    /**
     * @param string $interval
     * @return string
     */
    public function getCronCommand(): ?string
    {
        return $this->getCronTimerDef($this->getInterval()) . ' www-data /usr/bin/php ' . $this->getCronPath('dump') . ' >> ' . $this->getLogFilePath() . ' 2>&1';
    }
}