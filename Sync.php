<?php

namespace DBRisinajumi\DbBackup;

use Exception;

/**
 * Class Sync
 * Upload files to remote server via scp, rsync or rclone
 * Uses scp by default
 * @package DBRisinajumi\DbBackup
 */
class Sync extends Backup
{
    protected $host;
    protected $port;
    protected $user;
    protected $password;
    protected $key;
    protected $remotePath;
    protected $localPath;
    protected $syncEngine = self::SYNC_ENGINE_SCP;
    
    public const SYNC_ENGINE_RSYNC = 'rsync';
    public const SYNC_ENGINE_RCLONE = 'rclone';
    public const SYNC_ENGINE_SCP = 'scp';
    
    /**
     * 
     */
    public function init(): void
    {
        echo 'Running ' . $this->syncEngine . '....' . PHP_EOL;
        echo '[Backup Host] ' . $this->getHost() . PHP_EOL;
        echo '[Local Path] ' . $this->getLocalPath() . PHP_EOL;
        echo '[Remote Path] ' . $this->getRemotePath() . PHP_EOL;
        echo '[Exec] ' . $this->getExecCommand() . PHP_EOL;
        echo '[Cron command] ' . $this->getCronCommand() . PHP_EOL;
        echo '[Cron Log Path] ' . $this->getLogPath() . PHP_EOL;
    }
    
    /**
     * @return string
     */
    public function getClientName(): ?string
    {    
        return $this->syncEngine;
    }

    /**
     * @return string
     */
    public function getExecCommand(): string
    {
        switch($this->syncEngine) {
            case self::SYNC_ENGINE_RSYNC:
                $command = 'rsync -avze';
                //$command = 'rsync -n --update -va --progress --verbose';
        
                if ($this->port) {
                    $command .= ' --port=' . $this->port . ' ';
                }
        
                // e. g. ~/.ssh/targethost_rsa_rsync
                if ($this->key) {
                    $command .= " -e 'ssh -i " . $this->key . "'";
                }
        
                $command .= ' ssh ' . $this->getLocalPath() . ' ' . $this->getUserAndHost() . ':' . $this->getRemotePath();
                break;

            //@FIXME  Rclone cannot correctly work in command line. Requires generated config with obscured password. See: https://forum.rclone.org/t/login-authentication-failed-password-generated-using-rclone-config-create/20766/4
            case self::SYNC_ENGINE_RCLONE:
               $command = 'rclone sync --sftp-host ' . $this->getHost() . ' --sftp-user ' . $this->user . ' --sftp-pass ' . $this->password . ' --no-obscure :sftp:'. $this->getLocalPath() . ' ' . $this->getRemotePath();     
                break;
            default:
                // Requires sshpass to be installed on local server
                $command = "sshpass -p '" . $this->password . "' scp -r -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null " .  $this->getLocalPath() . ' ' . $this->getUserAndHost() . ':' . $this->getRemotePath();
                if ($this->debug) {
                    $command .= ' -vvv';
                }
        }
    
        return $command;
    }
    
    /**
     * @return string
     */
    public function getHost(): string
    {   
        return $this->host ?? 'backup-host';
    }

    /**
     * @return string
     */
    public function getUserAndHost(): string
    {   
        return $this->user ? $this->user . '@' . $this->getHost() : $this->getHost();
    }
    
    /**
     * @return string
     */
    public function getLogPath(): string
    {
        return $this->logPath ??  dirname(__FILE__, 1) . '/runtime/backup/log/' . $this->getClientName() . '-upload-daily.log';
    }
    
    /**
     * @return string
     */
    public function getLocalPath(): string
    {
        return $this->localPath ??  dirname(__FILE__, 1) . '/runtime/backup/' . $this->getBackupDirName();
    }
    
    /**
     * @return mixed|string
     */
    public function getBackupDirName()
    {
        global $argv;
        return $argv[1] ?? 'mysql';
    }
    
    /**
     * @return string
     */
    public function getRemotePath(): string
    {
        // Remote home dir by default
        return $this->remotePath ?? '.';
    }
    
    /**
     * @param string $interval
     * @return string
     */
    public function getCronCommand(): string
    {
        return $this->getCronTimerDef(self::INTERVAL_DAILY) . ' www-data /usr/bin/php ' . $this->getCronPath() . '  > ' . $this->getLogPath();
    }
}