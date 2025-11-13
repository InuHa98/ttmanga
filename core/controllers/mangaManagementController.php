<?php

class mangaManagementController {

	const ACTION_NEW_MANGA = 'new-manga';
	const ACTION_NEW_CHAPTER = 'new-chapter';
	const ACTION_DETAIL = 'detail';
	const ACTION_EDIT_MANGA = 'edit-manga';
	const ACTION_EDIT_CHAPTER = 'edit-chapter';
	const ACTION_DELETE_MANGA = 'delete-manga';
	const ACTION_TEAM_PARTNER = 'team-partner';
	const ACTION_TOOL_LEECH = 'tool-leech';
	const ACTION_SORT_CHAPTER = 'sort-chapter';
	const ACTION_ADD = 'add';
	const ACTION_CHANGE = 'change';
	const ACTION_REMOVE = 'remove';
	const ACTION_DELETE = 'delete';
	const ACTION_DELETE_MULTIPLE = 'delete-multiple';

	public const INPUT_ID = 'id';
	public const INPUT_ACTION = '_action';
	public const INPUT_NAME = 'name';
	public const INPUT_NAME_OTHER = 'name_other';
	public const INPUT_TEAM = 'team';
	public const INPUT_AUTH = 'auth';
	public const INPUT_GENRES = 'genres';
	public const INPUT_STATUS = 'status';
	public const INPUT_TYPE = 'type';
	public const INPUT_DESC = 'desc';
	public const INPUT_IMAGE = 'image';
	public const INPUT_COVER = 'cover';
	public const INPUT_LINK = 'link';
	public const INPUT_REASON = 'reason';
	public const INPUT_INDEX = 'index';

	private static $team = null;

	public function index($action, $id = null)
	{
		if (!Auth::$data) {
			return ServerErrorHandler::error_404();
		}

		self::$team = Team::get(['id' => Auth::$data['team_id']]);
		if (!self::$team) {
			return ServerErrorHandler::error_404();
		}

		if (self::$team['user_ban'] != Team::IS_NOT_BAN && !UserPermission::isAdmin() && !UserPermission::has('admin_manga_edit')) {
			$user_ban = User::get(['id' => self::$team['user_ban']]);
			return View::render_theme('team.banned', [
				'title' => 'Cảnh báo nhóm dịch bị cấm', #edit_lang
				'team' => self::$team,
				'user_ban' => $user_ban
			]); 
		}

		View::addData('_team', self::$team);
		
		$block_view = null;
		switch($action) {

			case self::ACTION_NEW_CHAPTER:
				$block_view = self::new_chapter($id);
				break;

			case self::ACTION_EDIT_CHAPTER:
				$block_view = self::edit_chapter($id);
				break;

			case self::ACTION_NEW_MANGA:
				$block_view = self::new_manga();
				break;

			case self::ACTION_EDIT_MANGA:
				$block_view = self::edit_manga($id);
				break;

			case self::ACTION_TEAM_PARTNER:
				$block_view = self::team_partner($id);
				break;

			case self::ACTION_SORT_CHAPTER:
				$block_view = self::sort_chapter($id);
				break;

			case self::ACTION_TOOL_LEECH:
				$block_view = self::tool_leech($id);
				break;

			case self::ACTION_DETAIL:
			default:
				$action = self::ACTION_DETAIL;
				$block_view = self::detail($id);
				break;
		}

		return View::render_theme('manga_management.index', compact(
			'action',
			'block_view'
		));
	}

	public static function is_own_manga($manga_id) {
		if (!Auth::$isLogin) {
			return false;
		}
		$manga = is_array($manga_id) ? $manga_id : Manga::get(['id' => $manga_id]);
		$team = self::$team ?? Team::get(['id' => Auth::$data['team_id']]);
		return UserPermission::isAdmin() || UserPermission::has('admin_manga_edit') || $team['own_id'] == Auth::$data['id'] || $manga['user_upload'] == Auth::$data['id'];
	}

