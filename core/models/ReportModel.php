<?php


class Report extends Model {

	public static $table = 'core_reports';
	protected static $primary_key = 'id';
	protected static $timestamps = true;
	protected static $default_join = [
		'LEFT JOIN <core_users> AS <cur> ON <{table}.user_id> = <cur.id>',
		'LEFT JOIN <core_users> AS <cuu> ON <{table}.user_update> = <cuu.id>',
		'LEFT JOIN <core_roles> AS <crr> ON <cur.role_id> = <crr.id>',
		'LEFT JOIN <core_roles> AS <cru> ON <cuu.role_id> = <cru.id>',
		'LEFT JOIN <core_mangas> ON <{table}.manga_id> = <core_mangas.id>',
		'LEFT JOIN <core_chapters> ON <{table}.chapter_id> = <core_chapters.id>',
	];
	protected static $default_selects = [
		'<{table}.*>',
		'<cur.name> AS <user_report_name>',
		'<cur.username> AS <user_report_username>',
		'<cur.avatar> AS <user_report_avatar>',
		'<cur.user_ban> AS <user_report_ban_id>',
		'<crr.color> AS <user_report_role_color>',
		'<cuu.name> AS <user_update_name>',
		'<cuu.username> AS <user_update_username>',
		'<cuu.avatar> AS <user_update_avatar>',
		'<cuu.user_ban> AS <user_update_ban_id>',
		'<cru.color> AS <user_update_role_color>',
		'<core_mangas.name> AS <manga_name>',
		'<core_mangas.image> AS <manga_image>',
		'<core_chapters.name> AS <chapter_name>'
	];
	protected static $order_by = [
		'status' => 'ASC',
		'created_at' => 'DESC'
	];


	public const STATUS_PENDING = 0;
	public const STATUS_COMPLETE = 1;
	public const STATUS_REJECT = 2;

	public const TYPE_DUPLICATE = 1;
	public const TYPE_INCORRECT = 2;
	public const TYPE_VANDALISM = 3;
	public const TYPE_OTHER = 4;

	public static function create($type, $manga_id, $chapter_id, $note)
	{
		if(!$type)
		{
			return false;
		}

		if(parent::insert([
			'type' => $type,
			'user_id' => Auth::$id ?? 0,
			'manga_id' => $manga_id,
			'chapter_id' => $chapter_id,
			'note' => $note,
			'status' => self::STATUS_PENDING
		]) > 0)
		{
			return true;
		}

		return false;
	}

	public static function getTypeName($type) {
		switch($type) {
			case self::TYPE_DUPLICATE: return 'Trùng lặp';
			case self::TYPE_INCORRECT: return 'Lỗi hình ảnh';
			case self::TYPE_VANDALISM: return 'Phá hoại';
			case self::TYPE_OTHER: return 'Khác';
			default: return 'Không xác định';
		}
	}

	public static function getStatusName($status) {
		switch($status) {
			case self::STATUS_PENDING: return '<span class="btn btn--round btn--small btn-outline-warning">Chờ xử lý</span>';
			case self::STATUS_COMPLETE: return '<span class="btn btn--round btn--small btn-outline-success">Đã xử lý</span>';
			case self::STATUS_REJECT: return '<span class="btn btn--round btn--small btn-outline-danger">Không phải lỗi</span>';
			default: return 'Không xác định';
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

	}

	protected static function onSuccessDelete($count_items = 0)
	{

	}

	protected static function onErrorDelete()
	{

	}
}





?>