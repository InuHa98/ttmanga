<?php

class Controller {

	protected $code = 0;
	protected $error = false;
	protected $success = false;
	protected static $data = [];

	public static $current = null;

	private const SEPARATOR_CONTROLLER = '@';

	public static function load($controllerName = null, $data = null)
	{
		$separatorName = static::separatorName($controllerName);

		if(!is_array($data))
		{
			$data = [$data];
		}
		else
		{
		    ///$data = array_values($data);
		}

		$class = $separatorName['name'];


		$method = $separatorName['method'];

		$controller_path = rtrim(CONTROLLER_PATH, '/').'/';
		$path_class = $controller_path.$class.'.php';
		$folder = $controller_path.$class;

		if(is_file($path_class))
		{
			self::load_trait($folder);

			include_once $path_class;

			if(!class_exists($class))
			{
				throw new Exception('Controller not found: "'. $class.'"');
			}

			if($method)
			{
				if(!method_exists($class, $method))
				{
					throw new Exception('Method Controller not found: "'. $method.'"');
					return false;
				}
				$instance = new $class;
				call_user_func_array([$instance, $method], $data);
				return true;
			}

			$reflect  = new ReflectionClass($class);
			if($data)
			{
				return $reflect->newInstanceArgs($data);
			}
			

			return $reflect;
		}
		else
		{
			throw new Exception('File controller not found: "'. $class .'"');
		}
		return null;
	}

	private static function load_trait($path) {
		if(!file_exists($path)) {
			return;
		}

		if(is_file($path)) {
			$name_trait = str_replace('.php', '', basename($path));
			if(!trait_exists($name_trait)) {
				include $path;
			}
		}


		$files = glob($path.'/*');
		if(!$files) {
			return;
		}

		foreach($files as $file) {
			if(is_file($file)) {
				$name_trait = str_replace('.php', '', basename($file));
				if(!trait_exists($name_trait)) {
					include $file;
				}
			} else {
				self::load_trait($file);
			}
		}
	}

	private static function separatorName($controllerName = null)
	{
		$controllerName = trim($controllerName.'');

		$result = [
			'name' => null,
			'method' => null
		];

		if(!$controllerName)
		{
			return $result;
		}
		$separator = preg_quote(static::SEPARATOR_CONTROLLER, '/');
		preg_match("/^(\w+)(?:{$separator}?(\w+))?$/si", $controllerName, $split);

		$result['name'] = isset($split[1]) && $split[1] ? trim($split[1]) : null;
		$result['method'] = isset($split[2]) && $split[2] ? trim($split[2]) : null;

		return $result;
	}

	public function addData($data = null)
	{
		if($data == "")
		{
			return false;
		}

		static::$data = array_merge(static::$data, is_array($data) ? $data : [$data]);
		return true;
	}

	public function getData($key = null)
	{
		if($key == "")
		{
			return static::$data;
		}
		return isset(static::$data[$key]) ? static::$data[$key] : null;
	}
}

?>