	public static function is_own_chapter($chapter_id) {
		if (!Auth::$isLogin) {
			return false;
		}
		$chapter = is_array($chapter_id) ? $chapter_id : Chapter::get(['id' => $chapter_id]);
		$team = self::$team ?? Team::get(['id' => Auth::$data['team_id']]);
		return UserPermission::isAdmin() || UserPermission::has('admin_manga_edit') || $team['own_id'] == Auth::$data['id'] || $chapter['user_upload'] == Auth::$data['id'];
	}

	private function detail($id) {
		$success = null;
		$error = null;
		$manga = Manga::get(['id' => $id]);
		if(!$manga)
		{
			return ServerErrorHandler::error_404();
		}

		if (Security::validate() == true) {
			$action = trim(Request::post(self::INPUT_ACTION, ''));
			switch($action) {
				case self::ACTION_DELETE_MANGA:
					
					if (self::is_own_manga($manga) && Manga::update($manga['id'], [
						'is_trash' => Manga::IS_INACTIVE
					]) > 0) {
						if ($manga['user_upload'] != Auth::$data['id']) {
							$reason = trim(Request::post(self::INPUT_REASON, ''));
							Notification::create([
								'user_id' => $manga['user_upload'],
								'from_user_id' => Auth::$data['id'],
								'type' => Notification::TYPE_DELETE_MANGA,
								'data' => [
									'name' => $manga['name'],
									'reason' => $reason
								]
							]);							
						}
						Alert::push([
							'type' => 'success',
							'message' => 'Xoá truyện thành công' #edit_lang
						]);
						return redirect_route('my_team');
					} else {
						$error = 'Có lỗi xảy ra. Vui lòng thử lại sau ít phút';  #edit_lang
					}
					break;

				case self::ACTION_CHANGE:
					$user_id = intval(Request::post(self::INPUT_ID, null));
					$user = User::get(['id' => $user_id]);
					
					if (!$user || !TeamManga::has(['team_id' => $user['team_id'], 'manga_id' => $manga['id']])) {
						$error = 'Không tìm thấy thành viên'; #edit_lang
					} else {
						if (self::is_own_manga($manga) && Manga::update($manga['id'], [
							'user_upload' => $user['id']
						]) > 0) {
							$manga = Manga::get(['id' => $id]);
							$success = 'Thay đổi quyền sở hữu truyện thành công'; #edit_lang
						} else {
							$error = 'Có lỗi xảy ra. Vui lòng thử lại sau ít phút';  #edit_lang
						}
					}
					break;

				case self::ACTION_DELETE:
					$id = intval(Request::post(self::INPUT_ID, null));
					$chapter = Chapter::get(['id' => $id]);
					
					if (!$chapter) {
						$error = 'Không tìm thấy chương truyện'; #edit_lang
					} else {
						if (self::is_own_chapter($chapter) && Chapter::delete($chapter['id']) > 0) {

							Chapter::update(['manga_id' => $manga['id'], 'index[>]' => $chapter['index']], ['index[-]' => 1]);

							$last_chapter = Chapter::get([
								'manga_id' => $manga['id'],
								'ORDER' => [
									'created_at' => 'DESC'
								],
								'LIMIT' => 1
							]);
							Manga::update($manga['id'], ['id_last_chapter' => $last_chapter['id'] ?? 0]);

							Comment::delete(['chapter_id' => $chapter['id']]);

							$lst_history = History::list([
								'data[~]' => '"'.$manga['id'].'":["'.$chapter['id'].'",'
							]);
							foreach($lst_history as $o) {
								$data = json_decode($o['data'], true);
								unset($data[$manga['id']]);
								History::update($o['id'], $data);
							}

							if ($chapter['user_upload'] != Auth::$data['id']) {
								$reason = trim(Request::post(self::INPUT_REASON, ''));
								Notification::create([
									'user_id' => $chapter['user_upload'],
									'from_user_id' => Auth::$data['id'],
									'type' => Notification::TYPE_DELETE_CHAPTER,
									'data' => [
										'name' => $chapter['name'],
										'reason' => $reason
									]
								]);					
							}

							$success = 'Xoá thành công chương: '.$chapter['name']; #edit_lang
						} else {
							$error = 'Bạn phải là người upload chương này mới có thể xoá';  #edit_lang
						}
					}
					break;

				case self::ACTION_DELETE_MULTIPLE:
					$ids = Request::post(self::INPUT_ID, []);
					$chapters = Chapter::select(['id', 'user_upload', 'name'])::list(['id' => $ids]);

					$chapters = array_filter($chapters, function($o) {
						return self::is_own_chapter($o);
					});
					
					if (!$chapters) {
						$error = 'Không tìm thấy chương truyện nào'; #edit_lang
					} else {
						$chapter_ids = array_column($chapters, 'id');
						$deleted = Chapter::delete(['id' => $chapter_ids]);
						if ($deleted > 0) {

							$lst_chapter = Chapter::select([
								'id', 'index'
							])::list([
								'manga_id' => $manga['id'],
								'ORDER' => [
									'index' => 'ASC'
								]
							]);
							$index = 0;
							foreach($lst_chapter as $c) {
								Chapter::update($c['id'], ['index' => $index++]);
							}

							$last_chapter = Chapter::get([
								'manga_id' => $manga['id'],
								'ORDER' => [
									'created_at' => 'DESC'
								],
								'LIMIT' => 1
							]);
							Manga::update($manga['id'], ['id_last_chapter' => $last_chapter['id'] ?? 0]);

							Comment::delete(['chapter_id' => $chapter_ids]);
							foreach($chapters as $c) {
								$lst_history = History::list([
									'data[~]' => '"'.$manga['id'].'":["'.$c['id'].'",'
								]);
								foreach($lst_history as $o) {
									$data = json_decode($o['data'], true);
									unset($data[$manga['id']]);
									History::update($o['id'], $data);
								}

								if ($c['user_upload'] != Auth::$data['id']) {
									$reason = trim(Request::post(self::INPUT_REASON, ''));
									Notification::create([
										'user_id' => $c['user_upload'],
										'from_user_id' => Auth::$data['id'],
										'type' => Notification::TYPE_DELETE_CHAPTER,
										'data' => [
											'name' => $c['name'],
											'manga_id' => $manga['id'],
											'reason' => $reason
										]
									]);					
								}
							}
							$success = 'Xoá thành công '.$deleted.' chương truyện'; #edit_lang
						} else {
							$error = 'Bạn phải là người upload chương này mới có thể xoá';  #edit_lang
						}
					}
					break;
			}
		}

		$name_other = Manga::get_name_other($manga);
		$genres = Manga::list_genres($manga);
		$auths = Manga::get_auth($manga);
		$teams = Manga::get_team($manga);
		$status = Manga::get_status_name($manga);
		$links = json_decode($manga['links'], true);
		$own = User::select([
			'id',
			'name',
			'username',
			'avatar',
			'user_ban',
			'(SELECT <color> FROM <core_roles> WHERE <id> = <{table}.role_id>) AS <role_color>'
		])::get($manga['user_upload']);

		$chapters = Chapter::join([
			'LEFT JOIN <core_users> ON <{table}.user_upload> = <core_users.id>'
		])::select([
			'id',
			'name',
			'created_at',
			'user_upload',
			'<core_users.name> AS <uploader_name>',
			'<core_users.username> AS <uploader_username>',
			'<core_users.avatar> AS <uploader_avatar>',
			'<core_users.user_ban> AS <uploader_ban_id>',
			'(SELECT <color> FROM <core_roles> WHERE <id> = <core_users.role_id>) AS <uploader_role_color>'
		])::list([
			'manga_id' => $manga['id']
		]);

		$lst_teams = TeamManga::list([
			'manga_id' => $manga['id']
		]);

		$insertHiddenToken = Security::insertHiddenToken();
		return [
			'title' => 'Quản lý truyện: '.$manga['name'], #edit_lang,
			'view' => 'manga_management.detail',
			'data' => compact(
				'success',
				'error',
				'insertHiddenToken',
				'own',
				'manga',
				'name_other',
				'genres',
				'auths',
				'teams',
				'status',
				'links',
				'chapters',
				'lst_teams'
			)
		];
	}

