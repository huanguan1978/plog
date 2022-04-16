<?php
/**
封装 Simple-PHP-Logger，实现基于指定目录的日志记录
*/

namespace Plog;

use Plog\Logger;

class Tlogger extends Logger
{

    /**
     $log_path - 指定存放日期的目录
     @var string
    */
    protected static $log_path;

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
      指定一个写日志用的路径和文件名并返回当前存放日志的目录
     @param string $log_path - 指定存放日志的目录
     @retrun string 返回当前存放日志的目录
    */
    static function logpath(string $logpath=null):string {

        if(empty($logpath)){
            $logpath = __DIR__.DIRECTORY_SEPARATOR.'logs';
        }

        if (!file_exists($logpath)) {
            mkdir($logpath, 0777, true);
        }

        static::$log_path = $logpath;
        return static::$log_path;
    }



    /**
     * 覆写此方法，指定的目录下产生日志文件
     * @param string $log_file - path and filename of log
     * @param array $params - settable options
     * return string 当前所用的日志文件
     */
    public static function createLogFile()
    {
        if(empty(static::$log_path)){
            static::$log_path = static::logpath();
        }

        $time = date(static::$options['dateFormat']);
        static::$log_file =  static::$log_path.DIRECTORY_SEPARATOR. "log-{$time}.txt";

        //Create log file if it doesn't exist.
        if (!file_exists(static::$log_file)) {
            fopen(static::$log_file, 'w') or exit("Can't create {static::log_file}!");
        }
        //  die(static::$log_file);
        //Check permissions of file.
        if (!is_writable(static::$log_file)) {
            //throw exception if not writable
            throw new Exception("ERROR: Unable to write to file!", 1);
        }
        return static::$log_file;
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    function __destruct()
    { }

}
