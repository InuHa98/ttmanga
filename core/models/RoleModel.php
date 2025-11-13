<?php


class Role extends Model {

	public static $table = 'core_roles';
	protected static $primary_key = 'id';
	protected static $timestamps = true;
	protected static $default_join = [];
	protected static $default_selects = [
		'*'
	];
	protected static $order_by = [
		'is_default' => 'DESC',
		'level' => 'ASC'
	];

	protected static $items = null;


    public const DEFAULT_ROLE_ADMIN = 1; // id role admin mặc định
    public const DEFAULT_ROLE_VIEWER = 2; // id role viewer mặc định

	public const DEFAULT_NAME_ADMIN = 'Administrator'; // tên admin mặc định
	public const DEFAULT_NAME_VIEWER = 'Viewer'; // tên viewer mặc định

    public const MIN_LEVEL = 0;
    public const MAX_LEVEL = 100;

	public const IS_DEFAULT = 1;


	public static function create($data = [])
	{
		if (!isset($data['name']) && $data['name'] == "")
		{
			return false;
		}


		$data = array_merge([
			"name" => '',
			"perms" => "[]",
			"color" => "#000000",
			"level" => self::MAX_LEVEL,
			"is_default" => 0
		], $data);

		if(self::insert($data) > 0)
		{
			return true;
		}
		return false;
	}


	public static function setup_default_role()
	{

		if(!self::has(['id' => self::DEFAULT_ROLE_ADMIN]))
		{
			self::create([
				"id" => self::DEFAULT_ROLE_ADMIN,
				"name" => self::DEFAULT_NAME_ADMIN,
				"perms" => [
					UserPermission::ALL_PERMS
				],
				"color" => "#ff0066",
				"level" => self::MIN_LEVEL,
				"is_default" => self::IS_DEFAULT
			]);
		}

		if(!self::has(['id' => self::DEFAULT_ROLE_VIEWER]))
		{
			self::create([
				"id" => self::DEFAULT_ROLE_VIEWER,
				"name" => self::DEFAULT_NAME_VIEWER,
				"perms" => UserPermission::member_default(),
				"color" => "#474747",
				"level" => self::MAX_LEVEL,
				"is_default" => self::IS_DEFAULT
			]);
		}
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
		self::$items = self::select([self::$primary_key])::list($where);
	}

	protected static function onSuccessDelete($count_items = 0)
	{
		self::setup_default_role();

		User::update([
			'role' => array_column(self::$items, self::$primary_key)],
			[
				'role' => self::DEFAULT_ROLE_VIEWER
			]
		);
	}

	protected static function onErrorDelete()
	{

	}
}





?>