	private function new_manga() {
		$success = null;
		$error = null;

		$name = trim(Request::post(self::INPUT_NAME, ''));
		$name_other = Request::post(self::INPUT_NAME_OTHER, []);
		$author = Request::post(self::INPUT_AUTH, []);
		$teams = Request::post(self::INPUT_TEAM, [self::$team['name']]);
		$genres = Request::post(self::INPUT_GENRES, []);
		$status = Request::post(self::INPUT_STATUS, Manga::STATUS_ONGOING);
		$type = intval(Request::post(self::INPUT_TYPE, Manga::TYPE_NOT_WARNING));
		$image = trim(Request::post(self::INPUT_IMAGE, ''));
		$cover = trim(Request::post(self::INPUT_COVER, ''));
		$desc = trim(Request::post(self::INPUT_DESC, ''));
		$links = Request::post(self::INPUT_LINK, []);

		$name_other = array_filter(array_unique($name_other), function($v, $k) {
			return $v != '';
		}, ARRAY_FILTER_USE_BOTH);

		$author = array_filter(array_unique($author), function($v, $k) {
			return $v != '';
		}, ARRAY_FILTER_USE_BOTH);

		$teams = array_filter(array_unique($teams), function($v, $k) {
			return $v != '';
		}, ARRAY_FILTER_USE_BOTH);

		$genres = array_filter($genres, function($v, $k) {
			return $v == 1;
		}, ARRAY_FILTER_USE_BOTH);

		switch($status) {
			case Manga::STATUS_COMPLETE: break;
			case Manga::STATUS_DROP: break;
			default:
				$status = Manga::STATUS_ONGOING;
				break;
		}

		switch($type) {
			case Manga::TYPE_WARNING_16: break;
			case Manga::TYPE_WARNING_17: break;
			case Manga::TYPE_WARNING_18: break;
			default:
				$type = Manga::TYPE_NOT_WARNING;
				break;
		}

		$links = array_filter(array_unique($links), function($v, $k) {
			$exp = explode('|', $v ?? '');
			return ValidateHelper::isValidURL(trim($exp[0]));
		}, ARRAY_FILTER_USE_BOTH);

		
		if (Security::validate() == true) {
			$count_genres = !$genres ? 0 : Genres::count(['id' => array_keys($genres)]);
			if ($name == '') {
				$error = 'Vui lòng nhập tên truyện'; #edit_lang
			}
			else if (!$author) {
				$error = 'Vui lòng nhập tên tác giả'; #edit_lang
			}
			else if (!$teams) {
				$error = 'Vui lòng nhập tên nhóm dịch'; #edit_lang
			}
			else if ($count_genres < 1) {
				$error = 'Vui lòng chọn ít nhất 1 thể loại'; #edit_lang
			}
			else if (!ValidateHelper::isValidURL($image)) {
				$error = 'Link ảnh không hợp lệ'; #edit_lang
			}
			else if ($cover != '' && !ValidateHelper::isValidURL($cover)) {
				$error = 'Link ảnh bìa không hợp lệ'; #edit_lang
			}
			else if ($desc == '') {
				$error = 'Vui lòng nhập mô tả truyện'; #edit_lang
			}
			else {
				if (Manga::create(compact(
					'name',
					'name_other',
					'author',
					'teams',
					'genres' ,
					'status',
					'type',
					'image',
					'cover',
					'desc',
					'links'
				)) > 0) {
					Alert::push([
						'type' => 'success',
						'message' => 'Thêm mới truyện thành công' #edit_lang
					]);
					return redirect_route('my_team');
				} else {
					$error = 'Có lỗi xảy ra. Vui lòng thử lại sau ít phút';  #edit_lang
				}
			}
		}

		$insertHiddenToken = Security::insertHiddenToken();

		return [
			'title' => 'Thêm truyện mới', #edit_lang,
			'view' => 'manga_management.add_edit_manga',
			'data' => compact(
				'success',
				'error',
				'name',
				'name_other',
				'author',
				'teams',
				'genres',
				'status',
				'type',
				'image',
				'cover',
				'desc',
				'links',
				'insertHiddenToken'
			)
		];
	}
	
