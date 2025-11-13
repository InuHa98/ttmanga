<?php

ignore_user_abort(false);

class ajaxController
{

	public const SEARCH_USER = 'search-user';
	public const SEARCH_TEAM_PARTNER = 'search-team-partner';
	public const SEARCH_TEAM_MEMBER = 'search-team-member';
	public const SEARCH_REPORT = 'search-report';
	public const LIST_CHAPTER = 'list-chapter';
	public const LIST_IMAGE = 'list-image';
	public const UPLOAD_IMAGE = 'upload-image';
	public const CHECK_IMAGE_CHAPTER = 'check-image-chapter';
	public const TYPE_ALLOW_UPLOAD_IMAGE = ['image/jpeg', 'image/jpg', 'image/png'];
	public const MAX_SIZE_UPLOAD_IMAGE = 5242880;

	public function __construct($name = null, $action = null)
	{
		if (session_status() === PHP_SESSION_ACTIVE) {
			session_write_close();
		}
		if (connection_aborted()) {
			exit;
		}

		if ($name) {
			$method = str_replace('-', '_', $name);
			if(!method_exists($this, $method))
			{
				return self::result(403, 'Method not found'); #edit_lang
			}
			$this->$method();			
		}
	}

	private static function result($code, $message, $data = null)
	{
		exit(json_encode([
			'code' => $code,
			'message' => $message,
			'data' => $data
		], JSON_PRETTY_PRINT));
	}

	private static function check_method_accept($accept_method)
	{
		if(!is_array($accept_method))
		{
			$accept_method = [strtoupper($accept_method)];
		}
		else
		{
			$accept_method = array_map(function($value) {
				return strtoupper($value);
			}, $accept_method);
		}
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
		return in_array($method, $accept_method);
	}


	private function search_user()
	{
		if(!self::check_method_accept('get'))
		{
			return self::result(403, 'Access is denied'); #edit_lang
		}

		$keyword = trim(Request::get(InterFaceRequest::KEYWORD, ''));

		$users = User::select([
			'id',
			'name',
			'username',
			'avatar',
			'user_ban',
			'<core_roles.color> AS <role_color>'
		])::list([
			'OR' => [
				'username[~]' => '%'.$keyword.'%',
				'name[~]' => '%'.$keyword.'%'
			],
			'id[!]' => Auth::$data['id']
		]);

		if(!$users || $keyword == '')
		{
			return self::result(404, 'Không tìm thấy thành viên nào'); #edit_lang
		}

		$users = array_map(function($user) {
			$user['display_name'] = User::get_display_name($user);
			$user['avatar'] = User::get_avatar($user);
			$user['first_name'] = User::get_first_charname($user);
			$user['bg_avatar'] = User::get_color_avatar($user['first_name']);
			return $user;
		}, $users);

		return self::result(200, 'Tìm thấy '.count($users).' thành viên', $users); #edit_lang
	}

	private function search_team_member()
	{
		if(!self::check_method_accept('post'))
		{
			return self::result(403, 'Access is denied'); #edit_lang
		}

		$keyword = trim(Request::post(InterFaceRequest::KEYWORD, ''));
		$team_id = intval(Request::post(InterFaceRequest::TEAM, ''));
		$manga_id = intval(Request::post(InterFaceRequest::MANGA, ''));

		$manga = Manga::get(['id' => $manga_id]);
		if(!$manga)
		{
			return self::result(404, 'Không tìm thấy truyện'); #edit_lang
		}

		$team = Team::get(['id' => $team_id]);
		if(!$team)
		{
			return self::result(404, 'Không tìm thấy nhóm'); #edit_lang
		}

		$users = User::select([
			'id',
			'name',
			'username',
			'avatar',
			'user_ban',
			'<core_roles.color> AS <role_color>'
		])::list([
			'OR' => [
				'username[~]' => '%'.$keyword.'%',
				'name[~]' => '%'.$keyword.'%'
			],
			'id[!]' => $manga['user_upload'],
			'team_id' => $team['id']
		]);

		if(!$users || $keyword == '')
		{
			return self::result(404, 'Không tìm thấy thành viên nào'); #edit_lang
		}

		$users = array_map(function($user) {
			$user['display_name'] = User::get_display_name($user);
			$user['avatar'] = User::get_avatar($user);
			$user['first_name'] = User::get_first_charname($user);
			$user['bg_avatar'] = User::get_color_avatar($user['first_name']);
			return $user;
		}, $users);

		return self::result(200, 'Tìm thấy '.count($users).' thành viên', $users); #edit_lang
	}

