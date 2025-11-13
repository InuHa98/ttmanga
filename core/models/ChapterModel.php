<?php


class Chapter extends Model {

	public static $table = 'core_chapters';
	protected static $primary_key = 'id';
	protected static $timestamps = true;
	protected static $default_join = [];
	protected static $default_selects = [
		'*'
	];
	protected static $order_by = [
		'index' => 'DESC'
	];

	public const SEPARATOR = "\n";

	public const POSITION_TOP = 'top';
	public const POSITION_BOTTOM = 'bottom';

	public static function create($data = []) {
		return self::modifed(null, $data);
	}

	public static function change($id, $data = []) {
		return self::modifed($id, $data);
	}

	public static function modifed($id, $data = [])
	{
		if(!isset($data['name'], $data['images'], $data['index'], $data['manga_id']))
		{
			return false;
		}

		$data = array_merge([
			'download' => ''
		], $data);


		$images = [];
		foreach($data['images'] as $o) {
			$o = str_replace(["\r", "\n"], '', trim($o));
			$images[] = $o;
		}

		$links = [];
		if (!empty($data['links'])) {
			foreach($data['links'] as $o) {
				$o = str_replace(["\r", "\n"], '', trim($o));
				$links[] = $o;
			}			
		}


		if ($id === null) {
			$data_create = [
				'user_upload' => $data['user_upload'] ?? Auth::$data['id'],
				'manga_id' => $data['manga_id'],
				'name' => ucwords(mb_strtolower($data['name'])),
				'image' => implode(self::SEPARATOR, $images),
				'download' => $links,
				'index' => Chapter::count(['manga_id' => $data['manga_id']]) + 1,
				'created_at' => $data['created_at'] ?? time()
			];

			if (parent::has([
				'name[~]' => $data_create['name'],
				'manga_id' => $data_create['manga_id']
			])) {
				return false;
			}

			if(parent::insert($data_create) > 0)
			{
				Manga::update(['id' => $data['manga_id']], ['id_last_chapter' => Chapter::$insert_id]);
				Bookmark::update(['manga_id' => $data['manga_id']], ['is_read' => Bookmark::TYPE_UNREAD]);
				if ($data['index'] == self::POSITION_BOTTOM) {
					parent::update(['manga_id' => $data['manga_id']], ['index[+]' => 1]);
					parent::update(static::$insert_id, ['index' => 1]);
				}
				return true;
			}
		} else {
			$data_update = [
				'name' => $data['name'],
				'image' => implode(self::SEPARATOR, $images),
				'download' => $links
			];

			if (parent::has([
				'name[~]' => $data_update['name'],
				'manga_id' => $data['manga_id'],
				'id[!]' => $id
			])) {
				return false;
			}

			if(parent::update($id, $data_update) > 0)
			{
				$chapter = Chapter::get(['id' => $id]);
				if ($chapter) {
					switch($data['index']) {
						case self::POSITION_TOP:
							parent::update(['manga_id' => $data['manga_id'], 'index[>]' => $chapter['index']], ['index[-]' => 1]);
							parent::update($id, ['index' => Chapter::count(['manga_id' => $data['manga_id']])]);
							break;
						case self::POSITION_BOTTOM:
							parent::update(['manga_id' => $data['manga_id'], 'index[<]' => $chapter['index']], ['index[+]' => 1]);
							parent::update($id, ['index' => 1]);
							break;
					}
				}
				return true;
			}
		}
		return false;
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