	private function edit_manga($id) {
		$success = null;
		$error = null;

		$manga = Manga::get(['id' => $id]);
		if(!$manga || !self::is_own_manga($manga))
		{
			return ServerErrorHandler::error_404();
		}


		$arr_team = Manga::get_team_name($manga, true);
		$arr_genres = array_fill_keys(array_filter(explode(',', $manga['genres_id'] ?? '')), 1);

		$arr_link = $manga['links'] ? array_map(function($o) {
			return trim($o['url'].'') . '|' . trim($o['text'].'');
		}, json_decode($manga['links'], true)) : [];

		$name = trim(Request::post(self::INPUT_NAME, $manga['name']));
		$name_other = Request::post(self::INPUT_NAME_OTHER, explode(Manga::SEPARATOR, $manga['name_other'] ?? ''));
		$author = Request::post(self::INPUT_AUTH, explode(Manga::SEPARATOR, $manga['auth'] ?? ''));
		$teams = Request::post(self::INPUT_TEAM, $arr_team);
		$genres = Request::post(self::INPUT_GENRES, $arr_genres);
		$status = Request::post(self::INPUT_STATUS, $manga['status']);
		$type = intval(Request::post(self::INPUT_TYPE, $manga['type']));
		$image = trim(Request::post(self::INPUT_IMAGE, $manga['image']));
		$cover = trim(Request::post(self::INPUT_COVER, $manga['cover']));
		$desc = trim(Request::post(self::INPUT_DESC, $manga['text']));
		$links = Request::post(self::INPUT_LINK, $arr_link);

		$name_other = array_filter(array_unique($name_other), function($v, $k) {
			return $v != '';
		}, ARRAY_FILTER_USE_BOTH);

		$author = array_filter(array_unique($author), function($v, $k) {
			return $v != '';
		}, ARRAY_FILTER_USE_BOTH);

		$teams = array_filter(array_unique($teams), function($v, $k) {
			return $v != '';
		}, ARRAY_FILTER_USE_BOTH);

		$genres = array_filter($genres, function($v, $k) {
			return $v == 1;
		}, ARRAY_FILTER_USE_BOTH);

		switch($status) {
			case Manga::STATUS_COMPLETE: break;
			case Manga::STATUS_DROP: break;
			default:
				$status = Manga::STATUS_ONGOING;
				break;
		}

		switch($type) {
			case Manga::TYPE_WARNING_16: break;
			case Manga::TYPE_WARNING_17: break;
			case Manga::TYPE_WARNING_18: break;
			default:
				$type = Manga::TYPE_NOT_WARNING;
				break;
		}

		$links = array_filter(array_unique($links), function($v, $k) {
			$exp = explode('|', $v ?? '');
			return ValidateHelper::isValidURL(trim($exp[0]));
		}, ARRAY_FILTER_USE_BOTH);

		
		if (Security::validate() == true) {
			$count_genres = !$genres ? 0 : Genres::count(['id' => array_keys($genres)]);
			if ($name == '') {
				$error = 'Vui lòng nhập tên truyện'; #edit_lang
			}
			else if (!$author) {
				$error = 'Vui lòng nhập tên tác giả'; #edit_lang
			}
			else if (!$teams) {
				$error = 'Vui lòng nhập tên nhóm dịch'; #edit_lang
			}
			else if ($count_genres < 1) {
				$error = 'Vui lòng chọn ít nhất 1 thể loại'; #edit_lang
			}
			else if (!ValidateHelper::isValidURL($image)) {
				$error = 'Link ảnh không hợp lệ'; #edit_lang
			}
			else if ($cover != '' && !ValidateHelper::isValidURL($cover)) {
				$error = 'Link ảnh bìa không hợp lệ'; #edit_lang
			}
			else if ($desc == '') {
				$error = 'Vui lòng nhập mô tả truyện'; #edit_lang
			}
			else {
				if (Manga::changeInfo($manga['id'], compact(
					'name',
					'name_other',
					'author',
					'teams',
					'genres' ,
					'status',
					'type',
					'image',
					'cover',
					'desc',
					'links'
				)) > 0) {
					$success = 'Cập nhật truyện thành công'; #edit_lang
				} else {
					$error = 'Có lỗi xảy ra. Vui lòng thử lại sau ít phút';  #edit_lang
				}
			}
		}

		$insertHiddenToken = Security::insertHiddenToken();

		return [
			'title' => 'Chỉnh sửa truyện: '.$manga['name'], #edit_lang,
			'view' => 'manga_management.add_edit_manga',
			'data' => compact(
				'success',
				'error',
				'manga',
				'name',
				'name_other',
				'author',
				'teams',
				'genres',
				'status',
				'type',
				'image',
				'cover',
				'desc',
				'links',
				'insertHiddenToken'
			)
		];
	}

