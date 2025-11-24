<?php


class profileController implements Interface_controller {
	use Trait_profile;

	const BLOCK_INFOMATION = 'Infomation';
	const BLOCK_LOGINDEVICE = 'Login-Device';
	const BLOCK_CHANGEPASSWORD = 'Change-Password';
	const BLOCK_SMILEY = 'Smiley';
	const BLOCK_SETTINGS = 'Settings';

	const ACTION_ADD = 'Add';
	const ACTION_EDIT = 'Edit';
	const ACTION_DELETE = 'Delete';

	public function me($block = null, $action = null)
	{
		if(Auth::$isLogin != true)
		{
			return Router::redirect('*', RouteMap::get('home'));
		}

		$form_action = Request::post(self::INPUT_FORM_ACTION, null);

		switch($form_action)
		{
			case self::ACTION_UPLOAD_IMAGE:
				$type = null;
				if(isset($_POST['save-avatar']))
				{
					$type = AvatarCover::TYPE_AVATAR;
				}
		
				if(isset($_POST['save-cover']))
				{
					$type = AvatarCover::TYPE_COVER;
				}
				Alert::push(AvatarCover::upload_avatar_cover_profile(Request::post(self::INPUT_FORM_DATA_IMAGE, null), $type));
				return Router::redirect('*', current_url());
				break;
		}

		$block_view = null;

		switch($block) {
			case self::BLOCK_LOGINDEVICE:
				$block_view = self::block_logindevice();
				break;
			case self::BLOCK_CHANGEPASSWORD:
				$block_view = self::block_changepassword();
				break;
			case self::BLOCK_SETTINGS:
				$block_view = self::block_settings();
				break;
			case self::BLOCK_SMILEY:
				$block_view = self::block_smiley($action);
				break;
			default:
				$block = self::BLOCK_INFOMATION;
				$block_view = self::block_infomation();
				break;
		}
		
		$total_comments = Comment::count([
			'user_id' => Auth::$id
		]);

		$display_name = Auth::$data['name'] != '' ? Auth::$data['name'] : Auth::$data['username'];

		return View::render_theme('profile.me', compact('block', 'block_view', 'display_name', 'total_comments'));
	}


	const BLOCK_COMMENTS = 'comments';
	const BLOCK_MANGA_UPLOAD = 'manga-upload';
	const BLOCK_MANGA_JOIN = 'manga-join';

	public function user($id, $block = null)
	{
		$user = User::get($id);

		if(!$user)
		{
			return ServerErrorHandler::error_404();
		}

		$title = 'Thành viên '.$user['username']; #edit_lang

		$block_view = null;

		switch($block) {
			case self::BLOCK_MANGA_JOIN:
				$block_view = self::block_manga_join($user);
				break;
			case self::BLOCK_MANGA_UPLOAD:
				$block_view = self::block_manga_upload($user);
				break;
			default:
				$block = self::BLOCK_COMMENTS;
				$block_view =self::block_comments($user);
				break;
		}

		$total_comments = Comment::count([
			'user_id' => $user['id']
		]);

		$display_name = $user['name'] != '' ? $user['name'] : $user['username'];

		$team = Team::get(['id' => $user['team_id']]);

		return View::render_theme('profile.user', compact('title', 'user', 'team', 'block', 'block_view', 'display_name', 'total_comments'));
	}

	public static function insertHiddenAction($action_name)
	{
		return '<input type="hidden" name="'.self::INPUT_FORM_ACTION.'" value="'.$action_name.'">';
	}
}





?>