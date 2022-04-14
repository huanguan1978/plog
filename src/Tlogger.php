<?php
/**
封装 Simple-PHP-Logger，实现基于指定目录的日志记录
*/
// namespace Tlogger;

require 'Logger.php';


class Tlogger extends Logger 
{
    /** 覆写此成员，存放日志的数据表名，
     * $log_file - path and log file name
     * @var string
     */		
	protected static $log_file;	

    /** 覆写此成员，存放PDO连接实例
     * $file - file
     * @var string
     */		
	protected static $file;
	
    private static $instance;	

	
	// 传递一个写日志用的路径和文件名并返回拼接好路径的日志文件名
	static function pathfile(string $logpath=null, string $logfile=null):string {
			if(empty($logpath)){
				$logpath = sys_get_temp_dir();
			}else{
				if (!file_exists($logpath)) {
					mkdir($logpath, 0777, true);
				}				
			}
			
			if(empty($logfile)){
				$time = date(static::$options['dateFormat']);
				$logfile='log-{$time}.txt';
			}
			
			$pathfile = $logpath.DIRECTORY_SEPARATOR.$logfile;			
			if (!file_exists($pathfile)) {
				fopen($pathfile, 'w') or exit("Can't create {$pathfile}!");
			}

			if (!is_writable(static::$log_file)) {
				throw new Exception("ERROR: Unable to write to file!", 1);
			}			
			static::$log_file = $pathfile;
		}
		
		return static::$log_file;
	}	
	
    /** 覆写此方法，产生指定目录的log_file
     * Create the log file
     * @param string $log_file - path and filename of log
     * @param array $params - settable options
     */
    public static function createLogFile()
    {
		if(empty(static::$log_file)){
			static::$log_file = static::pathfile();
			return static::$log_file;
		}
		
        $time = date(static::$options['dateFormat']);
        static::$log_file =  __DIR__ . "/logs/log-{$time}.txt";


        //Check if directory /logs exists
        if (!file_exists(__DIR__ . '/logs')) {
            mkdir(__DIR__ . '/logs', 0777, true);
        }

        //Create log file if it doesn't exist.
        if (!file_exists(static::$log_file)) {
            fopen(static::$log_file, 'w') or exit("Can't create {static::log_file}!");
        }

        //Check permissions of file.
        if (!is_writable(static::$log_file)) {
            //throw exception if not writable
            throw new Exception("ERROR: Unable to write to file!", 1);
        }
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