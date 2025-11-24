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
			$total_all_error = 0;
			$total_team_error = 0;
			$total_team_member_approval = 0;
			if (Auth::$data['team_id']) {
				$team = Team::select(['own_id'])::get(['id' => Auth::$data['team_id']]);
				if ($team) {
					$where_notification_report = [
						'status' => Report::STATUS_PENDING,
						'[RAW] EXISTS (SELECT 1 FROM <'.TeamManga::$table.'> WHERE <'.TeamManga::$table.'.manga_id> = <{table}.manga_id> AND <'.TeamManga::$table.'.team_id> = :team_id)' => [
							'team_id' => Auth::$data['team_id']
						]
					];	
					if ($team['own_id'] == Auth::$data['id']) {
						$total_team_member_approval = RequestJoinTeam::count([
							'team_id' => Auth::$data['team_id']
						]);
					} else {
						$where_notification_report['OR'] = [
							'core_mangas.user_upload' => Auth::$data['id'],
							'core_chapters.user_upload' => Auth::$data['id']
						];
					}
					$total_team_error = Report::count($where_notification_report);
				}
			}

			if (UserPermission::isAdmin()) {
				$total_all_error = Report::count(['status' => Report::STATUS_PENDING]);
			}
			View::addData('_count_all_error', $total_all_error);
			View::addData('_count_team_error', $total_team_error);
			View::addData('_count_team_member_approval', $total_team_member_approval);


			View::addData('_count_message', Messenger::count_new_inbox());

			$_count_notification = Notification::count_new();
			View::addData('_count_notification', $_count_notification);

			$lst_bookmark = [];
			$_count_bookmark = Bookmark::count_follows(Bookmark::TYPE_UNREAD);
			if ($_count_bookmark > 0) {
				$lst_bookmark = Bookmark::select([
					'<core_mangas.id>',
					'<core_mangas.name>',
					'<core_mangas.image>',
					'<core_chapters.name> AS <name_last_chapter>',
					'<core_chapters.created_at> AS <created_last_chapter>'
				])::list([
					'is_read' => Bookmark::TYPE_UNREAD,
					'user_id' => Auth::$id
				]);
			}
			View::addData('_count_bookmark', $_count_bookmark);
			View::addData('_lst_bookmark', $lst_bookmark);
			
			$_count_approval_team = 0;
			if(UserPermission::has('admin_team_approval')) {
				$_count_approval_team = Team::count(['active[!]' => Team::IS_ACTIVE]);		
			}
			View::addData('_count_approval_team', $_count_approval_team);

			if($_count_notification > 0)
			{
				View::addData('_notification', Notification::get_list_new());
			}
		}

	}

}


?>