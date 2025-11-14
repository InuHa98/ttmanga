<?php


class Manga extends Model {

	public static $table = 'core_mangas';
	protected static $primary_key = 'id';
	protected static $timestamps = true;
	protected static $default_join = [
		'LEFT JOIN <core_chapters> ON <core_chapters.id> = <{table}.id_last_chapter>'
	];
	protected static $default_selects = [
		'<{table}.*>',
		'<core_chapters.id> AS <id_last_chapter>',
		'<core_chapters.name> AS <name_last_chapter>',
		'<core_chapters.created_at> AS <created_last_chapter>'
	];
	

	protected static $where = [
		'is_trash' => self::IS_ACTIVE
	];

	protected static $order_by = [
		'id' => 'DESC'
	];

	public static function get($id = null, $select = null){
		return self::build_info(parent::get($id, $select));
	}

	public static function list($where = [], $select = null)
	{
		return array_map(function($o) {
			return self::build_info($o);
		}, parent::list($where, $select));
	}

	public static function build_info($o) {
		if (!empty($o['team_ids'])) {
			$core_teams = Team::select(['GROUP_CONCAT(<{table}.name> SEPARATOR "'.self::SEPARATOR.'") AS <value>'])::get(['id' => array_filter(explode(',', $o['team_ids']))]);	
		}

		if (!empty($o['auth_ids'])) {
			$core_manga_authors = MangaAuthor::select(['GROUP_CONCAT(<{table}.name> SEPARATOR "'.self::SEPARATOR.'") AS <value>'])::get(['id' => array_filter(explode(',', $o['auth_ids']))]);
		}

		if (!empty($o['team_name_ids'])) {
			$core_manga_team_names = MangaTeamName::select(['GROUP_CONCAT(<{table}.name> SEPARATOR "'.self::SEPARATOR.'") AS <value>'])::get(['id' => array_filter(explode(',', $o['team_name_ids']))]);
		}

		if (!empty($o['name_other_ids'])) {
			$core_manga_other_names = MangaOtherName::select(['GROUP_CONCAT(<{table}.name> SEPARATOR "'.self::SEPARATOR.'") AS <value>'])::get(['id' => array_filter(explode(',', $o['name_other_ids']))]);
		}

		if ($o) {
			$o['team_name_from_id'] = $core_teams['value'] ?? '';	
			$o['auth'] = $core_manga_authors['value'] ?? '';
			$o['team_name'] = $core_manga_team_names['value'] ?? '';
			$o['name_other'] = $core_manga_other_names['value'] ?? '';	
		}

		return $o;
	}

    public const SEPARATOR = "\n";

    public const STATUS_ALL = -1;
    public const STATUS_ONGOING = 0;
    public const STATUS_COMPLETE = 1;
    public const STATUS_DROP = 2;

    public const TYPE_NOT_WARNING = 0;
    public const TYPE_WARNING_16 = 1;
    public const TYPE_WARNING_17 = 2;
    public const TYPE_WARNING_18 = 3;

    public const IS_ACTIVE = 0;
    public const IS_INACTIVE = 1;

	public static function create($data = []) {
		return self::modifed(null, $data);
	}

	public static function changeInfo($id, $data = []) {
		return self::modifed($id, $data);
	}

