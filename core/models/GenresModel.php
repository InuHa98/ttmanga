<?php


class Genres extends Model {

	public static $table = 'core_genres';
	protected static $primary_key = 'id';
	protected static $timestamps = true;
	protected static $default_join = [];
	protected static $default_selects = [
		'*'
	];
	protected static $order_by = [
		'name' => 'ASC'
	];

	public static function create($name, $text)
	{
		if ($name == '' || $text == '')
		{
			return false;
		}

		if(self::insert([
			'name' => $name,
			'text' => $text
		]) > 0)
		{
			return true;
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