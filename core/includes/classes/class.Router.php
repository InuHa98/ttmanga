<?php


class Router {

	private static $uri;
	private static $method;
	private static $protocol;

	private static $prefix = null;
	private static $data = [];

	private static $route_data = [];
	private static $route_regex = null;
	public static $current_route = null;

	private const NO_ROUTE = "/";
	private const SUPPORTED = [
		'GET',
		'POST',
		'PUT',
		'PATCH',
		'DELETE',
		'HEAD',
		'OPTIONS'
	];

	public const NAMESPACE_API = '/api';

	function __construct()
	{
		$this->bootstrap();
	}

	static function __callStatic($name, $args)
	{

		$name = strtoupper($name);
		$function_name = $name;
		
		switch($name) {
			case 'MATCH':
				if(count($args) < 3)
				{
					return static::class;
				}

				$method   = isset($args[0]) ? $args[0] : null;
				$route    = isset($args[1]) ? $args[1] : null;
				$callback = isset($args[2]) ? $args[2] : null;
				$current_route = isset($args[3]) ? $args[3] : null;

				if(!is_array($method))
				{
					$method = [$method];
				}
				$method = array_map(function($arr){
					return strtoupper($arr);
				}, $method);

				$name = $method;

				break;

			case 'VIEW':
				$name     = ['GET', 'HEAD'];
				$route    = isset($args[0]) ? $args[0] : null;
				$viewName = isset($args[1]) ? $args[1] : null;
				$data     = isset($args[2]) ? $args[2] : null;
				$current_route = isset($args[3]) ? $args[3] : null;

				if(!$viewName)
				{
					return static::class;
				}

				$callback = function() use ($viewName, $data){
					View::render($viewName, $data);
				};
				break;

			case 'GROUP':
				$prefix   = isset($args[0]) ? $args[0] : null;
				$callback = isset($args[1]) ? $args[1] : null;
				$current_route = isset($args[2]) ? $args[2] : null;
				
				if(!($callback instanceof Closure))
				{
					return static::class;
				}


				if (self::$prefix) {
					$prefix = self::$prefix.ltrim($prefix, '/');
				}
				self::$prefix = self::formatRoute($prefix);

		  		$prefix_regex = self::makeRegexRoute(self::$prefix);
				if(!preg_match("#^{$prefix_regex}#si", self::$uri))
				{
					self::$prefix = null;
					return static::class;
				}

		  		preg_match("#^{$prefix_regex}$#si", self::$uri, $route_args);

				if($current_route != "")
				{
					self::$current_route = $current_route;
				}

		  		$args = self::fill_args($callback, $route_args, null);


		  		call_user_func_array($callback, $args);

		  		self::$prefix = null;
				return static::class;

			case 'ALL':
				$name = self::SUPPORTED;
			default:

				$route    = isset($args[0]) ? $args[0] : null;
				$callback = isset($args[1]) ? $args[1] : null;
				$current_route = isset($args[2]) ? $args[2] : null;

				if(!is_array($name))
				{
					$name = [$name];
				}
				break;
		}

		if(is_array($route)) {
			foreach($route as $rt) {
				$new_arg = $args;
				$new_arg[0] = $rt;
				call_user_func_array([static::class, $function_name], $new_arg);
			}
			return;
		}

		if(self::$prefix)
		{
			$route = rtrim($route, '/');
		}

		$route = self::$prefix.self::formatRoute($route);

		$route_regex = self::makeRegexRoute($route);

		if(!preg_match("#^".$route_regex."$#si", self::$uri))
		{
			return static::class;
		}

		self::$route_regex = $route_regex;
		
		$methods = array_filter($name, function($item){
			return in_array($item, self::SUPPORTED);
		});

		if(!$callback || !($callback instanceof Closure) || !$name)
		{
			return static::class;
		}


		self::$route_data[] = [
			'method' => $methods,
			'callback' => $callback
		];

		if($current_route != "" && !self::$current_route)
		{
			self::$current_route = $current_route;
		}


		return static::class;
	}

    function __destruct()
    {
        return $this->resolve();
    }

    private function bootstrap()
    {

		self::$method   = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
		self::$protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : null;

    	$path_router = isset($_SERVER['PHP_SELF']) ? dirname($_SERVER['PHP_SELF']) : null;
		self::$uri   = isset($_SERVER['REQUEST_URI']) ? preg_replace("/^(.*?)\?(.*?)$/si", "$1", $_SERVER['REQUEST_URI']) : null;
    	self::$uri   = preg_replace("#^".self::escape_string_regex($path_router)."/(.*)$#si", "$1", self::$uri);
    	self::$uri   = self::formatRoute(self::$uri);
    }