	private function search_team_partner()
	{
		if(!self::check_method_accept('post'))
		{
			return self::result(403, 'Access is denied'); #edit_lang
		}

		$manga_id = intval(Request::post(InterFaceRequest::ID, ''));
		$keyword = trim(Request::post(InterFaceRequest::KEYWORD, ''));

		$manga = Manga::get(['id' => $manga_id]);
		if(!$manga)
		{
			return self::result(404, 'Không tìm thấy truyện'); #edit_lang
		}

		$teams = Team::select([
			'id',
			'name',
			'avatar'
		])::list([
			'name[~]' => '%'.$keyword.'%',
			'[RAW] <{table}.id> NOT IN (SELECT <core_team_mangas.team_id> FROM <core_team_mangas> WHERE <core_team_mangas.team_id> = <{table}.id> AND <core_team_mangas.manga_id> = :manga_id)' => [
				'manga_id' => $manga['id']
			]
		]);

		if(!$teams || $keyword == '')
		{
			return self::result(404, 'Không tìm thấy nhóm dịch nào'); #edit_lang
		}

		$teams = array_map(function($o) {
			$o['avatar'] = Team::get_avatar($o);
			$o['first_name'] = User::get_first_charname($o);
			$o['bg_avatar'] = User::get_color_avatar($o['first_name']);
			return $o;
		}, $teams);

		return self::result(200, 'Tìm thấy '.count($teams).' nhóm dịch', $teams); #edit_lang
	}

	private function search_report()
	{
		if(!self::check_method_accept('get'))
		{
			return self::result(403, 'Access is denied'); #edit_lang
		}

		$keyword = trim(Request::get(InterFaceRequest::KEYWORD, ''));

		$where = [
			'OR' => [
				'name[~]' => '%'.$keyword.'%',
			],
			'ORDER' => [
				'name' => 'ASC'
			]
		];
		$otherName = MangaOtherName::list(['name[~]' => '%'.$keyword.'%']);
		if ($otherName) {
			$where['OR']['OR'] = [];
			$i = 0;
			foreach($otherName as $o) {
				$where['OR']['OR']['[RAW] FIND_IN_SET(:id'.($i).', <{table}.name_other_ids>)'] = ['id'.$i++ => $o['id']];
			}
		}

		$lst_manga = Manga::list($where);

		if(!$lst_manga || $keyword == '')
		{
			return self::result(404, 'Không tìm thấy truyện nào'); #edit_lang
		}

		$response = [];
		foreach($lst_manga as $manga) {
			$response[] = [
				'id' => $manga['id'],
				'name' => trim($manga['name'].''),
				'name_other' => array_filter(explode("\n", trim($manga['name_other'].''))),
				'image' => trim($manga['image'].'')
			];
		} 

		return self::result(200, 'Tìm thấy '.count($response).' truyện', $response); #edit_lang
	}

	private function list_chapter()
	{
		if(!self::check_method_accept('get'))
		{
			return self::result(403, 'Access is denied'); #edit_lang
		}

		$id = intval(Request::get(InterFaceRequest::ID, null));

		$manga = Manga::get($id);

		if(!$manga)
		{
			return self::result(404, 'Manga not found'); #edit_lang
		}

		$chapters = Chapter::select(['id', 'name', 'created_at', 'download'])::list([
			'manga_id' => $id
		]);

		return self::result(200, 'Successfully', $chapters); #edit_lang
	}

	private function list_image()
	{
		if(!self::check_method_accept('post'))
		{
			return self::result(403, 'Access is denied'); #edit_lang
		}

		$id = intval(Request::post(InterFaceRequest::ID, null));

		$chapter = Chapter::get(['id' => $id]);

		if(!$chapter)
		{
			return self::result(404, 'Không tìm thấy chương truyện'); #edit_lang
		}

		return self::result(200, 'Successfully', explode(Chapter::SEPARATOR, $chapter['image'] ?? '')); #edit_lang
	}