	private function new_chapter($manga_id) {
		$success = null;
		$error = null;

		$manga = Manga::get(['id' => $manga_id]);
		if(!$manga)
		{
			return ServerErrorHandler::error_404();
		}
		$name = trim(Request::post(self::INPUT_NAME, ''));
		$index = trim(Request::post(self::INPUT_INDEX, Chapter::POSITION_TOP));
		$links = Request::post(self::INPUT_LINK, []);
		$images = Request::post(self::INPUT_IMAGE, []);

		$links = array_filter(array_unique($links), function($o) {
			return ValidateHelper::isValidURL(trim($o));
		});

		$images = array_filter($images, function($o) {
			return ValidateHelper::isValidURL(trim($o));
		});

		if (Security::validate() == true) {
			if ($name == '') {
				$error = 'Vui lòng nhập tên chương'; #edit_lang
			}
			else if (!$images) {
				$error = 'Phải có ít nhất 1 hình ảnh'; #edit_lang
			}
			else if (Chapter::has([
				'name[~]' => $name,
				'manga_id' => $manga['id']
			])) {
				$error = 'Tên chương đã tồn tại'; #edit_lang
			} else {
				if (Chapter::create(compact(
					'manga_id',
					'name',
					'index',
					'links',
					'images'
				)) > 0) {
					Alert::push([
						'type' => 'success',
						'message' => 'Thêm mới chương thành công' #edit_lang
					]);
					return redirect_route('manga_management', ['action' => mangaManagementController::ACTION_DETAIL, 'id' => $manga['id']]);
				} else {
					$error = 'Có lỗi xảy ra. Vui lòng thử lại sau ít phút';  #edit_lang
				}
			}
		}

		$insertHiddenToken = Security::insertHiddenToken();

		$items = [];
		return [
			'title' => 'Thêm chương mới: '.$manga['name'], #edit_lang,
			'view' => 'manga_management.add_edit_chapter',
			'data' => compact(
				'success',
				'error',
				'manga',
				'name',
				'index',
				'links',
				'images',
				'items',
				'insertHiddenToken'
			)
		];
	}

