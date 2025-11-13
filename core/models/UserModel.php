<?php


class User extends Model {
	public static $table = 'core_users';
	protected static $primary_key = 'id';
	protected static $timestamps = true;
	protected static $default_join = [
		'LEFT JOIN <core_roles> ON <core_users.role_id> = <core_roles.id>'
	];
	protected static $default_selects = [
		'<{table}.*>',
        '<core_roles.name> AS <role_name>', 
        '<core_roles.color> AS <role_color>', 
        '<core_roles.perms> AS <role_perms>', 
        '<core_roles.level> AS <role_level>' 
	];
	protected static $order_by = [
		'username' => 'ASC'
	];

	public const DEFAULT_LIMIT_DEVICE = 3;
	public const DEFAULT_REP = 0;
	public const SEX_UNKNOWN = 'u';
	public const SEX_MALE = 'm';
	public const SEX_FEMALE = 'f';

	public const IS_ACTIVE = 0;


	public static function create($data = [])
	{
		if(!isset($data['username']) || !isset($data['password']) || !isset($data['email']))
		{
			return false;
		}

		$data = array_merge([
			'username' => null,
			'password' => null,
			'email' => null,
			'name' => '',
			'sex' => self::SEX_UNKNOWN,
			'date_of_birth' => '',
			'avatar' => '',
			'cover' => '',
			'bio' => '',
			'facebook' => '',
			'role_id' => Role::DEFAULT_ROLE_VIEWER,
			'perms' => [],
			'settings' => [
				'hide_info' => false,
				'limit_age' => 0,
				'language' => env(DotEnv::DEFAULT_LANGUAGE),
	        	'theme' => env(DotEnv::DEFAULT_THEME)
			],
			'rep' => self::DEFAULT_REP,
			'team_id' => '',
			'forgot_key' => '',
			'forgot_time' => 0,
			'auth_session' => '',
			'limit_device' => self::DEFAULT_LIMIT_DEVICE,
			'user_ban' => self::IS_ACTIVE,
			'adm' => 0
		], $data);

		$data['password'] = Auth::encrypt_password($data['password']);

		if(parent::insert($data) == true)
		{
			return true;
		}

		return false;
	}

	public static function get_setting($user_id = null, $name = null, $default_value = null)
	{
		if(!is_numeric($user_id) && !is_array($user_id))
		{
			$default_value = $name;
			$name = $user_id;
			$user_id = null;
		}

		$user = !$user_id ? Auth::$data : (is_array($user_id) ? $user_id : parent::get($user_id));
		if(!$user)
		{
			return $default_value;
		}

		$settings = $user['settings'];
		if(!is_array($settings))
		{
			$settings = json_decode($settings, true);
		}

		if(!$name)
		{
			return $settings;
		}

		return isset($settings[$name]) ? $settings[$name] : $default_value;
	}

	public static function update_setting($user_id, $data_settings)
	{
		$user = !$user_id ? Auth::$data : (is_array($user_id) ? $user_id : parent::get($user_id));

		$data_settings = array_merge(self::get_setting($user) ?? [], $data_settings);

		if(parent::update($user['id'], [
			'settings' => $data_settings
		]) > 0)
		{
			if($user['id'] == Auth::$id)
			{
				Auth::$data['settings'] = $data_settings;
			}
			return true;
		}

		return false;
	}

	public static function get_first_charname($user)
	{
		$name = (isset($user['name']) && $user['name'] != '') 
			? $user['name'] :
				(isset($user['username']) ? $user['username'] : '');
		return $name[0];
	}