	public function check_image_chapter($chapter = null)
	{

		if (!$chapter) {
			$chapter = Chapter::get(['id' => intval(Request::post(InterFaceRequest::ID, 0))]);
		}

		if(!self::check_method_accept('post') || !$chapter)
		{
			return self::result(403, 'Access is denied'); #edit_lang
		}

		$images = array_filter(explode(Chapter::SEPARATOR, trim($chapter['image'])));
		if (count($images) < 1) {
			return self::result(429, 'Không có ảnh'); #edit_lang
		}

		$error = 0;
		$plus = '';
		foreach($images as $image) {
			if (connection_aborted()) {
				if (session_status() === PHP_SESSION_ACTIVE) {
					session_write_close();
				}
				exit;
			}
			if ($error > 3) {
				$plus = '+';
				break;
			}
			if (!self::is_live_image(trim($image))) {
				$error++;
			}
		}

		if ($error > 0) {
			return self::result(429, 'DIE', $error.$plus); #edit_lang
		}

		return self::result(200, 'OK'); #edit_lang
	}

	public function upload_image($data = null, $name = 0)
	{
		if (connection_aborted()) {
			exit;
		}
		$team = Team::get(['id' => !empty(Auth::$data['team_id']) ? Auth::$data['team_id'] : 0]);

		if(!self::check_method_accept('post') || !$team)
		{
			return self::result(403, 'Access is denied'); #edit_lang
		}

		$config_upload = ConfigUpload::get(['id' => $team['config_id']]);
		if(!$config_upload)
		{
			return self::result(429, 'Chưa có cấu hình upload'); #edit_lang
		}

		$name = trim(Request::post(InterFaceRequest::NAME, Request::request_header(InterFaceRequest::X_NAME, $name)));
		$link = trim(Request::post(InterFaceRequest::URL, ''));
		$data = $data ? $data : file_get_contents('php://input');

		$options = [
			'max_size' => 2048, // kích thước ảnh tối đa sau khi upload
			'size' => 0, // size ảnh google trả về
			'check_size' => false, // kích thước ảnh tối thiểu (px)
			'min_length' => false, // kích cỡ ảnh tối thiểu (byte)
			'max_length' => self::MAX_SIZE_UPLOAD_IMAGE, // Kích cỡ ảnh tối đa (byte)
			'timeout_download' => 5, // giây
			'timeout_upload' => 15, // giây
			're_download' => 3,
			're_put' => 10,
			're_upload' => 3,
			'prefix_name' => 'ttmanga_v2_'
		];

		$googleUpload = new GoogleUpload($config_upload, $options);
		$result = null;
		if ($link) {
			$result = $googleUpload->linkUpload($link, $name);
			$result = !empty($result[0]) ? $result[0] : $result;
		}
		else if ($data) {
			$result = $googleUpload->dataUpload($data, $name);
		}
		else {
			return self::result(429, 'Không tìm thấy dữ liệu upload'); #edit_lang
		}

		if ($googleUpload->isError($result)) {
			return self::result(429, $googleUpload->getError($result)); #edit_lang
		}

		return self::result(200, 'Upload thành công', $result); #edit_lang
	}

	private static function is_live_image($link) {
		$ch = curl_init($link);
		curl_setopt_array($ch, [
			CURLOPT_NOBODY => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_USERAGENT => "Mozilla/5.0",
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_CONNECTTIMEOUT => 5
		]);
		curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

		if (($info['http_code'] ?? 0) != 200 || ($info['download_content_length'] ?? 0) < 1000) {
			return false;
		}

		$ch = curl_init($link);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => false,
			CURLOPT_USERAGENT => "Mozilla/5.0",
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_RANGE => "0-16384" // chỉ lấy 16KB đầu
		]);
		$data = curl_exec($ch);
		curl_close($ch);

		if (!$data) {
			return false;
		}

		$im = @imagecreatefromstring($data);
		if (!$im) {
			return false;
		}

		$width = imagesx($im);
		$height = imagesy($im);

		return !($width == $height && $width < 300);
	}

}





?>