	private static function escape_string_regex($string = null)
	{
		return preg_replace_callback("/(\.|\\|\+|\*|\?|\[|\\^|\]|\\$|\(|\)|\{|\}|\=|\!|\<|\>|\:|\-|\#|\/)/si", function($char) {
			return '\\'.$char[1];
		}, $string);
	}

    private static function makeRegexRoute($route = null)
    {
		$i = 0;
		$regex = preg_replace_callback("/\{(\w+)(\(.*\))?(\??)\}/U", function($var) use (&$i) {
			$i++;
			$format = isset($var[2]) && $var[2] ? $var[2] : '[^/]+';
			$force  = isset($var[3]) && $var[3] ? '?' : '';
			return ($i != 1 ? $force : null)."(?<{$var[1]}>".$format.")".$force;
		}, $route);

		return str_replace('//', '/', preg_replace("/\/$/U", "/?", $regex));
    }

    private static function formatRoute($route = null)
    {
    	$route = str_replace('//', '/', $route);
        $result = trim($route, '/');
        $result = '/'.$result;
		$result = str_replace('//', '/', $result);
		$result = rtrim($result, '/');
		return $result.'/';
    }

    private static function fill_args($callback = null, $route_args = [], $default = null)
    {
    	if(is_null($callback))
    	{
    		return [];
    	}

		$info = new ReflectionFunction($callback);
        $args = array_fill(0, $info->getNumberOfParameters(), $default);
        if(count($args) > 0)
        {
        	$i = 0;
	        foreach ($info->getParameters() as $param)
	        {
	        	$class = trim($param->getType().'');
	        	if($class)
	        	{
	        		$args[$i] = class_exists($class) ? new $class() : null;
	        	}
	        	else
	        	{
		        	$args[$i] = isset($route_args[$param->name]) ? $route_args[$param->name] : ($param->isDefaultValueAvailable() ? $param->getDefaultValue() : $default);
	        	}
	        	$i++;
	        }			        	
        }
        return $args;
    }

    private function resolve()
    {

        $method = self::$method;
        $route_args = [];

        if(!self::$route_data)
        {
        	return $this->invalidRouteHandler();
        }

		if(class_exists('serverBootHandler'))
		{
			serverBootHandler::bootstrap();
	        if(preg_match("#^".self::NAMESPACE_API."(/(.*))?$#si", self::$uri))
	        {
				if (serverBootHandler::api() === false) {
					return;
				}
	        }
	        else
	        {
				if (serverBootHandler::web() === false) {
					return;
				}
	        }
	    }

  		preg_match("#^".self::$route_regex."$#si", self::$uri, $route_args);

		$isNotFound = true;
		foreach (self::$route_data as $data) {
	        if (!in_array(self::$method, $data['method']) || is_null($data['callback']))
	        {
	            continue;
	        }

			$isNotFound = false;
	        $args = self::fill_args($data['callback'], $route_args, null);

			
	        call_user_func_array($data['callback'], $args);
		}

        if($isNotFound)
        {
        	return $this->invalidRouteHandler();
        }
    	
    }

    public static function redirect($route = null, $toUrl = null, $code = 302)
    {
    	if($route == "" || $toUrl == "")
    	{
    		return false;
    	}

    	$route = trim($route);

    	if($route != '*')
    	{
			$route = self::$prefix.self::formatRoute($route);
			if(self::$prefix)
			{
				$route = rtrim($route, '/');
			}

			$route = self::makeRegexRoute($route);    		
    	}

		if($route == '*' || preg_match("#^{$route}$#si", self::$uri))
		{
			$toUrl = trim($toUrl);
			if(!preg_match("/^https?:\/\/(.*?)$/si", $toUrl))
			{	
				$toUrl = rtrim(App::$url, '/').'/'.ltrim($toUrl, '/');
			}
			ob_start();
			header('Location: '.$toUrl, true, $code);
			exit();			
		}
    }

	public static function reload()
	{
		ob_start();
		header('Location: '.rtrim(APP_URL, '/').'/'.ltrim(self::$uri, '/'), true, 302);
		exit();	
	}


    public static function addData($data = null)
    {
		if($data != "")
		{
			self::$data = array_merge(self::$data, is_array($data) ? $data : [$data]);
		}
		return static::class;
    }

	public static function getData($key = null)
	{
		if($key == "")
		{
			return self::$data;
		}
		return isset(self::$data[$key]) ? self::$data[$key] : null;
	}

    private function invalidRouteHandler()
    {
    	if(self::$uri == self::NO_ROUTE)
    	{
    		return;
    	}

        if(method_exists('ServerErrorHandler', 'error_404'))
        {
        	ServerErrorHandler::error_404();
        }
    }
}


?>