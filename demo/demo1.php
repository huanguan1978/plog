<?php

require '../vendor/autoload.php';

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
