<?php


class RegisterKey extends Model {
	protected static $timestamps = true;
	public static $table = 'core_register_key';
	protected static $primary_key = 'id';

	protected static $default_join = [
		'LEFT JOIN <core_users> AS <user_creator> ON <{table}.creator_id> = <user_creator.id>',
		'LEFT JOIN <core_roles> AS <role_creator> ON <user_creator.role_id> = <role_creator.id>',
	];
	protected static $default_selects = [
		'<{table}.*>',
		'<user_creator.username> AS <creator_username>',
		'<user_creator.avatar> AS <creator_avatar>',
		'<user_creator.user_ban> AS <creator_user_ban>',
		'<role_creator.color> AS <creator_role_color>',
	];
	protected static $order_by = [
		'id' => 'DESC'
	];

	public const INPUT_KEY = 'custom_key';
	public const INPUT_NOTE = 'custom_note';

	public const STATUS_LIVE = 1;
	public const STATUS_DIE = 0;


	public static function create($key = null, $quantity = 1, $note = '')
	{
		$data = [
			'key' => $key != "" ? $key : self::generateKey(24, 6),
			'status' => self::STATUS_LIVE,
			'creator_id' => Auth::$data['id'],
			'quantity' => $quantity,
			'note' => $note
		];

		if(parent::insert($data) == true)
		{
			return true;
		}

		return false;
	}

	public static function validate($key = null)
	{
		if(!is_array($key))
		{
			$key = parent::get(['key' => $key]);
		}

		$status = isset($key['status']) ? $key['status'] : self::STATUS_DIE;

		if($status == self::STATUS_LIVE && $key['quantity'] > 0)
		{
			return true;
		}
		return false;
	}

	public static function delete($id = null)
	{
		if($id == "" || !is_numeric($id))
		{
			return false;
		}

		if(parent::delete($id) > 0)
		{
			return true;
		}
		return false;
	}

	public static function generateKey($length = 24, $explode = 6, $explode_string = "-")
	{
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++)
	    {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $explode > 0 ? implode($explode_string, str_split($randomString, $explode)) : $randomString;
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