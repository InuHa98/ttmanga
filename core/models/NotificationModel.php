<?php


class Notification extends Model {

	public static $table = 'core_notification';
	protected static $primary_key = 'id';
	protected static $timestamps = true;
	protected static $default_join = [
		'LEFT JOIN <core_users> AS <core_user> ON <{table}.from_user_id> = <core_user.id>',
		'LEFT JOIN <core_roles> AS <core_role> ON <core_user.role_id> = <core_role.id>',
	];
	protected static $default_selects = [
		'<{table}.*>',
		'<core_user.id> AS <user_from_id>',
		'<core_user.name> AS <user_from_name>',
		'<core_user.username> AS <user_from_username>',
		'<core_user.avatar> AS <user_from_avatar>',
		'<core_user.user_ban> AS <user_from_user_ban>',
		'<core_role.color> AS <user_from_role_color>'
	];
	protected static $order_by = [
		'created_at' => 'DESC',
		'seen' => 'ASC'
	];

	protected static $items = null;


	public const LIMIT_NEW_ITEM = 10;
	
	public const SEEN = 1;
	public const UNSEEN = 0;


	public const TYPE_ACCEPT_TEAM = 0;
	public const TYPE_REJECT_TEAM = 1;
	public const TYPE_COMMENT_REPLY = 2;
	public const TYPE_COMMENT_DELETE = 3;
	public const TYPE_CHANGE_USERNAME = 4;
	public const TYPE_CHANGE_PASSWORD = 5;
	public const TYPE_CHANGE_EMAIL = 6;
	public const TYPE_CHANGE_ROLE = 7;
	public const TYPE_BAN_USER = 8;
	public const TYPE_UNBAN_USER = 9;
	public const TYPE_CHANGE_NAME_TEAM = 10;
	public const TYPE_CHANGE_OWN_TEAM = 11;
	public const TYPE_BAN_TEAM = 12;
	public const TYPE_UNBAN_TEAM = 13;
	public const TYPE_REQUEST_JOIN_TEAM = 14;
	public const TYPE_DELETE_MANGA = 15;
	public const TYPE_RESTORE_MANGA = 16;
	public const TYPE_DELETE_CHAPTER = 17;
	public const TYPE_APPROVAL_MEMBER = 18;
	public const TYPE_REJECT_MEMBER = 19;
	public const TYPE_REMOVE_MEMBER = 20;
	public const TYPE_SUBMIT_REPORT = 21;
	public const TYPE_ADD_TEAM_PARTNER = 22;
	public const TYPE_REMOVE_TEAM_PARTNER = 23;
	public const TYPE_COMMENT_TAG = 24;
	public const TYPE_COMMENT_COMMENT = 25;

