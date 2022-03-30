<?php

namespace DbBackup\controllers\yii2;

use alpha\voyage\Module;
use DbBackup\Dumper;
use DbBackup\Sync;
use yii\console\Controller;
use yii\console\ExitCode;

#### WARNING! Requires Yii2 installed! ####

/**
* Class BackupController* @property Module $module
*/
class BackupController extends Controller
{       
    public $interval;
    
    public function options($actionID)
    {
        return ['interval'];
    }
    
    /**
     * @return string[]
     */
    public function optionAliases()
    {
        return ['i' => 'interval'];
    }
    
    /**
     * default action
     * @return int
     * @throws \Exception
     */
    public function actionDump(): int
    {
        $dumper = new Dumper(['interval' => $this->interval]);
        $dumper->setAuthFromEnv();
        echo $dumper->run();
        
        return ExitCode::OK;
    }
    
    /**
     * default action
     * @return int
     * @throws \Exception
     */
    public function actionSync(): int
    {
        $sync = new Sync(['interval' => $this->interval]);
        $sync->setAuthFromEnv();
        echo $sync->run();
        
        return ExitCode::OK;
    }
}
