<?php


class serverBootHandler {

	public static function bootstrap()
	{

	}

	public static function web()
	{
		/// mặc định chuyển đến trang đăng nhập nếu chưa đăng nhập
		if(env(DotEnv::APP_REQUIRED_LOGIN, false) && !Auth::isLogin() && Router::$current_route != 'is_auth_route' )
		{
			return Router::redirect('*', RouteMap::get('login'));
		}

		self::boot_view();
		///giao diện web
		Controller::load('themeController');
	}

	public static function api()
	{
		if(env(DotEnv::APP_REQUIRED_LOGIN, false) && !Auth::isLogin())
		{
			echo json_encode(['code' => 403, 'message' => 'Truy cập bị từ chối']);
			return false;
		}
	}

	public static function boot_view()
	{

		View::addData('_version', env('APP_VERSION'));

		$genres = [];
		foreach(Genres::list() as $genre)
		{
			$genres[$genre['id']] = $genre['name'];
		}
		View::addData('_genres', $genres);
		

		if(Auth::$isLogin == true)
		{
			View::addData('_count_message', Messenger::count_new_inbox());

			$_count_notification = Notification::count_new();
			View::addData('_count_notification', $_count_notification);
			$_count_bookmark = Bookmark::count_follows(Bookmark::TYPE_UNREAD);
			View::addData('_count_bookmark', $_count_bookmark);
			
			if(UserPermission::has('admin_team_approval')) {
				$_count_approval_team = Team::count(['active[!]' => Team::IS_ACTIVE]);
				View::addData('_count_approval_team', $_count_approval_team);				
			}


			if($_count_notification > 0)
			{
				View::addData('_notification', Notification::get_list_new());
			}
		}

	}

}


?>