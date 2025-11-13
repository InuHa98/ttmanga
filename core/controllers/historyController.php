<?php


class historyController {

	
	public function __construct()
	{
		if(Auth::$isLogin != true)
		{
			return Router::redirect('*', RouteMap::get('home'));
		}
	}

	public function index($type = null)
	{
		$title = 'Truyện xem gần đây'; #edit_lang
		$error = null;
		$success = null;

		$data = History::list_manga();
		$count = $data['count'];
		$history = $data['history'];
		$manga_items = $data['items'];
		$pagination = $data['pagination'];

		$view_mode = App::view_mode();

		return View::render_theme('history.index', compact('title', 'error', 'success', 'history', 'manga_items', 'pagination', 'view_mode'));
	}

}





?>