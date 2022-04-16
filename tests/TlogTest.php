<?php

namespace Plog\Tests;

use PHPUnit\Framework\TestCase;
use Plog\Tlogger;

class TlogTest extends TestCase {
    protected $log;
    protected $tempfile;

    function setUp():void {
        // $this->log = new Plogger();
        $this->log = Tlogger::getInstance();
        $this->log->setOptions(['logFormat' => 'Y-m-d H:i:s']);
    }

    function tearDown():void {
        if(!empty($this->tempfile) && file_exists($this->tempfile) ){
            @unlink($this->tempfile);
        }
    }

    // return file last line
    function tailfile(string $filename):string {
        $line = '';
        $section = file_get_contents($filename, true);
        if($section){
            $lines = explode(PHP_EOL, $section);
            $no = count($lines)-2; // last line is PHP_EOL
            $line = $lines[$no];
        }
        return $line;
    }

    function testPathfile():string {
        $logpath = sys_get_temp_dir();
        $inst_logpath = $this->log->logpath($logpath);
        $this->assertSame($inst_logpath, $logpath);
        $logfile = $this->log->createLogFile();
        return $logfile;
    }

     /**
     * @depends testPathfile
     */
    function testLogInfo(string $logfile):void {
        $level = 'INFO';
        $message = 'helloworld';
        $content = 'HelloWorld';
        $data = ['level'=>$level, 'message'=>$message, 'content'=>$content];
        // $json = json_encode($data);
        $ok = $this->log->info($message, $data);
        $this->assertTrue($ok);
        if($ok){
            // echo $logfile;
            $lastline = $this->tailfile($logfile);
            $this->assertNotEmpty($lastline);
            if($lastline){
                $level = '['.$level.']';
                $this->assertStringContainsString($level, $lastline);
                $this->assertStringContainsString($message, $lastline);
                $this->assertStringContainsString($content, $lastline);
            }
        }
    }

    //cls.end
}
