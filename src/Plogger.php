<?php
/**
封装 Simple-PHP-Logger，实现基于数据库的logger
*/
namespace Plog;

use PDO;
use Plog\TLogger;

class Plogger extends Logger
{
    /**
     * $log_file - 覆写此成员，存放日志的数据表名
     * @var string
     */
    protected static $log_file;

    /**
     * $file - 覆写此成员，存放PDO连接实例
     * @var object
     */
    protected static $file;

    /**
     * $options - settable options
     * @var array $dateFormat of the format used for the log.txt file; $logFormat used for the time of a single log event
     */
    protected static $options = [
        'dateFormat' => 'Y-m-d',
        'logFormat' => 'Y-m-d H:i:s'
    ];

    private static $instance;

    /**
      $driver_name - PDO所用的数据驱动名,如:sqlite,mysql
      @var string
    */
    protected static $driver_name;

    /**
      传递一个可写的PDO连接进来
      @param object $dblink 已实例化过的PDO连接对像
      @return object 已实例化过的PDO连接对像
    */
    static function dblink(PDO $dblink=null){
        if($dblink && ($dblink instanceof PDO) ){
            try{
                // $dblink->getAttribute(PDO::ATTR_SERVER_INFO);
                static::$file = $dblink;
            }catch(PDOException $e){
                die($e->getMessage());
            }
        }
        return static::$file;
    }

    /**
      获得当前PDO连接的数据库类型，注意有些驱动类型无法自动侦测时请一定要传参指定，如sqlite类型
      @param string $name PDO所支持的数据驱动类型名字，如mysql,sqlite等
      @return string 数据驱动类型名字
    */
    static function dbtype(string $name=null):string {
        $dblink = static::dblink();
        if($name){
            static::$driver_name = $name;
        }else{
            if(empty(static::$driver_name)){
                try{
                    static::$driver_name = $dblink->getAttribute(PDO::ATTR_DRIVER_NAME);
                }catch(PDOException $e){
                    die($e->getMessage());
                }
            }
        }
        return static::$driver_name;
    }

    /**
      获得当前PDO连接最后插入的自增ID
      @link https://www.php.net/manual/en/pdo.lastinsertid.php [php.net官方参考]
      @param string $name 当数据表的主键为序列类型时需指定序列名
      @return string 自增ID值
    */
    static function lastInsertId(string $name=null):string {
        $dblink = static::dblink();
        return $dblink->lastInsertId($name);
    }

    /**
      查询指定编号的单条日志数据
      @param string $id 日志记录编号
      @return array 单条日志祥细内容
    */
    static function dbfind(string $id):array {
        $result = [];
        $dblink = static::dblink();
        $tablename = static::dbtable();
        $stmt = sprintf("SELECT * FROM %s WHERE id='%s'", $tablename, $id);
        $query = $dblink->query($stmt);
        if($query){
            $result = $query->fetch();
        }
        return $result;
    }

    /**
      传递一个写日志用的表名
      @param string $dbtable 日志记录数据表名，如 _plog为当前连接主库下的表，other._plog为当前连接other库下的_plog表
      @param bool $create 若数据表不存的情况下是否自动生成日志记录表，建议在类实例化后仅调用一次
      @return string 数据表名
    */
    static function dbtable(string $dbtable=null, bool $create=false){
        $sql = 'CREATE TABLE IF NOT EXISTS %s(%s,
            time VARCHAR(32)	NOT NULL,
            path VARCHAR(255) NOT NULL,
            line INTEGER		NOT NULL,
            severity CHAR(8)	NOT NULL,
            message VARCHAR(255) NOT NULL,
            context text NULL
            );';
        $idefsql = [
            'mysql'=>'id INTEGER PRIMARY KEY AUTO_INCREMENT',
            'sqlite'=>'id INTEGER PRIMARY KEY AUTOINCREMENT',
            'pgsql'=>'id SERIAL PRIMARY KEY',
            'sqlsrv'=>'id INT PRIMARY KEY IDENTITY',
        ];
        $idstmt = '';
        $lnk = static::dblink();
        if($lnk && $dbtable && (is_string($dbtable))){
            if($create){
                $driver = null;
                $driver_name = static::dbtype();
                if(false !==stripos('mysql', $driver_name)){	$driver = 'mysql'; $idstmt=$idefsql[$driver];
                }
                if(false !==stripos('sqlite', $driver_name)){	$driver = 'sqlite'; $idstmt=$idefsql[$driver];
                }
                if(false !==stripos('pgsql',$driver_name)){     $driver = 'pgsql'; $idstmt=$idefsql[$driver];
                }
                if(false !==stripos('sqlsrv', $driver_name)){	$driver = 'sqlsrv'; $idstmt=$idefsql[$driver];
                }

                $stmt = sprintf($sql, $dbtable, $idstmt);
                try{
                    $lnk->exec($stmt);
                }catch(PDOException $e){
                    die($e->getMessage());
                }
            }
            static::$log_file = $dbtable;
        }

