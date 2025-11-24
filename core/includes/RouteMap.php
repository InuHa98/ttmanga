<?php

class RouteMap {

	public const ROUTES = [
		'home' => '/',
		'login' => '/Login',
		'logout' => '/Logout',
		'register' => '/Register',
		'reset_password' => '/Reset-password',
		'change_password' => '/Change-password',
		'forgot_password' => '/Forgot-password',
		'profile' => '/User/{id(me|[0-9]+)}/{block?}/{action?}',
		'notification' => '/Notification/{id(seen|unseen|\d+)?}',
		'bookmark' => '/Bookmark/{type(new|api)?}',
		'history' => '/History',
		'manga' => '/Manga/{id(\d+)?}',
		'manga_management' => '/Manga-management/{action}/{id(\d+)?}',
		'chapter' => '/Manga/{id_manga(\d+)}/Chapter/{id_chapter(\d+)}',
		'search_manga' => '/Manga/search',
		'comment' => '/Comment',
		'comments' => '/Comments',
		'team' => '/Team/{name}',
		'messenger' => '/Messenger/{block(\w+)?}/{id(seen|unseen|\d+)?}',
		'search' => 'Search',
		'admin_panel' => '/Admin-panel/{group?}/{block?}/{action?}',
		'my_team' => '/My-team/{block?}/{action?}',
		'ajax' => '/Ajax/{name}/{action?}',
		'report' => '/Report/{block?}',
		'tool_leech' => '/Tool-leech/{block}',
	];

	private const ERROR = '#route_not_found';

	public static function get($name = null, $data = [])
	{
		if($name == "")
		{
			return null;
		}

		if(!isset(self::ROUTES[$name]))
		{
			return self::ERROR;
		}

		$route = self::ROUTES[$name];

		if(!$data)
		{
			return App::$url.'/'.trim(preg_replace("/\{(\w+)(\(.*\))?(\??)\}/U", "", $route), '/');
		}

		$i = -1;
		$route = preg_replace_callback("/\{(\w+)(\(.*\))?(\??)\}/U", function($matches) use (&$i, $data) {
			$i++;
			return urlencode(isset($data[$matches[1]]) ? $data[$matches[1]] : (isset($data[$i]) ? $data[$i] : ''));
		}, $route);

		return App::$url.'/'.trim($route, '/');
	}

	public static function join($string, $route_name, $data = []) 
	{
		$route = self::get($route_name, $data);
		return trim($route.'', '/').$string;
	}

	public static function build_query($params, $route_name, $data = []) {
		$route = self::get($route_name, $data);
		return trim($route.'', '?').'?'.http_build_query($params);
	}

}

?>