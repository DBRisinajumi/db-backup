<?php

namespace DbBackup;

use Exception;

/**
 * Class Backup
 * @package DbBackup
 */
class Backup
{
    protected $cronPath;
    protected $logPath;
    protected $dbEngine = self::DB_ENGINE_MYSQL;
    protected $debug = false;
    protected $interval = self::INTERVAL_LATEST;
    
    public const INTERVAL_LATEST = 'latest';
    public const INTERVAL_DAILY = 'daily';
    public const INTERVAL_WEEKLY = 'weekly';
    public const INTERVAL_MONTHLY = 'monthly';
    
    public const DB_ENGINE_MYSQL = 'mysql';
    
    /**
     * Backup constructor.
     * @param array $vars
     * @param bool $fromEnv
     */
    public function __construct(array $vars = [])
    {
        $this->initClassVars($vars);
        $this->init();
    }
    
    /**
     *
     */
    public function init()
    {
    }
    
    /**
     * @throws \Exception
     */
    public function setAuthFromEnv(array $vars = [], ?string $envPath = null): void
    {
        if (!$envPath) {
            $envPath = dirname(__DIR__, 3) . '/app_env/';
        }
        
        if (!file_exists($envPath) || !is_readable($envPath)) {
            throw new Exception('Env file not exists or not readable: ' . $envPath);
        }
        
        \Dotenv::load($envPath);
        
        foreach ($vars as $property => $const) {
            $value = getenv($const);
            $this->{$property} = $value;
        }
    }
    
    /**
     * @param array $vars
     */
    public function initClassVars(array $vars): void
    {
        foreach ($vars as $property => $const) {
            $this->{$property} = $const;
        }
    }
    
    /**
     * @return false|string|null
     */
    public function run()
    {
        $command = $this->getExecCommand();
        echo '[EXEC] ' . $command . PHP_EOL;
        return shell_exec($command) . PHP_EOL;
    }
    
    /**
     * @return string
     */
    public function getExecCommand(): string
    {
        throw new Exception(__FUNCTION__ . 'should be called from extended class');
    }
    
    /**
     * @return string
     */
    public function getLogPath(): string
    {
        return $this->logPath ?? dirname(__FILE__, 1) . '/runtime/log';
    }
    
    /**
     * @return string
     */
    public function getCronPath(): string
    {
        return $this->cronPath;
    }
    
    /**
     * @return string|null
     */
    public function getInterval(): ?string
    {
        return $this->interval ?? self::INTERVAL_LATEST;
    }
    
    /**
     * @param string $interval
     * @return string
     */
    public function getCronTimerDef(string $interval): string
    {
        $timeDef = '';
        switch ($interval) {
            case self::INTERVAL_WEEKLY:
                // 04:00 every week at Monday
                $timeDef = '0 4 * * 1';
                break;
            case self::INTERVAL_MONTHLY:
                // 04:00 every month at the first day
                $timeDef = '0 4 1 * *';
                break;
            default:
                // 04:00 Daily
                $timeDef = '0 4 * * *';
                break;
        }
        return $timeDef;
    }
}