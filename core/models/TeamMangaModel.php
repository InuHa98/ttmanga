<?php


class TeamManga extends Model {

	public static $table = 'core_team_mangas';
	protected static $primary_key = null;
	protected static $timestamps = false;
	protected static $default_join = [
		'INNER JOIN <core_teams> ON <core_teams.id> = <{table}.team_id>'
	];
	protected static $default_selects = [
		'<core_teams.id>',
		'<core_teams.name>',
		'<core_teams.avatar>',
		'<core_teams.created_at>',
	];
	protected static $order_by = [
		'<core_teams.name>' => 'ASC'
	];

	protected static $where = [];

	public static function create($data = [])
	{
		if(!isset($data['team_id'], $data['manga_id']))
		{
			return false;
		}

		if(parent::insert([
			'team_id' =>$data['team_id'],
			'manga_id' => $data['manga_id']
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