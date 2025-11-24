<?php


class Bookmark extends Model {

	public static $table = 'core_bookmarks';
	protected static $primary_key = null;
	protected static $timestamps = false;
	protected static $default_join = [
		'INNER JOIN <core_mangas> ON <core_mangas.id> = <{table}.manga_id>',
		'LEFT JOIN <core_chapters> ON <core_chapters.id> = <core_mangas.id_last_chapter>'
	];
	protected static $default_selects = [
		'<core_mangas.*>',
		'<core_chapters.id> AS <id_last_chapter>',
		'<core_chapters.name> AS <name_last_chapter>',
		'<core_chapters.created_at> AS <created_last_chapter>',
		'<{table}.is_read>'
	];
	protected static $order_by = [
		'<{table}.is_read>' => 'ASC',
		'<core_chapters.created_at>' => 'DESC'
	];

	protected static $where = [
		'<core_mangas.is_trash>' => Manga::IS_ACTIVE
	];

	public const TYPE_READ = 1;
	public const TYPE_UNREAD = 0;

	public const MAX_ITEM = 20; 

	public static function add($manga_id = null)
	{
		if(!$manga_id)
		{
			return false;
		}

		$manga = Manga::get($manga_id);
		if(!$manga)
		{
			return false;
		}

		$bookmark = parent::get(['user_id' => Auth::$id, 'manga_id' => $manga['id']]);
		if($bookmark)
		{
			return true;
		}

		if (parent::insert([
			'user_id' => Auth::$id,
			'manga_id' => $manga['id'],
			'is_read' => self::TYPE_READ
		]) > 0) {
			return Manga::update($manga['id'], ['follow[+]' => 1]) > 0;
		}
		return false;
	}

	public static function hasFollow($id_manga)
	{
		return parent::has([
			'user_id' => Auth::$id,
			'manga_id' => $id_manga
		]);
	}

	public static function count_follow_manga($id_manga)
	{
		return parent::count([
			'manga_id' => $id_manga
		]);
	}

	public static function count_follows($type, $bookmark = null)
	{
		if (!$bookmark) {
			return parent::count(['user_id' => Auth::$id, 'is_read' => $type]);
		}
		return is_array($bookmark) ? count($bookmark) : 0;
	}

	public static function remove($manga_id = null)
	{
		if(!$manga_id)
		{
			return false;
		}

		$manga = Manga::get($manga_id);
		if(!$manga)
		{
			return false;
		}

		$where = [
			'user_id' => Auth::$id,
			'manga_id' => $manga['id']
		];

		$bookmark = parent::get($where);
		if(!$bookmark)
		{
			return true;
		}

		if (parent::delete($where) > 0) {
			return Manga::update($manga['id'], ['follow[-]' => 1]) > 0;
		}
		return false;
	}
	
	public static function make_read_all()
	{
		if (!Auth::$id) {
			return false;
		}

		$where = [
			'user_id' => Auth::$id
		];
		return parent::update($where, ['is_read' => self::TYPE_READ]) > 0;
	}

	public static function make_unread_all()
	{
		if (!Auth::$id) {
			return false;
		}

		$where = [
			'user_id' => Auth::$id
		];
		return parent::update($where, ['is_read' => self::TYPE_UNREAD]) > 0;
	}


	public static function make_read($manga_id = null)
	{
		if(!$manga_id)
		{
			return false;
		}

		$manga = $manga_id && is_array($manga_id) ? $manga_id : Manga::get($manga_id);
		if(!$manga)
		{
			return false;
		}

		$where = [
			'user_id' => Auth::$id,
			'manga_id' => $manga['id']
		];

		$bookmark = parent::get($where);
		if(!$bookmark)
		{
			return false;
		}
		return parent::update($where, ['is_read' => self::TYPE_READ]) > 0;
	}

	public static function make_unread($manga_id = null)
	{
		if(!$manga_id)
		{
			return false;
		}

		$manga = $manga_id && is_array($manga_id) ? $manga_id : Manga::get($manga_id);
		if(!$manga)
		{
			return false;
		}

		$where = [
			'user_id' => Auth::$id,
			'manga_id' => $manga['id']
		];

		$bookmark = parent::get($where);
		if(!$bookmark)
		{
			return false;
		}
		return parent::update($where, ['is_read' => self::TYPE_UNREAD]) > 0;
	}

	public static function list_follow($is_unread = false)
	{
		$where = ['user_id' => Auth::$id];

		if ($is_unread) {
			$where['is_read'] = self::TYPE_UNREAD;
		}

		$count = parent::count($where);
		$items = [];


		new Pagination($count, self::MAX_ITEM);
		$pagination = Pagination::get();

		if($count > 0)
		{
			$items = parent::list(array_merge($where, [
				'LIMIT' => [
					$pagination['start'], $pagination['limit']
				]
			]));

			$items = array_map(function($o) {
				return Manga::build_info($o);
			}, $items);
		}

		return [
			'count' => $count,
			'items' => $items,
			'pagination' => $pagination
		];
	}

	public static function hasRead($manga_id, $chapter_id)
	{
		if (!Auth::$isLogin) {
			return;
		}

		$manga = $manga_id && is_array($manga_id) ? $manga_id : Manga::get($manga_id);
		$chapter = $chapter_id && is_array($chapter_id) ? $chapter_id : Chapter::get([
			'manga_id' => $manga['id'],
			'id' => $chapter_id
		]);	

		if(!$manga || !$chapter)
		{
			return false;
		}

		if ($manga['id_last_chapter'] == $chapter['id']) {
			self::make_read($manga);
		}
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