	private function edit_chapter($id_chapter) {
		$success = null;
		$error = null;

		$chapter = Chapter::get(['id' => $id_chapter]);
		if(!$chapter)
		{
			return ServerErrorHandler::error_404();
		}

		$manga = Manga::get(['id' => $chapter['manga_id']]);
		if(!$manga || !self::is_own_chapter($chapter))
		{
			return ServerErrorHandler::error_404();
		}

		$name = trim(Request::post(self::INPUT_NAME, $chapter['name']));
		$index = trim(Request::post(self::INPUT_INDEX, $chapter['index']));
		$links = Request::post(self::INPUT_LINK, $chapter['download'] ? json_decode($chapter['download'], true) : []);
		$images = Request::post(self::INPUT_IMAGE, explode(Chapter::SEPARATOR, $chapter['image'] ?? ''));

		$links = array_filter(array_unique($links), function($o) {
			return ValidateHelper::isValidURL(trim($o));
		});


		$images = array_filter($images, function($o) {
			return ValidateHelper::isValidURL(trim($o));
		});

		if (Security::validate() == true) {
			if ($name == '') {
				$error = 'Vui lòng nhập tên chương'; #edit_lang
			}
			else if (!$images) {
				$error = 'Phải có ít nhất 1 hình ảnh'; #edit_lang
			}
			else if (Chapter::has([
				'name[~]' => $name,
				'manga_id' => $manga['id'],
				'id[!]' => $chapter['id']
			])) {
				$error = 'Tên chương đã tồn tại'; #edit_lang
			} else {
				$manga_id = $manga['id'];
				if (Chapter::change($chapter['id'], compact(
					'manga_id',
					'name',
					'index',
					'links',
					'images'
				)) > 0) {
					$success = 'Lưu lại thành công'; #edit_lang
				} else {
					$error = 'Có lỗi xảy ra. Vui lòng thử lại sau ít phút';  #edit_lang
				}
			}
		}

		$insertHiddenToken = Security::insertHiddenToken();

		$items = [];
		foreach($images as $img) {
			$items[] = [
				'type' => 'image',
				'image' => $img,
				'link' => $img
			];
		}
		return [
			'title' => 'Chỉnh sửa chương truyện: '.$chapter['name'], #edit_lang,
			'view' => 'manga_management.add_edit_chapter',
			'data' => compact(
				'success',
				'error',
				'manga',
				'chapter',
				'name',
				'index',
				'links',
				'images',
				'items',
				'insertHiddenToken'
			)
		];
	}

