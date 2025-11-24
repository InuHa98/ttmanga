<?php


class homeController {

	public function index()
	{
		$title = env(DotEnv::APP_TITLE);
		$random_manga = Manga::list_random();

		$limit_new_chapter = 60;

		$new_chapters = Manga::list([
			// Chapter::$table.'.id[!]' => null,
			'ORDER' => [
				Chapter::$table.'.created_at' => 'DESC'
			],
			'LIMIT' => $limit_new_chapter
		]);

		$new_manga = Manga::list([
			'ORDER' => [
				'created_at' => 'DESC'
			],
			'LIMIT' => 10
		]);

		$view_manga = Manga::list([
			'ORDER' => [
				'view' => 'DESC'
			],
			'LIMIT' => 10
		]);

		$follow_manga = Manga::list([
			'ORDER' => [
				'follow' => 'DESC'
			],
			'LIMIT' => 10
		]);

		$new_comments = Comment::list([
			'ORDER' => [
				'created_at' => 'DESC'
			],
			'LIMIT' => 10
		]);

		$total_manga = Manga::count();
		$total_chapter = Chapter::count();

		return View::render_theme('home.index', compact('title', 'random_manga', 'limit_new_chapter', 'new_chapters', 'new_manga', 'view_manga', 'follow_manga', 'new_comments', 'total_manga', 'total_chapter'));
	}
}





?>