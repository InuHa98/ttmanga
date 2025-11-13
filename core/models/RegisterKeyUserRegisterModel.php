<?php


class RegisterKeyUserRegister extends Model {
	protected static $timestamps = false;
	public static $table = 'core_register_key_user_register';
	protected static $primary_key = null;

	protected static $default_join = [
		'JOIN <core_users> ON <{table}.user_id> = <core_users.id>',
		'LEFT JOIN <core_roles> ON <core_users.role_id> = <core_roles.id>',
	];
	protected static $default_selects = [
		'<core_users.id> AS <id>',
		'<core_users.name> AS <name>',
		'<core_users.username> AS <username>',
		'<core_users.avatar> AS <avatar>',
		'<core_users.user_ban> AS <user_ban>',
		'<core_roles.color> AS <role_color>',
	];
	protected static $order_by = [
	];

	public static function create($key_id, $user_id)
	{
		if (!$key_id || !$user_id) {
			return false;
		}

		if(parent::insert([
			'register_key_id' => $key_id,
			'user_id' => $user_id
		]) == true)
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