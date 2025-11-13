<?php


class themeController {

	public const COOKIE_NAME = '_themeID';
	public const REQUEST_CHANGE = 'setThemeID';
	public const REQUEST_RESET = 'resetThemeID';

	public static $current_theme = null;
	private static $themes_path = '/themes/';

	private static $accept_list = [
		'ttmanga' => 'TTmanga'
	];

	public function __construct()
	{
		$request_theme_id = isset($_GET[self::REQUEST_CHANGE]) ? trim($_GET[self::REQUEST_CHANGE]) : null;
		if($request_theme_id)
		{
			if(array_key_exists($request_theme_id, self::$accept_list) && file_exists(VIEW_PATH.self::$themes_path.$request_theme_id))
			{
				self::set($request_theme_id);
			}
		}

		if(isset($_GET[self::REQUEST_RESET]))
		{
			self::reset();
		}

		self::$current_theme = self::get();
		View::set_theme(self::$themes_path.self::$current_theme);
	}

	public static function get()
	{
		$cookie_theme = isset($_COOKIE[self::COOKIE_NAME]) ? $_COOKIE[self::COOKIE_NAME] : env(DotEnv::DEFAULT_THEME);
		return Auth::$isLogin == true ? (Auth::$data['settings']['theme'] ?? env(DotEnv::DEFAULT_THEME)) : $cookie_theme;
	}

	public static function set($theme_id)
	{
		setcookie(self::COOKIE_NAME, $theme_id, time() + 3600 * 24 * 365, '/');
		if(Auth::$isLogin == true)
		{
			User::update_setting(Auth::$id, ['theme' => $theme_id]);
		}
	}

	public static function reset()
	{
		setcookie(self::COOKIE_NAME, '', 1, '/');
		if(Auth::$isLogin == true)
		{
			User::update_setting(Auth::$id, ['theme' => env(DotEnv::DEFAULT_THEME)]);
		}
	}

	public static function path()
	{
		return VIEW_PATH.self::$themes_path.self::$current_theme.'/';
	}

	public static function list()
	{
		return self::$accept_list;
	}

	public static function load_css($path = null, $type = "text/css")
	{
		if($path == "")
		{
			return null;
		}

		return '<link rel="stylesheet" type="'.$type.'" href="'.APP_URL.'/assets/styles/'.self::$current_theme.'/'.trim($path, '/').'?v='.time().'" />';
	}

	public static function load_js($path = null, $type = "text/javascript")
	{
		if($path == "")
		{
			return null;
		}

		return '<script type="'.$type.'" src="'.APP_URL.'/assets/styles/'.self::$current_theme.'/'.trim($path, '/').'?v='.time().'"></script>';
	}
}





?>