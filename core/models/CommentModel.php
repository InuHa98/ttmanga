<?php


class Comment extends Model {

	public static $table = 'core_comments';
	protected static $primary_key = 'id';
	protected static $timestamps = true;
	protected static $default_join = [
		'LEFT JOIN <core_users> AS <core_user> ON <{table}.user_id> = <core_user.id>',
		'LEFT JOIN <core_roles> AS <core_role> ON <core_user.role_id> = <core_role.id>',
		'LEFT JOIN <core_chapters> AS <core_chapter> ON <{table}.chapter_id> = <core_chapter.id>',
		'LEFT JOIN <core_mangas> AS <core_manga> ON <{table}.manga_id> = <core_manga.id>',
	];
	protected static $default_selects = [
		'<{table}.*>',
		'(SELECT COUNT(*) FROM <{table}> AS <table_reply> WHERE <table_reply.refid> = <{table}.id>) AS <reply>',
		'<core_user.name> AS <user_name>',
		'<core_user.username> AS <user_username>',
		'<core_user.avatar> AS <user_avatar>',
		'<core_user.user_ban> AS <user_ban>',
		'<core_role.color> AS <user_role_color>',
		'<core_chapter.name> AS <chapter_name>',
		'<core_manga.name> AS <manga_name>'
	];
	protected static $order_by = [
		'created_at' => 'DESC'
	];

	public const MAX_ITEM_COMMENTS = 10;

	public static function create($data = [])
	{
		if(!isset($data['text']))
		{
			return false;
		}

		$data = array_merge([
			'refid' => 0,
			'manga_id' => 0,
			'chapter_id' => 0,
			'user_id' => Auth::$id,
			'text' => ''
		], $data);

		if(parent::insert($data) == true)
		{
			return true;
		}

		return false;
	}

	public static function list_comments($page = 1, $data = [], $is_reply = false)
	{
		$result = [
			'total' => 0,
			'pagination' => null,
			'items' => []
		];

		$isReverse = isset($data['reverse']) ? $data['reverse'] : false;
		$comment_id = isset($data['comment_id']) ? $data['comment_id'] : null;
		
		$where = [
			'refid' => isset($data['refid']) ? $data['refid'] : 0,
			'manga_id' => isset($data['manga_id']) ? $data['manga_id'] : 0
		];

		if(isset($data['chapter_id']) && $data['chapter_id'] != "")
		{
			$where['chapter_id'] = $data['chapter_id'];
		}

		$count = parent::count($where);

		new Pagination($count, self::MAX_ITEM_COMMENTS, $page);
		$pagination = Pagination::get();

		$result['total'] = $count;
		$result['pagination'] = $pagination;

		
		if($count < 1)
		{
			return $result;
		}

		$where_all = $where;
		if(isset($where_all['refid']))
		{
			unset($where_all['refid']);
		}
		$result['all_comment'] = Comment::count($where_all);

		if($is_reply && !$isReverse)
		{
			$where['ORDER'] = [
				'created_at' => 'ASC'
			];
		}

		if($comment_id) {
			if(!isset($where['ORDER'])) {
				$where['ORDER'] = [];
			}
			$where['ORDER']['id'] = [
				$comment_id
			];
			$where['ORDER']['created_at'] = 'DESC';
		}
	
		$items = parent::list(array_merge($where, [
			"LIMIT" => [
				$pagination['start'], $pagination['limit']
			]
		]));

		if($is_reply && $isReverse)
		{
			$items = array_reverse($items);
		}


		foreach($items as $item) {
			$user = [
				'id' => $item['user_id'],
				'name' => $item['user_name'],
				'username' => $item['user_username'],
				'avatar' => $item['user_avatar'],
				'user_ban' => $item['user_ban'],
				'role_color' => $item['user_role_color']
			];
			
			$first_name = User::get_first_charname($user);
			$data_item = [
				'id' => $item['id'],
				'refid' => $item['refid'],
				'profile' => RouteMap::get('profile', ['id' => $item['user_id']]),
				'avatar' => User::get_avatar($user),
				'first_name' => $first_name,
				'bg_avatar' => User::get_color_avatar($first_name),
				'color' => $item['user_role_color'],
				'user_id' => $user['id'],
				'username' => User::get_display_name($user),
				'username_raw' => _echo($user['name'] ? $user['name'] : $user['username']),
				'text' => _echo($item['text'], true, true),
				'chapter' => null,
				'reply' => $item['reply'],
				'time' => _time($item['created_at']),
				'edit' => _time($item['updated_at']),
				'is_reply' => (Auth::$isLogin && $item['refid'] == 0),
				'is_own' => (Auth::$isLogin && $user['id'] == Auth::$data['id']),
				'is_edit' => false,
				'is_delete' => false,
				'reason_delete' => false
			];

			$chapter = Chapter::get($item['chapter_id']);
			if($chapter)
			{
				$data_item['chapter'] = [
					'id' => $chapter['id'],
					'name' => _echo($chapter['name']),
					'link' => RouteMap::get('chapter', ['id_manga' => $chapter['manga_id'], 'id_chapter' => $chapter['id']])
				];
			}

			if($user['id'] == Auth::$id)
			{
				$data_item['is_edit'] = true;
				$data_item['is_delete'] = true;
			}
			else if(UserPermission::has('admin_delete_comment')) {
				$data_item['is_delete'] = true;
				$data_item['reason_delete'] = true;
			}


			$result['items'][] = $data_item;
		}

		

		return $result;
	}

	protected static function onBeforeInsert($data = null)
	{

	}

	protected static function onSuccessInsert($insert_id = null)
	{

	}

	protected static function onErrorInsert()
	{

	}

	protected static function onBeforeUpdate($data = null, $where = null)
	{

	}

	protected static function onSuccessUpdate($count_items = 0)
	{

	}

	protected static function onErrorUpdate()
	{

	}

	protected static function onBeforeDelete($where = null)
	{

	}

	protected static function onSuccessDelete($count_items = 0)
	{

	}

	protected static function onErrorDelete()
	{

	}
}





?>