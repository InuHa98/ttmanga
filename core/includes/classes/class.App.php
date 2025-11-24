<?php


class App {
	public static $data = [];
	public static $database = null;
	public static $version = null;
	public static $url = null;
	public static $auth = null;
	public static $pagination_limit = 10;
	public static $router = null;
	public static $request = null;
	public static $view = null;
	public static $lang = null;

	private static $path_route_web = null;
	private static $path_route_api = null;

	private const FORMAT_MODEL_FILE = '%sModel';
	public const COOKIE_VIEW_MODE = '_viewMode';
	public const COOKIE_READ_MODE = '_readMode';
	public const COOKIE_PADDING_MODE = '_paddingMode';
	public const COOKIE_WARNING = '_warning';

	public const PROFILE_UPLOAD_MODE_LOCALHOST = 'localhost';
	public const PROFILE_UPLOAD_MODE_IMGUR = 'imgur';

	private const FILE_CONFIG = 'config.json';

	function __construct()
	{
		if (extension_loaded('zlib')) {
		    @ini_set('zlib_output_compression','On');
		    @ini_set('zlib.output_compression_level', 3);
		    @ini_set('output_buffering','On');
		    ob_start();
		} else {
		    ob_start();
		}
		$this->load_config();
		$this->load_models();
		self::$request = new Request();
		self::$router = new Router();
		self::$view = new View();
		self::$lang = new Language();
	}

	private function load_config()
	{
		$path = CORE_PATH.'/'.self::FILE_CONFIG;
		if(!is_file($path)) {
			return;
		}
		DotEnv::json($path);
	}

	public static function update_config($data)
	{
		if(!is_array($data)) {
			return false;
		}

		$path = CORE_PATH.'/'.self::FILE_CONFIG;
		$configs = [];
		if(is_file($path)) {
			try {
				$configs = json_decode(file_get_contents($path), true);
				if(!is_array($configs)) {
					$configs = [];
				}
			} catch(Error $e) {}
		}

		foreach($data as $key => $value) {
			$configs[$key] = $value;
		}
		if(file_put_contents($path, json_encode($configs, JSON_PRETTY_PRINT))) {
			return true;
		}
		return false;
	}

	private function load_models()
	{
		spl_autoload_extensions('.php');
		spl_autoload_register(function($className){
			$className = str_replace('\\', '/', $className);
		    $filename = MODEL_PATH.'/'.sprintf(self::FORMAT_MODEL_FILE, $className).'.php';
		    if (is_readable($filename)) {
		        include_once $filename;
		    }
		});
	}

	public function default_pagination_limit($limit = null)
	{
		self::$pagination_limit = $limit;
	}

	public function set_url($url = null)
	{
		self::$url = $url;
	}

	public function set_version($version = null)
	{
		self::$version = $version;
	}

	public function set_language($config = [])
	{
		self::$lang::init($config);
	}

	public function set_auth($options = [])
	{
		self::$auth = new Auth($options);
	}

	public function set_timezone($timezone = null)
	{
		date_default_timezone_set($timezone);
	}

	public function connect_database($options = [])
	{
		try {
		    self::$database = new InuHa\Database($options);
		} catch(PDOException $error) {
		    exit('<b>Error</b>: Can\'t connect to Database: '.$error->getMessage());
		}
	}

	public function set_path_router($router = null, $path = null)
	{
		switch ($router) {
			case 'web':
				self::$path_route_web = $path;
				break;

			case 'api':
				self::$path_route_api = $path;
				break;
		}
	}

	public static function view_mode()
	{
		return Request::cookie(self::COOKIE_VIEW_MODE, null);
	}

	public static function comfirm_warning($id)
	{
		setcookie(self::COOKIE_WARNING.'_'.$id, true, time() + 3600, '/');	
	}

	public static function is_comfirm_warning($id)
	{
		$cookie = Request::cookie(self::COOKIE_WARNING.'_'.$id, null);
		return !empty($cookie);
	}

	public static function read_mode()
	{
		return Request::cookie(self::COOKIE_READ_MODE, null);
	}

	public static function padding_mode()
	{
		return Request::cookie(self::COOKIE_PADDING_MODE, null);
	}

	public function load_router_web($path = null)
	{
		if($path != "")
		{
			$this->set_path_router('web', $path);
		}
		if(is_file(self::$path_route_web))
		{
			include_once self::$path_route_web;
		}
	}

	public function load_router_api($path = null)
	{
		if($path != "")
		{
			$this->set_path_router('api', $path);
		}

		if(is_file(self::$path_route_api))
		{	
			self::$router::group(Router::NAMESPACE_API, function(){
				include_once self::$path_route_api;
			});
		}
	}

	public function addData($data = null)
	{
		if($data == "")
		{
			return false;
		}

		self::$data = array_merge(self::$data, is_array($data) ? $data : [$data]);
		return true;
	}

	public function getData($key = null)
	{
		if($key == "")
		{
			return self::$data;
		}
		return isset(self::$data[$key]) ? self::$data[$key] : null;
	}
}


?>