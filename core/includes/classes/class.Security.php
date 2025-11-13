<?php

class Security
{
	public const TOKEN_LABEL = '_csrf-token';

	private static $token = null;

	private static function check_session()
	{
		if (session_status() === PHP_SESSION_NONE) 
		{
			session_start();
		}

		if(!isset($_SESSION[self::TOKEN_LABEL]))
		{
			$_SESSION[self::TOKEN_LABEL] = [];
		}

		if(!is_array($_SESSION[self::TOKEN_LABEL]))
		{
			$_SESSION[self::TOKEN_LABEL] = [$_SESSION[self::TOKEN_LABEL]];
		}
	}

	private static function push($token = null)
	{
		self::check_session();
		if($token != "")
		{
			$_SESSION[self::TOKEN_LABEL][] = $token;
		}
	}

	private static function remove($token = null)
	{
		if($token != "")
		{
			$_SESSION[self::TOKEN_LABEL] = array_diff($_SESSION[self::TOKEN_LABEL], [$token]);
		}
	}

	public static function clear()
	{
		if(isset($_SESSION[self::TOKEN_LABEL]))
		{
			$_SESSION[self::TOKEN_LABEL] = [];
		}
	}

	public static function validate($token = null)
	{
		self::check_session();

		$token = !$token ? filter_input(INPUT_POST, self::TOKEN_LABEL, FILTER_UNSAFE_RAW) : $token;
		$token = $token !== null ? trim(strip_tags($token)) : '';
		$isValidate = ($token && in_array($token, $_SESSION[self::TOKEN_LABEL]));
		self::remove($token);
		if ($isValidate)
		{
			return true;
		}
		
		return false;
	}

	public static function getCSRFToken()
	{
		if(self::$token != "")
		{
			return self::$token;
		}
		self::$token = bin2hex(openssl_random_pseudo_bytes(32));
		self::push(self::$token);	
        return self::$token;
	}

    public static function insertHiddenToken()
    {
        return '<input type="hidden" name="'.self::TOKEN_LABEL.'" value="'.self::getCSRFToken().'" />';
    }
}

?>