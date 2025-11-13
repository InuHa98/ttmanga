<?php


class teamController {

	const COOKIE_ONLY_SHOW_MY_UPLOADER = '_osmu';
	const COOKIE_ONLY_SHOW_REPORT_PENDING = '_osrp';

	const BLOCK_CREATE_TEAM = 'Create-team';
	const BLOCK_LIST_MANGA = 'Manga';
	const BLOCK_LIST_MEMBER = 'Members';
	const BLOCK_REPORT = 'Report';
	const BLOCK_SETTING = 'Settings';

	const ACTION_ADD = 'Add';
	const ACTION_EDIT = 'Edit';
	const ACTION_DELETE = 'Delete';
	const ACTION_JOIN = 'Join';
	const ACTION_CANCEL_JOIN = 'Cancel-Join';
	const ACTION_UPLOAD_IMAGE = 'upload_image';
	const ACTION_APPROVAL_MEMBER = 'approval_member';
	const ACTION_REJECT_MEMBER = 'reject_member';
	const ACTION_REMOVE_MEMBER = 'remove_member';
	const ACTION_MAKE_SUCCESS = 'success';
	const ACTION_MAKE_REJECT = 'reject';
	const ACTION_MAKE_PENDING = 'pending';
	
	public const INPUT_ACTION = '_action';
	public const INPUT_UPLOADER = 'uploader';
	public const INPUT_ID = 'id';
	public const INPUT_TEAM = 'team';
	public const INPUT_NOTE = 'note';
	public const INPUT_NAME = 'name';
	public const INPUT_EMAIL = 'email';
	public const INPUT_USERNAME = 'username';
	public const INPUT_REASON = 'reason';
	public const INPUT_DATA_IMAGE = 'data_image';
    public const INPUT_FACEBOOK = 'i_facebook';
    public const INPUT_DESC = 'i_desc';
    public const INPUT_RULE = 'i_rule';


	public static $team = null;
	protected static $own = null;
	protected static $is_own = false;

	public function index($block = null, $action = null)
	{
		$success = null;
		$error = null;

		if (!Auth::$data) {
			return ServerErrorHandler::error_404();
		}

		$team = Team::get(Auth::$data['team_id']);

		if(!$team) {
			return self::no_team($block, $action);
		}

		if ($team['user_ban'] != Team::IS_NOT_BAN) {
			$user_ban = User::get(['id' => $team['user_ban']]);
			return View::render_theme('team.banned', [
				'title' => 'Nhóm dịch của tôi', #edit_lang
				'team' => $team,
				'user_ban' => $user_ban
			]); 
		}

		$is_own = $team['own_id'] == Auth::$data['id'];

		$notification_request_join = RequestJoinTeam::count([
			'team_id' => $team['id']
		]);

		$where_notification_report = [
			'status' => Report::STATUS_PENDING,
			'[RAW] EXISTS (SELECT 1 FROM <'.TeamManga::$table.'> WHERE <'.TeamManga::$table.'.manga_id> = <{table}.manga_id> AND <'.TeamManga::$table.'.team_id> = :team_id)' => [
				'team_id' => $team['id']
			]
		];

		if (!$is_own) {
			$where_notification_report['OR'] = [
				'core_mangas.user_upload' => Auth::$data['id'],
				'core_chapters.user_upload' => Auth::$data['id']
			];
		}

		$notification_report = Report::count($where_notification_report);


		$form_action = Request::post(self::INPUT_ACTION, null);
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
				Alert::push(AvatarCover::upload_avatar_cover_team(Request::post(self::INPUT_DATA_IMAGE, null), $type));
				return Router::redirect('*', current_url());
		}


		$own = [
			'id' => $team['own_id'],
			'name' => $team['own_name'],
			'username' => $team['own_username'],
			'avatar' => $team['own_avatar'],
			'user_ban' => $team['own_user_ban'],
			'role_color' => $team['own_role_color']
		];

		self::$team = $team;
		self::$own = $own;
		self::$is_own = $is_own;

		$block_view = null;

		switch($block) {

			case self::BLOCK_LIST_MEMBER:
				$block_view = self::block_list_member($action);
				break;

			case self::BLOCK_REPORT:
				$block_view = self::block_report($action);
				break;

			case self::BLOCK_SETTING:
				$block_view = self::block_setting($action);
				break;

			case self::BLOCK_LIST_MANGA:
			default:
				$block = self::BLOCK_LIST_MANGA;
				$block_view = self::block_list_manga($action);
				break;
		}

