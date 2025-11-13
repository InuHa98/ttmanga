<?php


class ConfigUpload extends Model {

	public static $table = 'core_config_upload';
	protected static $primary_key = 'id';
	protected static $timestamps = true;
	protected static $default_join = [];
	protected static $default_selects = ['*'];
	protected static $order_by = [
		'name' => 'ASC'
	];


	public static function create($name, $cookie, $note = '')
	{

		if($name == '' || $cookie == '')
		{
			return false;
		}

		if(parent::insert([
			'name' => $name,
			'cookie' => $cookie,
			'note' => $note,
			'album_id' => ''
		]) > 0)
		{
			return true;
		}

		return false;
	}

	
	public static function check_status($cookie) {
		$google_upload = new GoogleUpload();
		if($google_upload->check_status($cookie) === true)
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