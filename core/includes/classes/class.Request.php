<?php

class Request {

	public static $path;
	public static $method;
	public static $protocol;
	public static $authorization;

	private static $current_url = null;

	function __construct()
	{
		$this->bootstrap();
	}


	private function bootstrap()
	{
		self::$path = self::path();
		self::$method = self::method();
		self::$protocol = self::protocol();
	}

	public static function path()
	{
		if(is_null(self::$path))
		{
			self::$path = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;
		}
		return self::$path;
	}

	public static function method()
	{
		if(is_null(self::$method))
		{
			self::$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
		}
		return self::$method;
	}

	public static function protocol()
	{
		if(is_null(self::$protocol))
		{
			self::$protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : null;
		}
		return self::$protocol;
	}

	public static function authorization()
	{
		if(is_null(self::$authorization))
		{
			$user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null;
			$pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : null;

			self::$authorization = compact('user', 'pass');
		}
		return self::$authorization;
	}

	public static function request($key = null, $default = '')
	{
		if($key == '')
		{
			return $_REQUEST;
		}

		return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
	}

	public static function get($key = null, $default = '')
	{
		if($key == '')
		{
			return $_GET;
		}

		return isset($_GET[$key]) ? $_GET[$key] : $default;
	}

	public static function post($key = null, $default = '')
	{
		if($key == '')
		{
			return $_POST;
		}

		return isset($_POST[$key]) ? $_POST[$key] : $default;
	}

	public static function cookie($key = null, $default = '')
	{
		if($key == '')
		{
			return $_COOKIE;
		}

		return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
	}

	public static function session($key = null, $default = '')
	{
		if($key == '')
		{
			return $_SESSION;
		}

		return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
	}

	public static function server($key = null, $default = '')
	{
		if($key == '')
		{
			return $_SERVER;
		}

		return isset($_SERVER[$key]) ? $_SERVER[$key] : $default;
	}

	public static function request_header($key = null, $default = '')
	{
		$key = 'HTTP_'.strtoupper(str_replace('-', '_', $key));
		return isset($_SERVER[$key]) ? $_SERVER[$key] : $default;
	}

	public static function referer($url = null) {
		return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ($url ? $url : APP_URL);
	}

	public static function build_query($params = [])
	{

		$uri = isset($_SERVER['REQUEST_URI']) ? trim($_SERVER['REQUEST_URI']) : null;

		if(!$params || !is_array($params))
		{
			return $uri;
		}

		if(self::$current_url)
		{
			return self::$current_url['url'].'?'.http_build_query(array_merge(self::$current_url['query'], $params));
		}
		else
		{
			$regex = "#^(.*?)\?(.*?)$#si";
			$url = preg_replace($regex, "$1", $uri);
			
			$query_array = $params;
			self::$current_url = [
				'url' => $url,
				'query' => $query_array
			];
			if(preg_match($regex, $uri))
			{
				$query_string = preg_replace($regex, "$2", $uri);
				parse_str($query_string, $query_array);
				self::$current_url['query'] = $query_array;
				$query_array = array_merge($query_array, $params);
			}
			return $url.'?'.http_build_query($query_array);
		}
	}
}

?>