	public static function renderHTML($notification = [], $strip_tags = false)
	{
		if(!isset($notification['type']) || !isset($notification['data']))
		{
			return null;
		}

		$data = json_decode($notification['data'], true);

		$user = [
			'id' => $notification['user_from_id'],
			'name' => $notification['user_from_name'],
			'username' => $notification['user_from_username'],
			'avatar' => $notification['user_from_avatar'],
			'user_ban' => $notification['user_from_user_ban'],
			'role_color' => $notification['user_from_role_color']
		];

		$html = null;
		switch($notification['type'])
		{

			case self::TYPE_ACCEPT_TEAM:
				$html = 'Yêu cầu tạo nhóm <strong>'.$data['team_name'].'</strong> của bạn đã được chấp thuận.';
				break;

			case self::TYPE_REJECT_TEAM:
				$html = '<p>Yêu cầu tạo nhóm <strong>'.$data['team_name'].'</strong> của bạn đã bị từ chối. Lý do: <strong>'._echo($data['reason'], true).'</strong></p>';
				break;

			case self::TYPE_COMMENT_REPLY:
				$query = [InterFaceRequest::COMMENT => $data['comment_id']];
				$url_comment = $data['chapter_id'] ? RouteMap::build_query($query, 'chapter', ['id_manga' => $data['manga_id'], 'id_chapter' => $data['chapter_id']]) : RouteMap::build_query($query, 'manga', ['id' => $data['manga_id']]);
				$html = 'Vừa trả lời một <a href="'.$url_comment.'"><b>bình luận</b></a> của bạn.';
				break;

			case self::TYPE_COMMENT_DELETE:
				$url_comment = $data['chapter_id'] ? RouteMap::get('chapter', ['id_manga' => $data['manga_id'], 'id_chapter' => $data['chapter_id']]) : RouteMap::get('manga', ['id' => $data['manga_id']]);
				$html = '
				<p>Một <a href="'.$url_comment.'"><b>bình luận</b></a> của bạn đã bị xoá:</p>
				<div class="margin-y-4"><i>'._echo($data['comment'], true, true).'</i></div>
				<p>Lý do:</p>
				<p><strong>'._echo($data['reason'], true).'</strong></p>';
				break;
	
			case self::TYPE_COMMENT_TAG:
				$query = [InterFaceRequest::COMMENT => $data['comment_id']];
				$url_comment = $data['chapter_id'] ? RouteMap::build_query($query, 'chapter', ['id_manga' => $data['manga_id'], 'id_chapter' => $data['chapter_id']]) : RouteMap::build_query($query, 'manga', ['id' => $data['manga_id']]);
				$html = 'Vừa nhắc đến bạn trong 1 <a href="'.$url_comment.'"><b>bình luận</b></a>.';
				break;

			case self::TYPE_COMMENT_COMMENT:
				$query = [InterFaceRequest::COMMENT => $data['comment_id']];
				$url_comment = $data['chapter_id'] ? RouteMap::build_query($query, 'chapter', ['id_manga' => $data['manga_id'], 'id_chapter' => $data['chapter_id']]) : RouteMap::build_query($query, 'manga', ['id' => $data['manga_id']]);
				$html = 'Vừa bình luận trong 1 <a href="'.$url_comment.'"><b>bình luận</b> mà bạn đã trả lời</a>.';
				break;

			case self::TYPE_CHANGE_USERNAME:
				$html = '<p>Username của bạn vừa được thay đổi thành: <strong>'._echo($data['username']).'</strong></p>';
				break;

			case self::TYPE_CHANGE_PASSWORD:
				$html = '<p>Password của bạn vừa được thay đổi thành: <strong>'._echo($data['password']).'</strong></p>';
				break;

			case self::TYPE_CHANGE_EMAIL:
				$html = '<p>Email của bạn vừa được thay đổi thành: <strong>'._echo($data['email']).'</strong></p>';
				break;

			case self::TYPE_CHANGE_ROLE:
				$html = '<p>Chức vụ của bạn vừa được thay đổi thành: <span class="user-role margin-l-3 margin-t-2" style="background: '.$data['color'].'">'._echo($data['name']).'</span></p>';
				break;

			case self::TYPE_BAN_USER:
				$html = '<p>Tài khoản của bạn đã bị cấm.</p>';
				if($data['reason']) {
					$html .= '<p>Lý do: <strong>'._echo($data['reason'], true).'</strong></p>';
				}
				break;

			case self::TYPE_UNBAN_USER:
				$html = '<p>Tài khoản của bạn đã được gỡ bỏ lệnh cấm.</p>';
				break;

			case self::TYPE_CHANGE_NAME_TEAM:
				$html = '<p>Tên nhóm dịch của bạn vừa được thay đổi thành: <strong>'._echo($data['name']).'</strong></p>';
				break;

			case self::TYPE_CHANGE_OWN_TEAM:
				$url = '<a class="btn btn-outline-info btn--small margin-t-2 margin-l-2" href="'.RouteMap::get('team', ['name' => $data['team_name']]).'">'._echo($data['team_name']).'</a>';
				if($data['own_id'] == Auth::$data['id']) {
					$html = '<p>Bạn vừa được chỉ định làm trưởng nhóm dịch: '.$url.'</p>';
				} else {
					$html = '<p>Bạn không còn là trưởng nhóm của '.$url.'</p>';
				}
				break;

			case self::TYPE_BAN_TEAM:
				$html = '<p>Nhóm dịch của bạn đã bị cấm. </p>';
				if($data['reason']) {
					$html .= '<p>Lý do: <strong>'._echo($data['reason'], true).'</strong></p>';
				}
				break;

			case self::TYPE_UNBAN_TEAM:
				$html = '<p>Nhóm dịch của bạn đã được gỡ bỏ lệnh cấm.</p>';
				break;

			case self::TYPE_REQUEST_JOIN_TEAM:
				$user = User::get(['id' => $data['user_id']]);
				$html = '<p><a href="'.RouteMap::get('profile', ['id' => $user['id']]).'"><b>'._echo($user['username']).'</b></a> vừa gửi yêu cầu tham gia nhóm dịch của bạn: '._echo($data['note']).'</p>';
				break;

			case self::TYPE_DELETE_MANGA:
				$html = '<p>Truyện <b>'._echo($data['name']).'</b> của bạn đã bị xoá.</p>';
				if($data['reason']) {
					$html .= '<p>Lý do: <strong>'._echo($data['reason'], true).'</strong></p>';
				}
				break;

			case self::TYPE_RESTORE_MANGA:
				$manga = Manga::get(['id' => $data['manga_id']]);
				$html = '<p>Truyện <a target="_blank" href="'.RouteMap::get('manga', ['id' => $manga['id']]).'"><b>'._echo($manga['name']).'</b></a> của bạn đã được khôi phục hiển thị.</p>';
				break;

			case self::TYPE_DELETE_CHAPTER:
				$manga = Manga::get(['id' => $data['manga_id']]);
				$html = '<p>Chương <b>'._echo($data['name']).'</b> của truyện <a target="_blank" href="'.RouteMap::get('manga', ['id' => $manga['id']]).'"><b>'._echo($manga['name']).'</b></a> do bạn đăng đã bị xoá.</p>';
				if($data['reason']) {
					$html .= '<p>Lý do: <strong>'._echo($data['reason'], true).'</strong></p>';
				}
				break;

			case self::TYPE_APPROVAL_MEMBER:
				$team = Team::get(['id' => $data['team_id']]);
				$html = '<p>Yêu cầu tham gia nhóm dịch <a target="_blank" href="'.RouteMap::get('team', ['name' => $team['name']]).'"><b>'._echo($team['name']).'</b></a> của bạn đã được chấp thuận.</p>';
				break;

			case self::TYPE_REJECT_MEMBER:
				$team = Team::get(['id' => $data['team_id']]);
				$html = '<p>Yêu cầu tham gia nhóm dịch <a target="_blank" href="'.RouteMap::get('team', ['name' => $team['name']]).'"><b>'._echo($team['name']).'</b></a> của bạn đã bị từ chối</p>';
				if($data['reason']) {
					$html .= '<p>Lý do: <strong>'._echo($data['reason'], true).'</strong></p>';
				}
				break;

			case self::TYPE_REMOVE_MEMBER:
				$team = Team::get(['id' => $data['team_id']]);
				$html = '<p>Bạn vừa bị xoá khỏi nhóm dịch <a target="_blank" href="'.RouteMap::get('team', ['name' => $team['name']]).'"><b>'._echo($team['name']).'</b></a></p>';
				if($data['reason']) {
					$html .= '<p>Lý do: <strong>'._echo($data['reason'], true).'</strong></p>';
				}
				break;

			case self::TYPE_SUBMIT_REPORT:
				$html = '<p>Bạn vừa nhận được 1 thông báo lỗi truyện. Bấm vào <a target="_blank" href="'.RouteMap::get('my_team', ['block' => teamController::BLOCK_REPORT]).'">đây</a> để xem thông tin báo lỗi.</p>';
				break;

			case self::TYPE_ADD_TEAM_PARTNER:
				$manga = Manga::get(['id' => $data['manga_id']]);
				$html = '<p>Nhóm của bạn vừa được bổ nhiệm làm cộng sự của truyện <a target="_blank" href="'.RouteMap::get('manga', ['id' => $manga['id']]).'">'._echo($manga['name']).'</a>.</p>';
				break;

			case self::TYPE_REMOVE_TEAM_PARTNER:
				$manga = Manga::get(['id' => $data['manga_id']]);
				$html = '<p>Nhóm của bạn vừa được bị xoá quyền cộng sự của truyện <a target="_blank" href="'.RouteMap::get('manga', ['id' => $manga['id']]).'">'._echo($manga['name']).'</a>.</p>';
				break;

			default:
				return null;
		}
		return $strip_tags ? strip_tags($html) : $html;
	}