	private function team_partner($id) {
		$success = null;
		$error = null;

		$manga = Manga::get(['id' => $id]);
		if(!$manga || !self::is_own_manga($manga))
		{
			return ServerErrorHandler::error_404();
		}
		$own_user = User::get(['id' => $manga['user_upload']]);
		$own_team = Team::get(['id' => $own_user['team_id']]);

		if (Security::validate() == true) {
			$action = trim(Request::post(self::INPUT_ACTION, ''));
			$id = intval(Request::post(self::INPUT_ID, null));

			switch($action) {
				case self::ACTION_ADD:
					$team = Team::get([
						'id' => $id,
						'[RAW] <{table}.id> NOT IN (SELECT <core_team_mangas.team_id> FROM <core_team_mangas> WHERE <core_team_mangas.team_id> = <{table}.id> AND <core_team_mangas.manga_id> = :manga_id)' => [
							'manga_id' => $manga['id']
						]
					]);
					if (!$team) {
						$error = 'Không tìm thấy nhóm dịch'; #edit_lang
					} else {
						if (TeamManga::create([
							'manga_id' => $manga['id'],
							'team_id' => $team['id']
						]) > 0) {
							$team_members = User::select(['id'])::list(['team_id' => $team['id']]);
							Notification::create([
								'user_id' => array_column($team_members, 'id'),
								'from_user_id' => Auth::$data['id'],
								'type' => Notification::TYPE_ADD_TEAM_PARTNER,
								'data' => [
									'manga_id' => $manga['id']
								]
							]);
							$success = 'Thêm nhóm cộng sự thành công'; #edit_lang
						} else {
							$error = 'Có lỗi xảy ra. Vui lòng thử lại sau ít phút'; #edit_lang
						}
					}
					break;

				case self::ACTION_REMOVE:
					$team = Team::get([
						'id' => $id,
						'[RAW] <{table}.id> IN (SELECT <core_team_mangas.team_id> FROM <core_team_mangas> WHERE <core_team_mangas.team_id> != :team_id AND <core_team_mangas.manga_id> = :manga_id)' => [
							'manga_id' => $manga['id'],
							'team_id' => $own_team['id'] 
						]
					]);
					if (!$team) {
						$error = 'Không tìm thấy nhóm dịch'; #edit_lang
					} else {
						if (TeamManga::delete([
							'manga_id' => $manga['id'],
							'team_id' => $team['id']
						]) > 0) {
							$team_members = User::select(['id'])::list(['team_id' => $team['id']]);
							Notification::create([
								'user_id' => array_column($team_members, 'id'),
								'from_user_id' => Auth::$data['id'],
								'type' => Notification::TYPE_REMOVE_TEAM_PARTNER,
								'data' => [
									'manga_id' => $manga['id']
								]
							]);
							$success = 'Xoá nhóm cộng sự thành công'; #edit_lang
						} else {
							$error = 'Có lỗi xảy ra. Vui lòng thử lại sau ít phút'; #edit_lang
						}
					}
					break;
			}
		}


		$where = [
			'[RAW] <{table}.id> IN (SELECT <core_team_mangas.team_id> FROM <core_team_mangas> WHERE <core_team_mangas.team_id> != :team_id AND <core_team_mangas.manga_id> = :manga_id)' => [
				'manga_id' => $manga['id'],
				'team_id' => $own_team['id'] 
			]
		];

		$count = Team::count($where);
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();
		$lst_items = Team::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));

		$insertHiddenToken = Security::insertHiddenToken();
		return [
			'title' => 'Nhóm cộng sự: '.$manga['name'], #edit_lang,
			'view' => 'manga_management.team_partner',
			'data' => compact(
				'success',
				'error',
				'manga',
				'insertHiddenToken',
				'count',
				'lst_items',
				'pagination'
			)
		];
	}

	private function sort_chapter($id) {
		$success = null;
		$error = null;

		$manga = Manga::get(['id' => $id]);
		if(!$manga || !self::is_own_manga($manga))
		{
			return ServerErrorHandler::error_404();
		}


		if (Security::validate() == true) {
			$ids = array_reverse(Request::post(self::INPUT_ID, []));
			$index = 1;
			foreach($ids as $chapter_id) {
				Chapter::update([
					'id' => $chapter_id,
					'manga_id' => $manga['id']
				], ['index' => $index++]);
			}
			$success = 'Cập nhật vị trí thành công'; #edit_lang
		}

		$chapters = Chapter::join([
			'LEFT JOIN <core_users> ON <{table}.user_upload> = <core_users.id>'
		])::select([
			'id',
			'name',
			'created_at',
			'user_upload',
			'index',
			'<core_users.name> AS <uploader_name>',
			'<core_users.username> AS <uploader_username>',
			'<core_users.avatar> AS <uploader_avatar>',
			'<core_users.user_ban> AS <uploader_ban_id>',
			'(SELECT <color> FROM <core_roles> WHERE <id> = <core_users.role_id>) AS <uploader_role_color>'
		])::list([
			'manga_id' => $manga['id']
		]);

		$insertHiddenToken = Security::insertHiddenToken();
		return [
			'title' => 'Sắp xếp thứ tự chương: '.$manga['name'], #edit_lang,
			'view' => 'manga_management.sort_chapter',
			'data' => compact(
				'success',
				'error',
				'manga',
				'insertHiddenToken',
				'chapters'
			)
		];
	}


	private function tool_leech($id) {
		$success = null;
		$error = null;

		$manga = Manga::get(['id' => $id]);
		if(!$manga || !UserPermission::has('tool_leech'))
		{
			return ServerErrorHandler::error_404();
		}

		$insertHiddenToken = Security::insertHiddenToken();
		return [
			'title' => 'Tool leech: '.$manga['name'], #edit_lang,
			'view' => 'manga_management.tool_leech',
			'data' => compact(
				'success',
				'error',
				'manga',
				'insertHiddenToken'
			)
		];
	}

}




?>