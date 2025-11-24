<?php


class mangaController {

	public const SORT_ALPHABET = 'alphabet';
	public const SORT_UPDATE = 'update';
	public const SORT_NEW = 'new';
	public const SORT_VIEW = 'view';
	public const SORT_FOLLOW = 'follow';

	public const STATUS_ALL = 'all';
	public const STATUS_ONGOING = 'ongoing';
	public const STATUS_COMPLETE = 'complete';
	public const STATUS_DROP = 'drop';

	public const PARAM_SORT = 'sort';
	public const PARAM_STATUS = 'status';
	public const PARAM_CHARACTER = 'character';
	public const PARAM_GENRES = 'genres';
	public const PARAM_TYPE_SEARCH = 'type';

	public const CHARACTER_ALL = 'all';
	public const CHARACTER_SPECIAL = 'special';

	public const INPUT_KEYWORD = 'keyword';
	public const INPUT_AUTHOR = 'author';
	public const INPUT_TEAM = 'team';
	public const INPUT_GENRES = 'genres';
	public const INPUT_STATUS = 'status';


	public static function encodeImageURL($link, $fake_time = 0){
		if (!env(DotEnv::ENCODE_URL_IMAGE)) {
			return $link;
		}
		
		$config_user_exclude = env('ID_USER_EXCLUDE_ENCODE_URL_IMAGE', '');
		$backlist = !empty($config_user_exclude) ? array_filter(explode(',', $config_user_exclude)) : [];

		if(Auth::$isLogin && in_array(Auth::$data['id'], $backlist)) {
			return $link;
		}

		if (isUrlGoogleImage($link)) {
			$time = time() - $fake_time;
			$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
			$currentUrl .= "://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$currentUrl = preg_replace("#^(.*)/([0-9]+)\?(.*?)$#","$1/$2", $currentUrl);
			$hash = md5($currentUrl.'---'.$time);
			return APP_URL.'/image/'.$hash.'/'.$time.'/'.hashLink($link).'.jpg';
		}
		return $link;
	}