	public static function create($data = [])
	{
		if(!isset($data['user_id'], $data['from_user_id'], $data['type']))
		{
			return false;
		}

		if(is_array($data['user_id'])) {
			foreach($data['user_id'] as $id) {
				self::create(array_merge($data, [
					'user_id' => $id
				]));
			}
			return true;
		}


		$data = array_merge([
			'seen' => self::UNSEEN,
			'data' => []
		], $data);

		if(self::insert($data) > 0)
		{
			return true;
		}
		return false;
	}

	public static function count_new()
	{
		return parent::count([
			'user_id' => Auth::$id,
			'seen' => self::UNSEEN
		]);
	}

	public static function make_seen($ids = [])
	{
		$where = [
			'user_id' => Auth::$id
		];

		if($ids)
		{
			$where['id'] = $ids;
		}

		if(parent::update($where, ['seen' => self::SEEN]) > 0)
		{
			return true;
		}
		return false;
	}

	public static function make_unseen($ids = [])
	{
		$where = [
			'user_id' => Auth::$id
		];

		if($ids)
		{
			$where['id'] = $ids;
		}

		if(parent::update($where, ['seen' => self::UNSEEN]) > 0)
		{
			return true;
		}
		return false;
	}

	public static function delete($ids = [])
	{
		$where = [
			'user_id' => Auth::$id
		];

		if($ids)
		{
			$where['id'] = $ids;
		}

		if(parent::delete($where) > 0)
		{
			return true;
		}
		return false;
	}

	public static function get_list_new($limit = null)
	{
		$where = [
			'user_id' => Auth::$id,
			'seen' => self::UNSEEN
		];


		$where['LIMIT'] = is_numeric($limit) ? $limit : self::LIMIT_NEW_ITEM;

		return parent::list($where);
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