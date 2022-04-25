# plog

PSR3 logging library that is PDO extendable and simple to use

## TextLog Basic Usage

``` php
use Plog\Tlogger;
$tlog = Tlogger::getInstance();
$tlog->info('HelloWorld');
$tlog->debug("I'mHere",['data'=>['123','456']]);

```

## TextLog Output

``` plaintext
[2022-04-16 16:43:08] [test2.localhost.localdomain/xz/plog/demo.php] [12] : [INFO] - HelloWorld 
[2022-04-16 16:43:08] [test2.localhost.localdomain/xz/plog/demo.php] [13] : [DEBUG] - I'mHere {"data":["123","456"]}

```

## PdoLog Basic Usage

``` php
use Plog\Plogger;
$dbtype = 'sqlite';
$dbname = 'plog.db';
$dbtable = 'plog';
$dsn = sprintf("%s:%s", $dbtype, $dbname);
$plnk = new PDO($dsn);
$plog = Plogger::getInstance();
$plog->dblink($plnk);
$plog->dbtype($dbtype);
$plog->dbtable($dbtable, true);
$plog->info('HelloWorld');
$plog->debug("I'mHere",['data'=>['123','456',"I'm"]]);

```

## PdoLog Output

``` sql
SELECT * FROM plog;

```

| **id** | **time** | **path** | **line** | **severity** | **message** | **context** |
| --- | --- | --- | --- | --- | --- | --- |
| 1 | 2022-04-16 16:29:30 | test2.localhost.localdomain/xz/plog/demo.php | 25 | INFO | HelloWorld |  |
| 2 | 2022-04-16 16:29:30 | test2.localhost.localdomain/xz/plog/demo.php | 26 | DEBUG | ImHere | {"data":\["123","456","I\\u0027m"\]} |

## Installation

Install the latest version with:

``` shell
$ composer require orz/plog

```

## Usage without composer

[Download ZIP](https://github.com/huanguan1978/plog/archive/refs/heads/main.zip) See demo2.php.