		return View::render_theme('team.index', compact(
			'success',
			'error',
			'team',
			'notification_request_join',
			'notification_report',
			'is_own',
			'own',
			'block',
			'block_view'
		));
	}

	private static function no_team($block = null, $action = null) {

		$request_create_team = Team::get([
			'own_id' => Auth::$data['id'],
			'active' => Team::IS_NOT_ACTIVE
		]);

		$request_join_team = RequestJoinTeam::get([
			'user_id' => Auth::$data['id']
		]);

		switch($block) {

			case self::BLOCK_CREATE_TEAM:
				$title = 'Đăng kí nhóm dịch mới'; #edit_lang
				$success = null;
				$error = null;

				$name = trim(Request::post(self::INPUT_NAME, ''));
				$note = trim(Request::post(self::INPUT_NOTE, ''));

				if(!$request_join_team && Security::validate() == true) {
					if($name == '') {
						$error = 'Tên nhóm không được bỏ trống'; #edit_lang
					}
					else if(Team::has([
						'name[~]' => $name
					])) {
						$error = 'Tên nhóm đã tồn tại';
					}
					else if(Team::has([
						'own_id' => Auth::$data['id']
					])) {
						$error = 'Không thể gửi nhiều yêu cầu tạo nhóm cùng lúc';
					}
					else {
						if(Team::create($name, $note)) {
							$success = 'Gửi yêu cầu đăng ký nhóm dịch thành công. Vui lòng chờ quản trị viên chấp thuận.'; #edit_lang
						} else {
							$error = lang('system', 'default_error');
						}
					}
				}

				return View::render_theme('team.create_team', compact(
					'title',
					'success',
					'error',
					'name',
					'note',
					'request_join_team',
					'request_create_team'
				));


			default:
				$title = 'Tham gia nhóm dịch'; #edit_lang
			
				$success = null;
				$error = null;
		
				if(Security::validate() == true) {
		
					$action_form = trim(Request::post(self::INPUT_ACTION, ''));
					$id = intval(Request::post(InterFaceRequest::ID, null));
		
					switch($action_form) {
						case self::ACTION_JOIN:
							
							$note = trim(Request::post(self::INPUT_NOTE, ''));
		
							$team = Team::get([
								'active' => Team::IS_ACTIVE,
								'user_ban' => Team::IS_NOT_BAN,
								'id' => $id
							]);
		
							if(!$team) {
								$error = 'Không tìm thấy nhóm dịch muốn tham gia.'; #edit_lang
							}
							else if(Team::has([
								'own_id' => Auth::$data['id']
							])) {
								$error = 'Không thể tham gia nhóm khi đang gửi yêu cầu tạo nhóm';
							}
							else if(RequestJoinTeam::has([
								'user_id' => Auth::$data['id']
							])) {
								$error = 'Không thể yêu cầu tham gia nhiều nhóm dịch một lúc.'; #edit_lang
							}
							else if(Auth::$data['team_id']) {
								$error = 'Không thể tham gia 2 nhóm dịch cùng lúc.'; #edit_lang
							} else {
								if(RequestJoinTeam::create($team['id'], $note)) {
									Notification::create([
										'user_id' => $team['own_id'],
										'from_user_id' => Auth::$data['id'],
										'type' => Notification::TYPE_REQUEST_JOIN_TEAM,
										'data' => [
											'user_id' => Auth::$data['id'],
											'note' => $note
										]
									]);
									$success = 'Gửi yêu cầu tham gia thành công nhóm dịch: <b>'._echo($team['name']).'</b>';
								} else {
									$error = lang('system', 'default_error');
								}
							}
							break;
		
						case self::ACTION_CANCEL_JOIN:
						
							$request_join = RequestJoinTeam::get([
								'user_id' => Auth::$data['id'],
								'id' => $id
							]);
		
							if(!$request_join) {
								$error = 'Không tìm thấy yêu cầu tham gia nhóm dịch.'; #edit_lang
							} else {
								if(RequestJoinTeam::delete($request_join['id'])) {
									$success = 'Huỷ yêu cầu tham gia nhóm dịch thành công.';
								} else {
									$error = lang('system', 'default_error');
								}
							}
							break;
					}			
				}

				$where = [
					'active' => Team::IS_ACTIVE,
					'user_ban' => Team::IS_NOT_BAN
				];
		
				if($request_join_team) {
					$where['ORDER'] = [
						'id' => [$request_join_team['team_id']],
						'name' => 'ASC'
					];
				}
		
				$keyword = trim(Request::get(InterFaceRequest::KEYWORD, ''));

				if (!empty($keyword)) {
					$where['name[~]'] = '%'.$keyword.'%';
				}

				$count = Team::count($where);
				new Pagination($count, App::$pagination_limit);
				$pagination = Pagination::get();
				$team_list = Team::list(array_merge($where, [
					'LIMIT' => [
						$pagination['start'], $pagination['limit']
					]
				]));
		
				return View::render_theme('team.join_team', compact(
					'title',
					'success',
					'error',
					'keyword',
					'request_join_team',
					'request_create_team',
					'team_list',
					'pagination'
				));
		}
	}


    private static function block_list_manga($action = null) {

        $only_show_my_uploader = Request::cookie(self::COOKIE_ONLY_SHOW_MY_UPLOADER, null);

        $where = [
			'is_trash' => Manga::IS_ACTIVE,
            '[RAW] EXISTS (SELECT 1 FROM <core_team_mangas> WHERE <core_team_mangas.manga_id> = <{table}.id> AND <core_team_mangas.team_id> = :team_id)' => [
                'team_id' => self::$team['id']
            ]
		];

        if ($only_show_my_uploader == 'true') {
            $where['user_upload'] = Auth::$data['id'];
        }

		$keyword = trim(Request::get(InterFaceRequest::KEYWORD, ''));
		$type = trim(Request::get(InterFaceRequest::TYPE, ''));

		if($keyword != '') {
			switch($type) {
				case self::INPUT_TEAM:
					$team = Team::select(['id'])::list(['name[~]' => '%'.$keyword.'%']);
					$teamManga = TeamManga::select(['manga_id'])::list(['team_id[!]' => self::$team['id'], 'team_id' => $team ? array_column($team, 'id') : 0]);
					$where['id'] = $teamManga ? array_column($teamManga, 'manga_id') : 0;
					break;
				case self::INPUT_UPLOADER:
                    if ($only_show_my_uploader != 'true') {
                        $where['OR #uploader'] = [
                            'core_user_upload.name[~]' => '%'.$keyword.'%',
                            'core_user_upload.username[~]' => '%'.$keyword.'%'
                        ];
                    }
					break;
				default:
					$type = self::INPUT_NAME;
					$where['OR #keyword'] = [
						'name[~]' => '%'.$keyword.'%'
					];
					$otherName = MangaOtherName::list(['name[~]' => '%'.$keyword.'%']);
					if ($otherName) {
						$where['OR #keyword']['OR'] = [];
						$i = 0;
						foreach($otherName as $o) {
							$where['OR #keyword']['OR']['[RAW] FIND_IN_SET(:id'.($i).', <{table}.name_other_ids>)'] = ['id'.$i++ => $o['id']];
						}
					}
					break;
			}
		}

		$count = Manga::count($where);
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();
		$lst_manga = Manga::join([
			'LEFT JOIN <core_users> AS <core_user_upload> ON <{table}.user_upload> = <core_user_upload.id>',
			'LEFT JOIN <core_roles> AS <role_user_upload> ON <core_user_upload.role_id> = <role_user_upload.id>',
		], false)::select([
            '<core_user_upload.name> AS <uploader_name>',
			'<core_user_upload.username> AS <uploader_username>',
			'<core_user_upload.avatar> AS <uploader_avatar>',
			'<core_user_upload.user_ban> AS <uploader_user_ban>',
			'<role_user_upload.color> AS <uploader_role_color>',
		], false)::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));

		return [
			'title' => 'Nhóm dịch của tôi', #edit_lang,
			'view' => 'team.block.list_manga',
			'data' => compact(
                'only_show_my_uploader',
                'keyword',
                'type',
				'count',
				'lst_manga',
				'pagination'
			)
		];
	}

	private static function block_list_member($action = null) {
		$success = null;
		$error = null;

		$team = self::$team;
		$is_own = $team['own_id'] === Auth::$data['id'];

		$requests_join = RequestJoinTeam::list(['team_id' => $team['id']]);
		$requests_join_ids = array_map(function($o) {
			return $o['user_id'];
		}, $requests_join);

		$where = [
			'OR' => [
				'team_id' => $team['id'],
				'id' => $requests_join_ids
			],
			'ORDER' => [
				'name' => 'ASC',
				'username' => 'ASC',
			],
		];

		if ($requests_join_ids) {
			$where['ORDER'] = ['id' => $requests_join_ids] + $where['ORDER'];
		}

		$keyword = trim(Request::get(InterFaceRequest::KEYWORD, ''));
		$type = trim(Request::get(InterFaceRequest::TYPE, ''));

		if($keyword != '') {
			switch($type) {
				case self::INPUT_EMAIL:
					$where['email[~]'] = '%'.$keyword.'%';
					break;
				default:
					$type = self::INPUT_USERNAME;
					$where['OR #name'] = [
						'name[~]' => '%'.$keyword.'%',
						'username[~]' => '%'.$keyword.'%'
					];
					break;
			}
		}


		if(Security::validate() == true) {
			$action = trim(Request::post(self::INPUT_ACTION, ''));
			$id = intval(Request::post(self::INPUT_ID, 0));

			$user = User::get(['id' => $id]);

			if(!$user) {
				$error = 'Không tìm thấy thành viên'; #edit_lang
			} else {
				if($is_own && $user['id'] != Auth::$data['id']) {
					switch($action) {
						case self::ACTION_APPROVAL_MEMBER:
							$request = RequestJoinTeam::get(['team_id' => $team['id'], 'user_id' => $user['id']]);
							if (!$request || !empty($user['team_id'])) {
								$error = 'Yêu cầu gia nhập nhóm không tồn tại'; #edit_lang
							} else {
								if(User::update($user['id'], [
									'team_id' => $team['id']
								])) {
									RequestJoinTeam::delete($request['id']);
									Notification::create([
										'user_id' => $user['id'],
										'from_user_id' => Auth::$data['id'],
										'type' => Notification::TYPE_APPROVAL_MEMBER,
										'data' => [
											'team_id' => $team['id']
										]
									]);
									$success = 'Chấp thuận yêu cầu gia nhập nhóm thành công'; #edit_lang
								} else {
									$error = lang('system', 'default_error');
								}
							}
							break;

						case self::ACTION_REJECT_MEMBER:
							$request = RequestJoinTeam::get(['team_id' => $team['id'], 'user_id' => $user['id']]);
							if (!$request || !empty($user['team_id'])) {
								$error = 'Yêu cầu gia nhập nhóm không tồn tại'; #edit_lang
							} else {
								$reason = trim(Request::post(self::INPUT_REASON, ''));
								if(RequestJoinTeam::delete($request['id'])) {
									User::update($user['id'], [
										'team_id' => null
									]);
									Notification::create([
										'user_id' => $user['id'],
										'from_user_id' => Auth::$data['id'],
										'type' => Notification::TYPE_REJECT_MEMBER,
										'data' => [
											'team_id' => $team['id'],
											'reason' => $reason
										]
									]);
									$success = 'Từ chối yêu cầu gia nhập nhóm thành công'; #edit_lang
								} else {
									$error = lang('system', 'default_error');
								}
							}
							break;

						case self::ACTION_REMOVE_MEMBER:
							if ($user['team_id'] != $team['id']) {
								$error = 'Thành viên không có trong nhóm của bạn'; #edit_lang
							} else {
								$reason = trim(Request::post(self::INPUT_REASON, ''));
								if(User::update($user['id'], [
									'team_id' => null
								])) {
									Notification::create([
										'user_id' => $user['id'],
										'from_user_id' => Auth::$data['id'],
										'type' => Notification::TYPE_REMOVE_MEMBER,
										'data' => [
											'team_id' => $team['id'],
											'reason' => $reason
										]
									]);
									$success = 'Từ chối yêu cầu gia nhập nhóm thành công'; #edit_lang
								} else {
									$error = lang('system', 'default_error');
								}
							}
							break;
					}		
				}
			}
		}

		$count = User::count($where);
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();
		$user_list = User::select([
			'id',
			'username',
			'avatar',
			'email',
			'role_id',
			'perms',
			'user_ban',
			'team_id',
			'<core_roles.name> AS <role_name>', 
			'<core_roles.color> AS <role_color>', 
			'<core_roles.perms> AS <role_perms>', 
			'<core_roles.level> AS <role_level>'
		])::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));

		$insertHiddenToken = Security::insertHiddenToken();

		return [
			'title' => 'Thành viên nhóm dịch', #edit_lang,
			'view' => 'team.block.list_member',
			'data' => compact(
				'success',
				'error',
				'team',
				'is_own',
				'keyword',
				'type',
				'insertHiddenToken',
				'count',
				'user_list',
				'pagination'
			)
		];
	}

	private static function block_report($action = null) {

		$success = null;
		$error = null;

		$only_show_report_pending = Request::cookie(self::COOKIE_ONLY_SHOW_REPORT_PENDING, null);

		$where = [
			'manga_id[>]' => 0,
			'OR' => [
				'core_mangas.user_upload' => Auth::$data['id'],
				'core_chapters.user_upload' => Auth::$data['id']
			]
		];

		if (self::$team['own_id'] === Auth::$data['id']) {
			unset($where['OR']);
		}

        if ($only_show_report_pending == 'true') {
            $where['status'] = Report::STATUS_PENDING;
        }

		if(Security::validate() == true) {
			$action = trim(Request::post(self::INPUT_ACTION, ''));
			$id = intval(Request::post(self::INPUT_ID, 0));

			$where_report = [
				'id' => $id,
				'OR' => [
					'core_mangas.user_upload' => Auth::$data['id'],
					'core_chapters.user_upload' => Auth::$data['id']
				]
			];

			if (self::$team['own_id'] === Auth::$data['id']) {
				unset($where_report['OR']);
			}

			$report = Report::get($where_report);

			if(!$report) {
				$error = 'Không tìm thấy báo lỗi'; #edit_lang
			} else {
				switch($action) {
					case self::ACTION_MAKE_SUCCESS:
						if(Report::update($report['id'], [
							'status' => Report::STATUS_COMPLETE,
							'user_update' => Auth::$data['id']
						])) {
							$success = 'Đánh dấu đã xử lý báo lỗi thành công'; #edit_lang
						} else {
							$error = lang('system', 'default_error');
						}
						break;

					case self::ACTION_MAKE_REJECT:
						if(Report::update($report['id'], [
							'status' => Report::STATUS_REJECT,
							'user_update' => Auth::$data['id']
						])) {
							$success = 'Đánh dấu đây không phải là lỗi thành công'; #edit_lang
						} else {
							$error = lang('system', 'default_error');
						}
						break;

					case self::ACTION_MAKE_PENDING:
						if(Report::update($report['id'], [
							'status' => Report::STATUS_PENDING,
							'user_update' => Auth::$data['id']
						])) {
							$success = 'Đánh dấu đang chờ xử lý báo lỗi thành công'; #edit_lang
						} else {
							$error = lang('system', 'default_error');
						}
						break;
				}
			}
		}

		$count = Report::count($where);
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();
		$lst_report = Report::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));

		$insertHiddenToken = Security::insertHiddenToken();

		return [
			'title' => 'Báo lỗi truyện', #edit_lang,
			'view' => 'team.block.report',
			'data' => compact(
				'success',
				'error',
				'only_show_report_pending',
				'insertHiddenToken',
				'count',
				'lst_report',
				'pagination'
			)
		];
	}

	private static function block_setting($action = null) {

        $error = null;
        $success = null;

        $team = self::$team;

		if ($team['own_id'] != Auth::$data['id']) {
			return ServerErrorHandler::error_404();
		}

        $facebook = trim(Request::post(self::INPUT_FACEBOOK, $team['facebook']));
        $desc = trim(Request::post(self::INPUT_DESC, $team['desc']));
        $rule = trim(Request::post(self::INPUT_RULE, $team['rule']));

        if(Security::validate() == true)
        {
            if (Team::update($team['id'], compact('facebook', 'desc', 'rule')))
            {
                $success = lang('system', 'success_save');
            } else {
                $error = lang('system', 'default_error');
            }
        }

        $insertHiddenToken = Security::insertHiddenToken();
		return [
			'title' => 'Tuỳ chỉnh nhóm dịch', #edit_lang,
			'view' => 'team.block.settings',
			'data' => compact('error', 'success', 'insertHiddenToken', 'facebook', 'desc', 'rule')
		];
	}

}





?>