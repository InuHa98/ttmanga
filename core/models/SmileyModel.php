<?php


class Smiley extends Model {

	public static $table = 'core_smileys';
	protected static $primary_key = 'id';
	protected static $timestamps = true;
	protected static $default_join = [
		'LEFT JOIN <core_users> ON <{table}.user_id> = <core_users.id>',
		'LEFT JOIN <core_roles> ON <core_users.role_id> = <core_roles.id>',
	];
	protected static $default_selects = [
		'<{table}.*>',
		'<core_users.username> AS <user_username>',
		'<core_users.avatar> AS <user_avatar>',
		'<core_users.user_ban> AS <user_ban_id>',
		'<core_roles.color> AS <user_role_color>'
	];
	protected static $order_by = [
		'name' => 'ASC'
	];


	public const TYPE_SYSTEM = 0;
	public const TYPE_USER = 1;

	public static function create($name, $images, $is_system = false)
	{

		if($name == '' || !$images)
		{
			return false;
		}

		if(parent::insert([
			'type' => $is_system ? self::TYPE_SYSTEM : self::TYPE_USER,
			'user_id' => Auth::$id,
			'name' => $name,
			'images' => $images
		]) > 0)
		{
			return true;
		}

		return false;
	}

	public static function build_meme_source() {
		if(!Auth::$isLogin) {
			return '[]';
		}
		return json_encode(array_map(function($arr) {
			$arr['images'] = json_decode($arr['images'], true);
			return $arr;
		}, self::select([
			'name',
			'images'
		])::list([
			'OR' => [
				'user_id' => Auth::$data['id'],
				'type' => self::TYPE_SYSTEM
			],
			'ORDER' => [
				'type' => 'ASC'
			]
		])));
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