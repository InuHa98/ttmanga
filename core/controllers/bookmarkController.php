<?php


class bookmarkController {

	
	public const TYPE_NEW_CHAPTER = 'new';

	public const NAME_FORM_ACTION = 'action_form';

	public const INPUT_ID = 'id_form';

	public const ACTION_MAKE_READ_ALL = 'make_read_all';
	public const ACTION_MAKE_UNREAD_ALL = 'make_unread_all';
	public const ACTION_MAKE_READ = 'make_read';
	public const ACTION_MAKE_UNREAD = 'make_unread';
	public const ACTION_REMOVE = 'remove_bookmark';
	public const ACTION_ADD = 'add_bookmark';

	public function __construct()
	{
		if(Auth::$isLogin != true)
		{
			return Router::redirect('*', RouteMap::get('home'));
		}
	}

	public function index($type = null)
	{
		$title = 'Truyện đang theo dõi'; #edit_lang
		$error = null;
		$success = null;

		if (Security::validate()) {
			$action = trim(Request::post(self::NAME_FORM_ACTION, ''));
			switch($action) {
				case self::ACTION_MAKE_READ_ALL:
					Bookmark::make_read_all();
					$success = 'Đánh dấu đã đọc tất cả truyện theo dõi thành công'; #edit_lang
					break;
				case self::ACTION_MAKE_UNREAD_ALL:
					Bookmark::make_unread_all();
					$success = 'Đánh dấu chưa đọc tất cả truyện theo dõi thành công'; #edit_lang
					break;
			}
			View::addData('_count_bookmark', Bookmark::count_follows(Bookmark::TYPE_UNREAD));
		}

		$list_follow = Bookmark::list_follow($type == self::TYPE_NEW_CHAPTER ? true : false);

		$count = $list_follow['count'];
		$manga_items = $list_follow['items'];
		$pagination = $list_follow['pagination'];

		$view_mode = App::view_mode();

		$insertHiddenToken = Security::insertHiddenToken();

		return View::render_theme('bookmark.index', compact('title', 'error', 'success', 'insertHiddenToken', 'type', 'count', 'manga_items', 'pagination', 'view_mode'));
	}

	public function api()
	{
		$id = Request::post(self::INPUT_ID, null);
		$form_action = Request::post(self::NAME_FORM_ACTION, null);

		$code = 403;
		$message = 'Truy cập bị từ chối'; #edit_lang
		$data = null;

		if($id && UserPermission::has('user_follow'))
		{
			switch($form_action)
			{
				case self::ACTION_MAKE_READ:
					if(Bookmark::make_read($id) == true)
					{
						$code = 200;
						$message = 'Đánh dấu đã đọc thành công'; #edit_lang
					}
					else
					{
						$message = 'Có lỗi xảy ra. Vui lòng thử lai sau ít phút'; #edit_lang
					}
					break;

				case self::ACTION_MAKE_UNREAD:

					if(Bookmark::make_unread($id) == true)
					{
						$code = 200;
						$message = 'Đánh dấu chưa đọc thành công'; #edit_lang
					}
					else
					{
						$message = 'Có lỗi xảy ra. Vui lòng thử lai sau ít phút'; #edit_lang
					}
					break;

				case self::ACTION_ADD:
					if(Bookmark::add($id) == true)
					{
						$code = 200;
						$message = 'Thêm vào danh sách theo dõi thành công'; #edit_lang
						$data['hasFollow'] = true;
					}
					else
					{
						$message = 'Có lỗi xảy ra. Vui lòng thử lai sau ít phút'; #edit_lang
						$data['hasFollow'] = false;
					}
					break;

				case self::ACTION_REMOVE:
					if(Bookmark::remove($id) == true)
					{
						$code = 200;
						$message = 'Xoá khỏi danh sách theo dõi thành công'; #edit_lang
						$data['hasFollow'] = false;
					}
					else
					{
						$message = 'Có lỗi xảy ra. Vui lòng thử lai sau ít phút'; #edit_lang
						$data['hasFollow'] = true;
					}
					break;
			}
		}


		echo json_api($code, $message, $data);
		exit;
	}
}





?>