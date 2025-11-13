<?php

namespace InuHa;

class Exception {

	private $show_notice = true;
	private $show_warning = true;

	private $app_debug;
	private $error_log;
	private $path = null;

	function __construct($path = null)
	{
		$this->app_debug = env('APP_DEBUG', true);
		$this->error_log = env('ERROR_LOG', true);
		
		$this->path = $path;

		$this->show_error();
	}


	private function show_error()
	{
		@ini_set('log_errors', 0);

		if($this->app_debug != true)
		{
		    error_reporting(0);
		    @ini_set('display_errors', 0);
		}
		else
		{
		    error_reporting(E_ALL & ~E_NOTICE);
		    @ini_set('display_errors', 1);
		    set_error_handler([$this, 'userErrorHandler']);
		}
	}

	private function save_error_log($error_log = null)
	{
		if($this->error_log != true || !$this->path || !$error_log)
		{
			return;
		}

		if(!file_exists($this->path))
		{
			mkdir($this->path, 0755);
		}

		$name_log = 'log_'.date('d-m-Y').'.txt';
		file_put_contents($this->path.'/'.$name_log, $error_log, FILE_APPEND);
	}

	public function userErrorHandler($errno, $errstr, $errfile = '', $errline = 0, $errcontext = [])
	{
		$show_message = true;
		$save_log = true; 
	    switch ($errno) {
	        case E_PARSE:
	        case E_ERROR:
	        case E_CORE_ERROR:
	        case E_COMPILE_ERROR:
	        case E_USER_ERROR:
	            $error = 'Fatal Error';
	            break;
	        case E_WARNING:
	        case E_USER_WARNING:
	        case E_COMPILE_WARNING:
	        case E_RECOVERABLE_ERROR:
	            $error = 'Warning';
	            $save_log = false;
	            if($this->show_warning != true)
	            {
	            	$show_message = false;
	            }
	            break;
	        case E_NOTICE:
	        case E_USER_NOTICE:
	            $error = 'Notice';
	            $save_log = false;
	            if($this->show_notice != true)
	            {
	            	$show_message = false;
	            }
	            break;
	        case E_STRICT:
	            $error = 'Strict';
	            break;
	        case E_DEPRECATED:
	        case E_USER_DEPRECATED:
	            $error = 'Deprecated';
	            break;
	        default:
	        	$error = 'Fatal Exception';
	            break;
	    }

	    $error_string = '<b>'.$error.'</b>: ' . $errstr . ' in <b>' . $errfile . '</b> on line <b>' . $errline . '</b><br /><br />';

	    if($save_log)
	    {
		    $error_log = "[" . date("d-m-Y H:i:s", $_SERVER['REQUEST_TIME']) . '] PHP '.strip_tags($error_string)."\r\n";
		    $this->save_error_log($error_log);
	    }


	    if (!(error_reporting() & $errno))
	    {
	        return;
	    }

	    restore_error_handler();
	    if (function_exists('restore_exception_handler'))
	    {
	        restore_exception_handler();
	    }

	    if(!$show_message)
	    {
	    	return;
	    }

	    echo $error_string;
	}
}

?>