<?php

// require 'vendor/autoload.php';

const _PLOGLIB = [
    'Plog\Logger'=>  '..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Logger.php',
    'Plog\Tlogger'=> '..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'TLogger.php',
    'Plog\Plogger'=> '..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'PLogger.php',
];

function _autoload_($class_name) {
    $class_filename = "$class_name.php";
    if(array_key_exists($class_name, _PLOGLIB)){
        $class_filename = _PLOGLIB[$class_name];
    }
    if(is_file($class_filename)){
        require $class_filename;
    }
}

if(false === spl_autoload_functions()){
        if(function_exists('__autoload')){
            spl_autoload_register('__autoload', false);
        }
        if(function_exists('_autoload_')){
            spl_autoload_register('_autoload_', false);
        }
};

// ------------------------------------------------------------

use Plog\Plogger;
use Plog\Tlogger;

$tlog = Tlogger::getInstance();
//$tlog->setOptions(['logFormat' => 'Y-m-d H:i:s']);
$tlog->logpath(getcwd());
$tlog->info('HelloWorld');
$tlog->debug("I'mHere",['data'=>['123','456']]);

// -------------------------
$dbtype = 'sqlite';
$dbname = 'plog.db';
$dbtable = 'plog';
$dsn = sprintf("%s:%s", $dbtype, $dbname);
$plnk = new PDO($dsn);

$plog = Plogger::getInstance();
//$plog->setOptions(['logFormat' => 'Y-m-d H:i:s']);
$plog->dblink($plnk);
$plog->dbtype($dbtype);
$plog->dbtable($dbtable, true);
$plog->info('HelloWorld');
$plog->debug("I'mHere",['data'=>['123','456',"I'm"]]);