	public static function modifed($id, $data = [])
	{
		if(!isset($data['name'], $data['author'], $data['teams'], $data['genres'], $data['status'], $data['image'], $data['desc']))
		{
			return false;
		}

		$data = array_merge([
			'id_last_chapter' => 0,
			'type' => self::TYPE_NOT_WARNING,
			'cover' => '',
			'links' => []
		], $data);

		$lst_name_other = [];
		foreach($data['name_other'] as $o) {
			$o = str_replace(["\r", "\n"], '',  trim($o)); str_replace(["\r", "\n"], '',  trim($o));
			$isExist = MangaOtherName::get(['name[~]' => $o]); 
			if ($isExist) {
				$lst_name_other[] = $isExist['id'];
			} else {
				MangaOtherName::insert(['name' => $o]);
				$lst_name_other[] = MangaOtherName::$insert_id;
			}
		}

		$lst_authors = [];
		foreach($data['author'] as $o) {
			$o = str_replace(["\r", "\n"], '',  trim($o)); str_replace(["\r", "\n"], '',  trim($o));
			$isExist = MangaAuthor::get(['name[~]' => $o]); 
			if ($isExist) {
				$lst_authors[] = $isExist['id'];
			} else {
				MangaAuthor::insert(['name' => $o]);
				$lst_authors[] = MangaAuthor::$insert_id;
			}
		}

		$team = Team::get(['id' => Auth::$data['team_id']]);
		$team_ids = [];
		$team_name_ids = [];
		foreach($data['teams'] as $o) {
			$o = str_replace(["\r", "\n"], '',  trim($o)); str_replace(["\r", "\n"], '',  trim($o));
			if ($o == $team['name']) {
				$team_ids[] = $team['id'];
				continue;
			}

			$isExist = MangaTeamName::get(['name[~]' => $o]); 
			if ($isExist) {
				$team_name_ids[] = $isExist['id'];
			} else {
				MangaTeamName::insert(['name' => $o]);
				$team_name_ids[] = MangaTeamName::$insert_id;
			}
		}


		$genres_id = array_keys($data['genres']);

		$links = [];
		foreach($data['links'] as $o) {
			$o = str_replace(["\r", "\n"], '', trim($o ?? ''));
			$split = explode('|', $o ?? '');
			$url = trim($split[0] ?? '');
			$links[] = [
				'url' => $url,
				'text' => !empty($split[1]) ? trim($split[1]) : $url
			];
		}

		if ($id === null) {
			$data_create = [
				'id' => $data['id'] ?? null,
				'user_upload' => $data['user_upload'] ?? Auth::$data['id'],
				'team_ids' => implode(',', $team_ids),
				'team_name_ids' => implode(',', $team_name_ids),
				'name_other_ids' => implode(',', $lst_name_other),
				'auth_ids' => implode(',', $lst_authors),
				'genres_id' => implode(',', $genres_id),
				'name' => ucwords(mb_strtolower($data['name'])),
				'image' => $data['image'],
				'cover' => $data['cover'],
				'status' => $data['status'],
				'text' => $data['desc'],
				'links' => $links,
				'view' => $data['view'] ?? 0,
				'follow' => 0,
				'type' => $data['type'],
				'is_trash' => self::IS_ACTIVE,
				'trash_by' => 0
			];

			if (parent::has($data_create)) {
				return false;
			}

			if(parent::insert($data_create) > 0)
			{
				TeamManga::create([
					'team_id' => $team['id'],
					'manga_id' => static::$insert_id
				]);
				return true;
			}
		} else {
			$data_update = [
				'team_ids' => implode(',', $team_ids),
				'team_name_ids' => implode(',', $team_name_ids),
				'name_other_ids' => implode(',', $lst_name_other),
				'auth_ids' => implode(',', $lst_authors),
				'genres_id' => implode(',', $genres_id),
				'name' => $data['name'],
				'image' => $data['image'],
				'cover' => $data['cover'],
				'status' => $data['status'],
				'text' => $data['desc'],
				'links' => $links,
				'type' => $data['type']
			];

			if(parent::update($id, $data_update) > 0)
			{
				return true;
			}
		}
		return false;
	}

	public static function update_view($id_manga)
	{
		if($id_manga)
		{
			$timestamps = static::$timestamps;
			self::$timestamps = false;
			parent::update(['id' => $id_manga], ['view[+]' => 1]);
			self::$timestamps = $timestamps;
		}
	}

	public static function list_random()
	{
		return parent::select(['id', 'name', 'text', 'image'])::get([
			'ORDER' => 'RAND()',
			'LIMIT' => 1
		]);
	}

	public static function list_genres($id_manga)
	{
		$manga = is_array($id_manga) ? $id_manga : parent::get($id_manga);
		if(!$manga)
		{
			return null;
		}

		return Genres::list([
			'id' => explode(',', $manga['genres_id'])
		]);
	}

