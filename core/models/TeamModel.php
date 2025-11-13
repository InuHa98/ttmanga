<?php


class Team extends Model {

	public static $table = 'core_teams';
	protected static $primary_key = 'id';
	protected static $timestamps = true;
	protected static $default_join = [
		'LEFT JOIN <core_users> AS <core_own> ON <{table}.own_id> = <core_own.id>',
		'LEFT JOIN <core_roles> ON <core_roles.id> = <core_own.role_id>',
	];
	protected static $default_selects = [
		'<{table}.*>',
		'<core_own.name> AS <own_name>',
		'<core_own.username> AS <own_username>',
		'<core_own.avatar> AS <own_avatar>',
		'<core_own.user_ban> AS <own_user_ban>',
		'<core_roles.color> AS <own_role_color>',
	];
	protected static $order_by = [
		'name' => 'ASC'
	];

	public const IS_ACTIVE = 1;
	public const IS_NOT_ACTIVE = 0;
	public const IS_NOT_BAN = 0;


	public static function get($id = null, $select = null){
		return self::build_info(parent::get($id, $select));
	}

	public static function list($where = [], $select = null)
	{
		return array_map(function($o) {
			return self::build_info($o);
		}, parent::list($where, $select));
	}

	private static function build_info($o) {
		if (!empty($o['id'])) {
			$total_members = User::join([])::select(['id'])::list(['team_id' => $o['id']]);	
			$total_mangas = TeamManga::count(['team_id' => $o['id']]);
			$total_chapters = Chapter::count(['user_upload' => array_column($total_members, 'id')]);
		}
		if ($o) {
			$o['total_members'] = isset($total_members) ? count($total_members) : 0;	
			$o['total_mangas'] = $total_mangas ?? 0;
			$o['total_chapters'] = $total_chapters ?? 0;			
		}
		return $o;
	}

	public static function create($name, $note = '') {
		if($name == '') {
			return false;
		}

		if(parent::insert([
			'name' => $name,
			'own_id' => Auth::$id,
			'desc' => '',
			'avatar' => '',
			'cover' => '',
			'facebook' => '',
			'active' => self::IS_NOT_ACTIVE,
			'config_id' => 0,
			'note' => $note,
			'user_ban' => self::IS_NOT_BAN
		]) > 0)
		{
			return true;
		}

		return false;
	}

	public static function get_avatar($team_id = null) 
	{
		return self::get_path_avatar_or_cover('avatar', $team_id);
	}

	public static function get_cover($team_id = null) 
	{
		return self::get_path_avatar_or_cover('cover', $team_id);
	}

	private static function get_path_avatar_or_cover($mode = 'avatar', $team_id = null)
	{

		$team = !$team_id ? parent::get(Auth::$data['team_id']) : (is_array($team_id) ? $team_id : parent::get($team_id));
	
		$no_image = null;
	
		if(!isset($team['avatar']))
		{
			return $no_image;
		}
	
		preg_match("/^(URL|PATH)=(.*)$/si", trim($team[$mode]), $get);
	
		$type = isset($get[1]) ? $get[1] : null;
		$image = isset($get[2]) ? $get[2] : null;
	
		if(is_null($type) || is_null($image))
		{
			return $no_image;
		}
	
		if(strtolower($type) === "path")
		{
			return APP_URL.'/storage/teams/'.$mode.'/'.$image;
		}
		
		return $image;
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