	public function list()
	{
		$sort = trim(Request::get(self::PARAM_SORT, self::SORT_ALPHABET));
		$status = trim(Request::get(self::PARAM_STATUS, self::STATUS_ALL));
		$character = strtolower(trim(Request::get(self::PARAM_CHARACTER, self::CHARACTER_ALL)));
		$genres = intval(Request::get(self::PARAM_GENRES, null));

		$current_genres = Genres::get($genres);

		$title = 'Danh sách truyện tranh - '.env(DotEnv::APP_NAME); #edit_lang

		$join = [];
		$where = [];
		switch($sort) {

			case self::SORT_UPDATE:
				$where['ORDER'] = [
					Chapter::getTableName().'.created_at' => 'DESC'
				];
				break;

			case self::SORT_NEW:
				$where['ORDER'] = [
					'created_at' => 'DESC'
				];
				break;

			case self::SORT_VIEW:
				$where['ORDER'] = [
					'view' => 'DESC'
				];
				break;

			case self::SORT_FOLLOW:
				$where['ORDER'] = [
					'follow' => 'DESC'
				];
				break;

			case self::SORT_ALPHABET:
			default:
				$where['ORDER'] = [
					'name' => 'ASC'
				];
				break;
		}

		switch($status) {
			case self::STATUS_ONGOING:
				$where['status'] = Manga::STATUS_ONGOING;
				break;
			case self::STATUS_COMPLETE:
				$where['status'] = Manga::STATUS_COMPLETE;
				break;
			case self::STATUS_DROP:
				$where['status'] = Manga::STATUS_DROP;
				break;
		}

		if($character == self::CHARACTER_SPECIAL)
		{
			$where['[RAW] <{table}.name> RLIKE :rlike'] = [
				'rlike' => '^([^a-zA-Z])'
			];
		}
		else if(in_array($character, range('a', 'z')))
		{
			$where['name[~]'] = $character.'%';
		}

		if($current_genres)
		{
			$where['[RAW] FIND_IN_SET(:genres_id, <{table}.genres_id>)'] = [
				'genres_id' => $current_genres['id']
			];
		}

		$count = Manga::count($where);
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();
		$manga_items = Manga::join($join, false)::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));
		$view_mode = App::view_mode();



		return View::render_theme('manga.list', compact('title', 'sort', 'status', 'character', 'current_genres', 'count', 'manga_items', 'view_mode', 'pagination'));
	}

	public function search()
	{
		$count_filter = 0;
		$title = 'Tìm kiếm nâng cao - '.env(DotEnv::APP_NAME); #edit_lang

		$keyword = trim(Request::post(self::INPUT_KEYWORD, Request::get(self::INPUT_KEYWORD, '')));
		$author_name = trim(Request::post(self::INPUT_AUTHOR, Request::get(self::INPUT_AUTHOR, '')));
		$team_name = trim(Request::post(self::INPUT_TEAM, Request::get(self::INPUT_TEAM, '')));
		$genres = Request::post(self::INPUT_GENRES, []);
		$status = trim(Request::post(self::INPUT_STATUS, self::STATUS_ALL));

		$genres = array_filter($genres, function($v, $k) {
			return $v != 0;
		}, ARRAY_FILTER_USE_BOTH);
		
		if (isset($_POST['reset'])) {
			$keyword = null;
			$author_name = null;
			$team_name = null;
			$genres = [];
			$status = self::STATUS_ALL;
		}
		
		$where = [
			'ORDER' => [
				'name' => 'ASC'
			]
		];

		if ($keyword) {
			$count_filter++;
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
		}

		if ($author_name) {
			$count_filter++;
			$mangaAuth = MangaAuthor::list(['name[~]' => '%'.$author_name.'%']);
			if ($mangaAuth) {
				$where['OR #auth'] = [];
				$i = 0;
				foreach($mangaAuth as $o) {
					$where['OR #auth']['[RAW] FIND_IN_SET(:id'.($i).', <{table}.auth_ids>)'] = ['id'.$i++ => $o['id']];
				}
			}
		}

		if ($team_name) {
			$count_filter++;

			$lst_team = Team::join([])::select(['id'])::list(['name[~]' => '%'.$team_name.'%']);
			$mangaTeamName = MangaTeamName::list(['name[~]' => '%'.$team_name.'%']);
			
			$where['OR #team'] = [];
			if ($lst_team) {
				$i = 0;
				foreach($lst_team as $o) {
					$where['OR #team']['OR #id']['[RAW] FIND_IN_SET(:id'.($i).', <{table}.team_ids>)'] = ['id'.$i++ => $o['id']];
				}
			}
			if ($mangaTeamName) {
				$i = 0;
				foreach($mangaTeamName as $o) {
					$where['OR #team']['OR #name']['[RAW] FIND_IN_SET(:id'.($i).', <{table}.team_name_ids>)'] = ['id'.$i++ => $o['id']];
				}
			}
		}

		if(!empty($genres))
		{
			foreach($genres as $k => $v) {
				$count_filter++;
				if ($v == 1) {
					$where['[RAW] FIND_IN_SET(:id_genres'.$k.', <{table}.genres_id>)'] = [
						'id_genres'.$k => $k
					];
				}
				else if ($v == -1) {
					$where['[RAW] NOT (FIND_IN_SET(:id_genres'.$k.', <{table}.genres_id>))'] = [
						'id_genres'.$k => $k
					];
				}
			}
		}

		switch($status) {
			case self::STATUS_ONGOING:
				$where['status'] = Manga::STATUS_ONGOING;
				break;
			case self::STATUS_COMPLETE:
				$where['status'] = Manga::STATUS_COMPLETE;
				break;
			case self::STATUS_DROP:
				$where['status'] = Manga::STATUS_DROP;
				break;
		}

		if ($status != self::STATUS_ALL) {
			$count_filter++;
		}

		$count = $count_filter > 0 ? Manga::count($where) : 0;
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();
		$manga_items = $count > 0 ? Manga::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		])) : [];
		$view_mode = App::view_mode();

		$lst_genres = Genres::list();
		return View::render_theme('manga.search', compact('title', 'lst_genres', 'count_filter', 'keyword', 'author_name', 'team_name', 'genres', 'status', 'count', 'manga_items', 'view_mode', 'pagination'));
	}

	public function search_ajax() {
		$type = trim(Request::post(self::PARAM_TYPE_SEARCH, self::INPUT_KEYWORD));
		$keyword = trim(Request::post(self::INPUT_KEYWORD, ''));
		
		$response = [];
		if ($keyword != '') {
			switch($type) {

				case self::INPUT_AUTHOR:
					$where = [
						'name[~]' => '%'.$keyword.'%'
					];

					$author_items = MangaAuthor::list($where);
					foreach($author_items as $auth) {
						$response[] = [
							'name' => ucwords(strtolower(trim($auth['name']))),
							'url' => RouteMap::build_query([self::INPUT_AUTHOR => trim($auth['name'].'')], 'search_manga')
						];
					}
					break;

				case self::INPUT_TEAM:
					$query1 = [
						'table' => Team::$table,
						'columns' => ['name'],
						'where' => [
							'name[~]' => '%'.$keyword.'%'
						]
					];
					$query2 = [
						'table' => MangaTeamName::$table,
						'columns' => ['name'],
						'where' => [
							'name[~]' => '%'.$keyword.'%'
						]
					];

					$team_items = App::$database->union($query1, $query2);
					foreach($team_items as $team) {
						$response[] = [
							'name' => trim($team['name'].''),
							'url' => RouteMap::build_query([self::INPUT_TEAM => trim($team['name'].'')], 'search_manga')
						];
					} 
					break;

				default:
					$where = [
						'OR' => [
							'name[~]' => '%'.$keyword.'%',
						],
						'ORDER' => [
							'view' => 'DESC'
						]
					];
					$otherName = MangaOtherName::list(['name[~]' => '%'.$keyword.'%']);
					if ($otherName) {
						$where['OR']['OR'] = [];
						$i = 0;
						foreach($otherName as $o) {
							$where['OR']['OR']['[RAW] FIND_IN_SET(:id'.($i).', <{table}.name_other_ids>)'] = ['id'.$i++ => $o['id']];
						}
					}

					$manga_items = Manga::list($where);

					foreach($manga_items as $manga) {
						$response[] = [
							'id' => $manga['id'],
							'name' => trim($manga['name'].''),
							'name_other' => array_filter(explode("\n", trim($manga['name_other'].''))),
							'author' => trim($manga['auth'].''),
							'image' => trim($manga['image'].''),
							'last_chapter' => trim($manga['name_last_chapter'].''),
							'url' => RouteMap::get('manga', ['id' => $manga['id']])
						];
					} 
			}
		}
		return View::render_json($response);	
	}

	public function team($name)
	{
		$name = urldecode($name);
		$team = Team::get(['name[~]' => $name]);

		$own = $team ? [
			'id' => $team['own_id'],
			'name' => $team['own_name'],
			'username' => $team['own_username'],
			'avatar' => $team['own_avatar'],
			'user_ban' => $team['own_user_ban'],
			'role_color' => $team['own_role_color']
		] : [];

		if (!$team) {
			$team = ['name' => $name];
		}

		$title = 'Danh sách truyện của nhóm: '.$team['name'].' - '.env(DotEnv::APP_NAME); #edit_lang

		$join = [];
		$where = [
			'OR' => [],
			'ORDER' => [
				'name' => 'ASC'
			]
		];

		if (!empty($team['id'])) {
			$where['OR']['[RAW] FIND_IN_SET(:team_id, <{table}.team_ids>)'] = [
				'team_id' => $team['id']
			];
		}


		$mangaTeamName = MangaTeamName::list(['name[~]' => $team['name']]);
		if ($mangaTeamName) {
			$i = 0;
			foreach($mangaTeamName as $o) {
				$where['OR']['OR']['[RAW] FIND_IN_SET(:id'.($i).', <{table}.team_name_ids>)'] = ['id'.$i++ => $o['id']];
			}
		}


		$count = Manga::count($where);
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();
		$manga_items = Manga::join($join, false)::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));
		$view_mode = App::view_mode();

		return View::render_theme('manga.team', compact('title', 'team', 'own', 'count', 'manga_items', 'view_mode', 'pagination'));
	}

	public function manga($id)
	{
		Manga::update_view($id);
		$manga = Manga::get($id);

		if(!$manga)
		{
			return ServerErrorHandler::error_404();
		}

		if ($manga['type'] != Manga::TYPE_NOT_WARNING && !App::is_comfirm_warning($manga['id'])) {
			if (isset($_POST['submit'])) {
				App::comfirm_warning($manga['id']);
				return redirect_route('manga', ['id' => $manga['id']]);
			} else {
				return View::render_theme('manga.warning', [
					'title' => 'Cảnh báo - '.$manga['name'],
					'manga' => $manga
				]); 
			}
		}

		View::addData('_manga_id', $manga['id']);

		$title = $manga['name'].' - '.env(DotEnv::APP_NAME);

		$name_other = Manga::get_name_other($manga);
		$genres = Manga::list_genres($manga);
		$auths = Manga::get_auth($manga);
		$teams = Manga::get_team($manga);

		$status = Manga::get_status_name($manga);
		$links = json_decode($manga['links'], true);

		$views = $manga['view'];
		$hasFollow = Bookmark::hasFollow($manga['id']);
		$follows = Bookmark::count_follow_manga($manga['id']);

		$chapters = Chapter::select(['id', 'name', 'user_upload', 'created_at', 'download'])::list([
			'manga_id' => $manga['id']
		]);

		$uploader = [
			'creator' => User::get($manga['user_upload']),
			'uploader' => $chapters ? User::list([
				'id[!]' => $manga['user_upload'],
				'id' => array_column($chapters, 'user_upload'),
			]) : []
		];

		$chapters = Chapter::select(['id', 'name', 'created_at', 'download'])::list([
			'manga_id' => $manga['id']
		]);

		$other_teams = Manga::get_other_team($manga);

		$first_chapter = Chapter::get([
			'manga_id' => $manga['id'],
			'ORDER' => [
				'index' =>'ASC'
			],
			'LIMIT' => 1 
		]);

		$last_chapter = Chapter::get([
			'manga_id' => $manga['id'],
			'ORDER' => [
				'id' =>'DESC'
			],
			'LIMIT' => 1 
		]);

		$is_own = mangaManagementController::is_own_manga($manga);

		return View::render_theme('manga.info', compact('title', 'is_own', 'manga', 'name_other', 'genres', 'auths', 'teams', 'other_teams', 'status', 'links', 'views', 'follows', 'hasFollow', 'uploader', 'chapters', 'first_chapter', 'last_chapter'));
	}

	public function chapter($id_manga, $id_chapter)
	{
		Manga::update_view($id_manga);
		$manga = Manga::get($id_manga);
		$chapter = Chapter::get([
			'id' => $id_chapter,
			'manga_id' => isset($manga['id']) ? $manga['id'] : null
		]);

		if(!$chapter || !$manga)
		{
			return ServerErrorHandler::error_404();
		}

		if ($manga['type'] != Manga::TYPE_NOT_WARNING && !App::is_comfirm_warning($manga['id'])) {
			if (isset($_POST['submit'])) {
				App::comfirm_warning($manga['id']);
				return redirect_route('chapter', ['id_manga' => $manga['id'], 'id_chapter' => $chapter['id']]);
			} else {
				return View::render_theme('manga.warning', [
					'title' => 'Cảnh báo - '.$manga['name'],
					'manga' => $manga
				]); 
			}
		}

		View::addData('_chapter_id', $chapter['id']);
		History::add($manga, $chapter);
		Bookmark::hasRead($manga, $chapter);


		$title = $manga['name'].' - '.$chapter['name'].' | '.env(DotEnv::APP_NAME);

		$uploader = User::get($chapter['user_upload']);
		$images = trim($chapter['image'].'');
		$images = str_replace("\r", '', $images);
		$images = array_filter(explode(Chapter::SEPARATOR, $images ?? ''));

		$images = array_map(function($o) {
			return env(DotEnv::ENCODE_URL_IMAGE, false) ? self::encodeImageURL($o) : $o;
		}, $images);

		foreach ($images as $index => $o) {
			$images[$index] = env(DotEnv::ENCODE_URL_IMAGE, false) ? self::encodeImageURL($o, $index < 2 ? 0 : 600) : $o;
		}

		$team_name = Manga::get_team_name($manga);
		
		if(!$team_name)
		{
			$team_name = 'Không rõ'; #edit_lang
		}

		$other_teams = Manga::get_other_team($manga);


		$list_chapters = Chapter::list([
			'manga_id' => $manga['id']
		]);

		$next_chapter = Chapter::get([
			'manga_id' => $manga['id'],
			'index' => $chapter['index'] + 1
		]);

		$pre_chapter = Chapter::get([
			'manga_id' => $manga['id'],
			'index' => $chapter['index'] - 1
		]);

		$read_mode = App::read_mode();
		$padding_mode = App::padding_mode();

		$current_time = time();

		return View::render_theme('manga.chapter', compact('title', 'manga', 'chapter', 'uploader', 'images', 'team_name', 'other_teams', 'list_chapters', 'next_chapter', 'pre_chapter', 'read_mode', 'padding_mode', 'current_time'));

	}

}





?>