<?php


class ServerErrorHandler {

	public static function error_404()
	{
		ob_start();
		header(Request::protocol()." 404 Not Found", true, 404);

		$title = lang('error_404', 'title');
		$text = lang('error_404', 'text');
		$desc = lang('error_404', 'desc');
		$button = lang('error_404', 'button');
		View::render('error.404', compact('title', 'text', 'desc', 'button'));
		exit();
	}

}


?>