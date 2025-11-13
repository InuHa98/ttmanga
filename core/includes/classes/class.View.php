<?php

class View {
	
	private static $data = [];
	private const FILE_VIEW = '.view.php';

	private static $theme_path = null;

	public static function set_theme($theme_path = null)
	{
		self::$theme_path = trim($theme_path, '/');
	}


	public static function render($viewName = null, $data = null)
	{
		$path = rtrim(VIEW_PATH, '/').'/';
		$view = self::formatViewName($viewName);

		if(!is_file($path.$view))
		{
			$view = self::formatViewName($viewName, true);
			if(!is_file($path.$view))
			{
				die('File View not found: "'.$viewName.'"<p>[ Try <a href="?'.themeController::REQUEST_RESET.'=true">Reset theme</a> ]</p>');
			}
		}

		if(self::$data)
		{
			extract(self::$data);
		}

		if($data)
		{
			if(!is_array($data))
			{
				$data = [$data];
			}
			extract($data);

		}
		include_once $path.$view;
	}

	public static function render_theme($theme_id, $viewName = null, $data = null)
	{
		if($data == '')
		{
			$data = $viewName;
			$viewName = $theme_id;
			$theme_id = self::$theme_path;
		}

		$viewName = trim($theme_id, '/').'/'.$viewName;
		self::render($viewName, $data);
	}

	public static function render_json($data = null)
	{
		echo json_encode($data, JSON_PRETTY_PRINT);
	}

	private static function formatViewName($viewName = null, $replace_dot = false)
	{
		$viewName = trim(($replace_dot != false ? str_replace('.', '/', $viewName) : $viewName), '/');
		return $viewName.self::FILE_VIEW;
	}

	public static function addData($data = null, $value = null)
	{
		if(!is_array($data) && $value != "")
		{
			$data = [$data => $value];
		}

		if($data == "")
		{
			return false;
		}

		self::$data = array_merge(self::$data, is_array($data) ? $data : [$data]);
		return true;
	}

	public static function getData($key = null)
	{
		if($key == "")
		{
			return self::$data;
		}
		return isset(self::$data[$key]) ? self::$data[$key] : null;
	}
}

?>