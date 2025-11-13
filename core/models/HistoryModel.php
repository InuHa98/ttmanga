<?php


class History extends Model {

	public static $table = 'core_history';
	protected static $primary_key = 'id';
	protected static $timestamps = false;
	protected static $default_join = [];
	protected static $default_selects = [
		'*'
	];
	protected static $order_by = [
		'id' => 'DESC'
	];


	public const MAX_ITEM = 20;
	public const LIMIT_ITEM = 60;

	public static function add($manga_id, $chapter_id)
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

		$history = parent::get(['user_id' => Auth::$id]);
		if($history)
		{
			$data = json_decode($history['data'], true);
			if(isset($data[$manga['id']]))
			{
				unset($data[$manga['id']]);
			}

			$data[$manga['id']] = [$chapter['id'], time()];
			$data = array_reverse(array_slice(array_reverse($data, true), 0, self::LIMIT_ITEM, true), true);
			
			if(parent::update($history['id'], ['data' => $data]) > 0)
			{
				return true;
			}
		}
		else
		{
			$data = [
				$manga['id'] => [$chapter['id'], time()]
			];
			if(parent::insert([
				'user_id' => Auth::$id,
				'data' => $data
			]) > 0)
			{
				return true;
			}
		}

		return false;
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

		$history = parent::get(['user_id' => Auth::$id]);
		if($history)
		{
			$data = json_decode($history['data'], true);
			if(!isset($data[$manga['id']]))
			{
				return true;
			}
			unset($data[$manga['id']]);
			if(parent::update($history['id'], ['data' => $data]) > 0)
			{
				return true;
			}
		}
		else
		{
			return true;
		}

		return false;
	}


	public static function list_manga()
	{
		$count = 0;
		$items = [];
		$data = null;

		$history = parent::get(['user_id' => Auth::$id]);
		if($history)
		{
			$data = json_decode($history['data'], true);
			$count = count($data);
		}

		new Pagination($count, self::MAX_ITEM);
		$pagination = Pagination::get();

		if($data)
		{
			$data = array_slice($data, $pagination['start'], $pagination['limit'], true);
			$ids = array_map(function($arr) {
				return $arr[0];
			}, array_values($data));

			$items = Chapter::join([
					'INNER JOIN <'.Manga::$table.'> AS <core_mangas> ON <'.Manga::$table.'.id> = <{table}.manga_id> AND <'.Manga::$table.'.is_trash> = '.Manga::IS_ACTIVE
				])::select([
					'<core_mangas.*>',
					'<{table}.id> AS <id_last_chapter>',
					'<{table}.name> AS <name_last_chapter>'
				])::list([
					'id' => $ids,
					'ORDER' => [
						'id' => $ids
					]
				]);


			$items = array_map(function($o) {
				return Manga::build_info($o);
			}, $items);
		}

		return [
			'count' => $count,
			'history' => $data,
			'items' => $items,
			'pagination' => $pagination
		];
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