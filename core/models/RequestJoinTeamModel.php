<?php


class RequestJoinTeam extends Model {

	public static $table = 'core_request_join_team';
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
		'created_at' => 'DESC'
	];

	public static function create($team_id, $note = '')
	{

		if(!$team_id)
		{
			return false;
		}

		if(parent::insert([
			'user_id' => Auth::$id,
			'team_id' => $team_id,
			'note' => $note
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