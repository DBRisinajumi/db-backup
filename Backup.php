<?php

namespace DBRisinajumi\DbBackup;

use Exception;

/**
 * Class Backup
 * @package DBRisinajumi\DbBackup
 */
class Backup
{
    protected $cronPath;
    protected $logPath;
    protected $dbEngine = self::DB_ENGINE_MYSQL;
    
    public const DB_ENGINE_MYSQL = 'mysql';
    
    /**
     * Backup constructor.
     * @param array $vars
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
    {}
    
    /**
     * @throws \Exception
     */
    public function setAuthFromEnv(?string $envPath = null, array $vars): void
    {
        if (!$envPath) { 
            $envPath = dirname(__DIR__, 2) . '/app_env/.env';
        }
    
        if (!file_exists($envPath) || !is_readable($envPath)) {
            throw new Exception('Env file not exists or not readable: ' . $envPath);
        }
        
        \Dotenv::load($envPath);

        foreach ($vars as $property => $const) {
            if (is_array($const) && isset($const['required'])) {
                \Dotenv::required($const);
                $this->{$property} = $const;
            }
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
        echo $command;
        return shell_exec($command);
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
        return $this->logPath ??  dirname(__FILE__, 1) . '/runtime/log';
    }
    
    /**
     * @return string
     */
    public function getCronPath(): string
    {
        return $this->cronPath ?? __FILE__;
    }   
 }