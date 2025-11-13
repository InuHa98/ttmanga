<?php

class Alert
{
	public const SESSION_ALERT_NAME = '_system_alert';
	private static function check_session()
	{
		if (session_status() === PHP_SESSION_NONE) 
		{
			session_start();
		}

		if(!isset($_SESSION[self::SESSION_ALERT_NAME]))
		{
			$_SESSION[self::SESSION_ALERT_NAME] = [];
		}

		if(!is_array($_SESSION[self::SESSION_ALERT_NAME]))
		{
			$_SESSION[self::SESSION_ALERT_NAME] = [$_SESSION[self::SESSION_ALERT_NAME]];
		}
	}

	public static function push($data)
	{
		self::check_session();
		$_SESSION[self::SESSION_ALERT_NAME][] = $data;
	}

	public static function show()
	{
		self::check_session();
		$alert = $_SESSION[self::SESSION_ALERT_NAME];
		return $alert;
	}

	public static function clear()
	{
		if(isset($_SESSION[self::SESSION_ALERT_NAME]))
		{
			unset($_SESSION[self::SESSION_ALERT_NAME]);
		}
	}
}

?>