	public static function get_color_avatar($char = null)
	{
		$random_color = [
			'#9e9e9e',
			'#ba68c8',
			'#7986cb',
			'#e06055',
			'#a1887f',
			'#9ccc65',
			'#4dd0e1',
			'#f6bf26',
			'#8e96c2',
			'#57bb8a',
			'#5e97f6'
		];
		$background = $random_color[0];

		if($char != "")
		{
			$hex_array = str_split(ord($char));
			$index = array_reduce($hex_array, function($total, $value) {
				if(!is_numeric($value))
				{
					$value = 0;
				}
				return $total += $value;
			}, 0);

			$max = count($random_color) - 1;
			while($index > $max)
			{
				$index = $index - $max;
			}

			$background = $random_color[$index];
		}

		return $background;
	}

	public static function get_username($user_id = null)
	{
		$user = !$user_id ? Auth::$data : (is_array($user_id) ? $user_id : parent::get($user_id));
		if(!$user)
		{
			return null;
		}

		$is_banned = $user['user_ban'] != self::IS_ACTIVE;
		$name = _echo($user['username']);
		if($is_banned || empty($user['role_color'])) {
			$user['role_color'] = '#333';
		}
		return '<span style="color: '.$user['role_color'].'">'.($is_banned ? '<span class="user-banned">'.$name.'</span>' : $name).'</span>';
	} 


	public static function get_display_name($user_id = null)
	{
		$user = !$user_id ? Auth::$data : (is_array($user_id) ? $user_id : parent::get($user_id));
		if(!$user)
		{
			return null;
		}
		$is_banned = $user['user_ban'] != self::IS_ACTIVE;
		$name = $user['name'] != "" ? _echo($user['name']) : _echo($user['username']);
		if($is_banned) {
			$user['role_color'] = '#000';
		}
		return '<span style="color: '.$user['role_color'].'">'.($is_banned ? '<span class="user-banned">'.$name.'</span>' : $name).'</span>';
	}

	public static function get_role($user_id = null)
	{
		$user = !$user_id ? Auth::$data : (is_array($user_id) ? $user_id : parent::get($user_id));
		if(!$user)
		{
			return '<span class="user-role" style="background: #e2e3e8;color: #000;">Unknown</span>';
		}
		
		return $user['user_ban'] != self::IS_ACTIVE ? '<span class="user-role" style="background: #f6f7f9;color: #111;">Banned</span>' : '<span class="user-role" style="background: '.$user['role_color'].'">'._echo($user['role_name']).'</span>';
	} 

	public static function get_sex($user_id = null)
	{
		$user = !$user_id ? Auth::$data : (is_array($user_id) ? $user_id : parent::get($user_id));

		switch($user['sex'])
		{
			case self::SEX_MALE:
				return lang('system', 'sex_male');
			case self::SEX_FEMALE:
				return lang('system', 'sex_female');
			default:
				return lang('system', 'sex_unknown');
		}
	} 

	public static function get_avatar($user_id = null) 
	{
		return self::get_path_avatar_or_cover('avatar', $user_id);
	}

	public static function get_cover($user_id = null) 
	{
		return self::get_path_avatar_or_cover('cover', $user_id);
	}

	private static function get_path_avatar_or_cover($mode = 'avatar', $user_id = null)
	{

		$user = !$user_id ? Auth::$data : (is_array($user_id) ? $user_id : parent::get($user_id));
	
		$no_image = '';
	
		if(!isset($user['avatar']))
		{
			return $no_image;
		}
	
		preg_match("/^(URL|PATH)=(.*)$/si", trim($user[$mode]), $get);
	
		$type = isset($get[1]) ? $get[1] : null;
		$image = isset($get[2]) ? $get[2] : null;
	
		if(is_null($type) || is_null($image))
		{
			return $no_image;
		}
	
		if(strtolower($type) === "path")
		{
			return APP_URL.'/storage/users/'.$mode.'/'.$image;
		}
		
		return $image;
	}

	public static function count_limit_device($user_id = null)
	{
		$user = !$user_id ? Auth::$data : (is_array($user_id) ? $user_id : parent::get($user_id));
		return Auth::$limit_device == true && $user['limit_device'] != Auth::UNLIMITED_DEVICE ? max(1, $user['limit_device']).'  thiết bị' : 'Unlimited';
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