        return static::$log_file;
    }

    /**
      覆写此方法，生成日志文件，重用PDO连接
      @see dblink()
      @return object 已实例化过的PDO连接对像
    */
    static function createLogFile(){
        if(! static::$file){
            static::$file = static::dblink();
        }
    }

    /**
     * 覆写此方法，写日志记录到数据表
     * @param array $args Array of message (for log file),
     * line (of log method execution), severity (for log file) and displayMessage (to display on frontend for the used)
     * @return bool, true:success, false:failure
     */
    // public function writeLog($message, $line = null, $displayMessage = null, $severity)
    static function writeLog($args = []){
        //Create the log file
        static::createLogFile();

        // // grab the url path
        // $path = $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];

        //Grab time - based on the time format
        $time = date(static::$options['logFormat']);

        // Convert context to json
        $context = json_encode($args['context'], JSON_HEX_APOS|JSON_UNESCAPED_UNICODE);
        // $message = json_encode($args['message'], JSON_HEX_APOS|JSON_UNESCAPED_UNICODE);
        $message = str_replace("'", '', $args['message']);

        $caller = array_shift($args['bt']);
        $btLine = $caller['line'];
        $btPath = $caller['file'];

        // Convert absolute path to relative path (using UNIX directory seperators)
        $path = static::absToRelPath($btPath);

        // Create log variable = value pairs
        $timeLog = is_null($time) ? "[N/A] " : "[{$time}] ";
        $pathLog = is_null($path) ? "[N/A] " : "[{$path}] ";
        $lineLog = is_null($btLine) ? "[N/A] " : "[{$btLine}] ";
        $severityLog = is_null($args['severity']) ? "[N/A]" : "[{$args['severity']}]";
        $messageLog = is_null($args['message']) ? "N/A" : "{$message}";
        $contextLog = empty($args['context']) ? "" : "{$context}";

        // Write time, url, & message to end of file
        // fwrite(static::$file, "{$timeLog}{$pathLog}{$lineLog}: {$severityLog} - {$messageLog} {$contextLog}" . PHP_EOL);
        // echo("{$timeLog}{$pathLog}{$lineLog}: {$severityLog} - {$messageLog} {$contextLog}" . PHP_EOL);
        $timeLog = $time;
        $pathLog = $path;
        $lineLog = $btLine;
        $severityLog = $args['severity'];
        $messageLog = substr($messageLog, 0, 255);

        // die("{$timeLog}{$pathLog}{$lineLog}: {$severityLog} - {$messageLog} {$contextLog}" . PHP_EOL);

        $table = static::dbtable(); // $table='aa';
        $stmt = 'INSERT INTO '.$table.'(`time`,`path`,`line`,`severity`,`message`,`context` ) VALUE(?,?,?,?,?,?);';
        // die($stmt);
        $ok = false;
        $lnk = static::dblink();
        $sth = $lnk->prepare($stmt);
        if($sth){
            $sth->bindParam(1, $timeLog );
            $sth->bindParam(2, $pathLog );
            $sth->bindParam(3, $lineLog, PDO::PARAM_INT);
            $sth->bindParam(4, $severityLog, empty($severityLog)?PDO::PARAM_NULL:PDO::PARAM_STR);
            $sth->bindParam(5, $messageLog, empty($messageLog)?PDO::PARAM_NULL:PDO::PARAM_STR);
            $sth->bindParam(6, $contextLog, empty($contextLog)?PDO::PARAM_NULL:PDO::PARAM_STR);
            $ok = $sth->execute();
            if(!$ok){
                print_r($sth->errorInfo());
                exit;
            }
        }else{
            $stmt = sprintf("INSERT INTO `%s`(`time`,`path`,`line`,`severity`,`message`,`context` ) VALUES('%s','%s','%s','%s','%s','%s');",
                            $table, $timeLog, $pathLog, $lineLog, $severityLog, $messageLog,$contextLog
            );
            // echo $stmt;
            $success = $lnk->exec($stmt);
            $ok = ($success===false)?false:true;
            // var_dump($ok);exit;
        }
        return $ok;
    }

    /**
      覆写此方法，在写完日志后检查连接尝试断开连接，PDO连接是自动回收无需手动关闭
    */
    static function closeFile(){
        if(static::$file instanceof PDO ){
            // static::$file = null;
        }
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    function __construct()
    { }

    function __destruct()
    { }

}