	public static function get_auth($id_manga, $join = null)
	{
		$manga = is_array($id_manga) ? $id_manga : parent::get($id_manga);
		if(!$manga)
		{
			return null;
		}

		$auth = array_filter(explode(self::SEPARATOR, $manga['auth'] ?? ''));
		if($join != '')
		{
			return implode($join, $auth);
		}
		return $auth;
	}

	public static function get_team($id_manga)
	{
		$manga = is_array($id_manga) ? $id_manga : parent::get($id_manga);
		if(!$manga)
		{
			return null;
		}

		$ids = array_filter(explode(',', $manga['team_ids'] ?? ''));
		$teams = $ids ? Team::select(['name'])::list([
			'id' => $ids
		]) : [];

		if($manga['team_name'])
		{
			$team_name = explode(self::SEPARATOR, $manga['team_name'] ?? '');
			foreach($team_name as $team)
			{
				$teams[] = [
					'name' => trim($team)
				];
			}
		}

		return $teams;
	}

	public static function get_other_team($id_manga)
	{
		$manga = is_array($id_manga) ? $id_manga : parent::get($id_manga);
		if(!$manga)
		{
			return null;
		}

		$name = [trim($manga['name'])];
		$name_other = self::get_name_other($manga);
		if($name_other)
		{
			foreach($name_other as $val)
			{
				$name[] = trim($val);
			}
		}

		$where = [
			'id[!]' => $manga['id'],
			'OR' => [
				'name[~]' => $name
			]
		];

		$otherName = MangaOtherName::list(['name[~]' => $name]);
		if ($otherName) {
			$where['OR']['OR'] = [];
			$i = 0;
			foreach($otherName as $o) {
				$where['OR']['OR']['[RAW] FIND_IN_SET(:id'.($i).', <{table}.name_other_ids>)'] = ['id'.$i++ => $o['id']];
			}
		}

		$other_teams = [];
		$find_other_teams = self::list($where);
		if($find_other_teams)
		{
			foreach($find_other_teams as $val)
			{
				$name_other_team = self::get_team_name($val);
				$other_teams[] = [
					'team_name' => $name_other_team ? $name_other_team : 'Không rõ', #edit_lang,
					'manga_id' => $val['id'],
					'url' => RouteMap::get('manga', ['id' => $val['id']])
				];
			}
		}

		return $other_teams;
	}


	public static function get_name_other($id_manga, $join = null)
	{
		$manga = is_array($id_manga) ? $id_manga : parent::get($id_manga);
		if(!$manga)
		{
			return null;
		}

		$name_other = array_filter(explode(self::SEPARATOR, $manga['name_other'] ?? ''));
		if($join != '')
		{
			return implode($join, $name_other);
		}
		return $name_other;
	}

	public static function get_status_name($id_manga)
	{
		if(is_numeric($id_manga) || is_array($id_manga))
		{
			$manga = is_array($id_manga) ? $id_manga : parent::get($id_manga);
			if(!$manga)
			{
				return null;
			}	
			$status = $manga['status'];
		}
		else
		{
			$status = $id_manga;
		}
		
		if($status == self::STATUS_COMPLETE)
		{
			return '<span class="manga-status complete">Đã hoàn thành</span>'; #edit_lang
		}
		else if($status == self::STATUS_DROP)
		{
			return '<span class="manga-status drop">Đã tạm ngưng</span>'; #edit_lang
		}
		return '<span class="manga-status ongoing">Đang tiến hành</span>'; #edit_lang
	}

	public static function get_team_name($id_manga, $return_array = false)
	{
		$manga = is_array($id_manga) ? $id_manga : parent::get($id_manga);
		if(!$manga)
		{
			return null;
		}

		$team_name_from_id = trim($manga['team_name_from_id'].'');
		$team_name = trim($manga['team_name'].'');
		
		$name = trim($team_name_from_id.self::SEPARATOR.$team_name);
		if(!$name)
		{
			return null;
		}
		return $return_array == true ?  array_filter(explode(self::SEPARATOR, $name ?? '')) : str_replace(self::SEPARATOR, ' , ', $name);
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