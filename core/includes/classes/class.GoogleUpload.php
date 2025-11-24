<?php


class GoogleUpload
{
	private $upload_config = null;
	private $cookie = null;
	private $album_id = null;
	private $batchid = 0;

	private $options = [
		'max_size' => 2048, // max size before upload
		'size' => 0, // size render url
		'check_size' => false, // (px) false to disable
		'min_length' => false, // (byte) false to disable
		'max_length' => false, // (byte) false to disable
		'timeout_download' => 5, // second
		'timeout_upload' => 15, // second
		're_download' => 3,
		're_put' => 10,
		're_upload' => 3,
		'prefix_name' => null
	];

	private const URL_CHECKER = 'https://www.blogger.com/picker?hl=en&parent=https%3A%2F%2Fwww.blogger.com&multiselectEnabled=true&nav=((%22photos%22,%22Upload%22,{%22mode%22:%22palette%22,%22hideBc%22:%22true%22,%22upload%22:%22true%22,%22data%22:{%22silo_id%22:%223%22},%22parent%22:%220%22}))';

	private const URL_RESUMABLE = 'https://www.blogger.com/upload/blogger/photos/resumable?authuser=0';

	public const ERROR_IMAGE = '{{error-image}}';
	public const ERROR_SIZE = '{{error-size}}';
	public const ERROR_MIN_LENGTH = '{{error-min-length}}';
	public const ERROR_MAX_LENGTH = '{{error-max-length}}';
	public const ERROR_UPLOAD = '{{error-upload}}';

	function __construct($id_upload_config = null, $options = [])
	{
		$this->getUploadConfig($id_upload_config);
		if($options)
		{
			$this->set($options);
		}
	}

	public static function bytesToMB($bytes, $precision = 2) {
		return number_format($bytes / 1048576, $precision);
	}

	public function isError($code) {
		return preg_match('/{{error-(.*)}}/si', $code);
	}

	public function getError($code) {
		switch($code) {
			case self::ERROR_IMAGE: return 'Dữ liệu ảnh không hợp lệ';
			case self::ERROR_SIZE: return 'Kích thước ảnh tối thiểu phải là '.$this->options['check_size'].'px';
			case self::ERROR_MIN_LENGTH: return 'Kích cỡ ảnh tối thiểu phải là '.self::bytesToMB($this->options['min_length']).'MB';
			case self::ERROR_MAX_LENGTH: return 'Kích cỡ ảnh tối đa là '.self::bytesToMB($this->options['max_length']).'MB';
			case self::ERROR_UPLOAD: return 'Không thể upload ảnh. Vui lòng thử lại sau ít phút';
			default: return 'Có lỗi xảy ra. Vui lòng thử lại sau ít phút';
		}
	}

	public function set($options = [])
	{
		if(!$options || !is_array($options))
		{
			return false;
		}

		if(isset($options['max_size']) && intval($options['max_size']) > 0)
		{
			$this->options['max_size'] = intval($options['max_size']);
		}

		if(isset($options['size']) && intval($options['size']) >= 0)
		{
			$this->options['size'] = intval($options['size']);
		}

		if(isset($options['check_size']) && ($options['check_size'] === false || intval($options['check_size']) > 0))
		{
			$this->options['check_size'] = $options['check_size'];
		}

		if(isset($options['min_length']) && ($options['min_length'] === false || intval($options['min_length']) > 0))
		{
			$this->options['min_length'] = $options['min_length'];
		}

		if(isset($options['max_length']) && ($options['max_length'] === false || intval($options['max_length']) > 0))
		{
			$this->options['max_length'] = $options['max_length'];
		}

		if(isset($options['timeout_download']) && intval($options['timeout_download']) > 0)
		{
			$this->options['timeout_download'] = $options['timeout_download'];
		}

		if(isset($options['timeout_upload']) && intval($options['timeout_upload']) > 0)
		{
			$this->options['timeout_upload'] = $options['timeout_upload'];
		}

		if(isset($options['re_download']) && intval($options['re_download']) >= 0)
		{
			$this->options['re_download'] = $options['re_download'];
		}

		if(isset($options['re_put']) && intval($options['re_put']) >= 0)
		{
			$this->options['re_put'] = $options['re_put'];
		}

		if(isset($options['re_upload']) && intval($options['re_upload']) >= 0)
		{
			$this->options['re_upload'] = $options['re_upload'];
		}

		if(isset($options['prefix_name']))
		{
			$this->options['prefix_name'] = trim($options['prefix_name'].'');
		}

	}

	private function getUploadConfig($id_upload_config = null)
	{

		if(is_null($id_upload_config))
		{
			return false;
		}

		$this->upload_config = is_array($id_upload_config) ? $id_upload_config : ConfigUpload::get($id_upload_config);

		if(isset($this->upload_config['cookie']))
		{
			$this->setCookie(trim($this->upload_config['cookie']));
		}

		if(isset($this->upload_config['album_id']))
		{
			$this->setAlbum_id(trim($this->upload_config['album_id']));
		}
	}

	public function setCookie($cookie = null)
	{
		if($cookie)
		{
			return $this->cookie = $cookie;
		}
		else
		{
			if(isset($this->upload_config['cookie']))
			{
				return $this->cookie = trim($this->upload_config['cookie'].'');
			}
		}
	}

	public function setAlbum_id($id = null)
	{
		if($id)
		{
			return $this->album_id = $id;
		}
		else
		{
			if(isset($this->upload_config['album_id']))
			{
				return $this->album_id = trim($this->upload_config['album_id'].'');
			}
		}
		
	}

	private function updateAlbum_id($id = null){

		if(isset($this->upload_config['id']) && $this->upload_config['id'])
		{
			if($id && is_numeric($id))
			{
				if(ConfigUpload::update($this->upload_config['id'], [
					'album_id' => $id
				])) {
					$this->album_id = $id;
					return true;
				}
			}			
		}

		return false;
	}

	private function request_get_api($url = null, $header = [], $cookie = null){

		$cookie = is_null($cookie) ? $this->cookie : $cookie;

		if(is_null($url) || !$cookie)
		{
			return false;
		}

		$header[] = 'Upgrade-Insecure-Requests: 1';
		$header[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3';
		$header[] = 'Cookie: '.$cookie;

		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_URL => $url,
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT => $this->options['timeout_download'],
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_FAILONERROR => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_VERBOSE => 0
		]);

		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}

	private function request_post_api($url = null, $post = null, $header = [], $cookie = null){

		$cookie = is_null($cookie) ? $this->cookie : $cookie;

		if(is_null($url) || !$cookie)
		{
			return false;
		}

		$header[] = 'Upgrade-Insecure-Requests: 1';
		$header[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3';
		$header[] = 'Cookie: '.$cookie;

		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_URL => $url,
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_POSTFIELDS => $post,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT => $this->options['timeout_upload'],
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_FAILONERROR => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_VERBOSE => 0
		]);

		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}

	public function check_status($cookie = null)
	{
		$cookie = is_null($cookie) ? $this->cookie : $cookie;

		if(!$cookie)
		{
			return false;
		}

		$html = $this->request_get_api(self::URL_CHECKER, [], $cookie);

		if(preg_match("/<title>OnePick<\/title>/si", $html))
		{
			return true;
		}
		return false;
	}


	private function reWriteName($text = null)
	{
		$text = trim($text.'');
		if($text === "")
		{
			return null;
		}

	    $text = str_replace("–", "", $text);
	    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
	    $text = str_replace(" ", "-", $text);
	    $text = str_replace("@", "-", $text);
	    $text = str_replace("/", "-", $text);
	    $text = str_replace("\\", "-", $text);
	    $text = str_replace(":", "", $text);
	    $text = str_replace("\"", "", $text);
	    $text = str_replace("<", "", $text);
	    $text = str_replace(">", "", $text);
	    $text = str_replace(",", "", $text);
	    $text = str_replace("?", "", $text);
	    $text = str_replace(";", "", $text);
	    $text = str_replace(".", "", $text);
	    $text = str_replace("[", "", $text);
	    $text = str_replace("]", "", $text);
	    $text = str_replace("(", "", $text);
	    $text = str_replace(")", "", $text);
	    $text = str_replace("́", "", $text);
	    $text = str_replace("̀", "", $text);
	    $text = str_replace("̃", "", $text);
	    $text = str_replace("̣", "", $text);
	    $text = str_replace("̉", "", $text);
	    $text = str_replace("*", "", $text);
	    $text = str_replace("!", "", $text);
	    $text = str_replace("$", "-", $text);
	    $text = str_replace("&", "-and-", $text);
	    $text = str_replace("%", "", $text);
	    $text = str_replace("#", "", $text);
	    $text = str_replace("^", "", $text);
	    $text = str_replace("=", "", $text);
	    $text = str_replace("+", "", $text);
	    $text = str_replace("~", "", $text);
	    $text = str_replace("`", "", $text);
	    $text = str_replace("--", "-", $text);
	    $text = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $text);
	    $text = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $text);
	    $text = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $text);
	    $text = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $text);
	    $text = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $text);
	    $text = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $text);
	    $text = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $text);
	    $text = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $text);
	    $text = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $text);
	    $text = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $text);
	    $text = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $text);
	    $text = preg_replace("/(đ)/", 'd', $text);
	    $text = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $text);
	    $text = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $text);
	    $text = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $text);
	    $text = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $text);
	    $text = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $text);
	    $text = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $text);
	    $text = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $text);
	    $text = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $text);
	    $text = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $text);
	    $text = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $text);
	    $text = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $text);
	    $text = preg_replace("/(Đ)/", 'D', $text);
	    $text = preg_replace("#[^A-z0-9]#is", "-", $text);
	    $text = preg_replace("#([-]+)#", "-", $text);
	    $text = trim(strtolower($text.''),'-');
	    return $text;
	}

	private function convert_webp_to_jpg($str = null)
	{
        if(empty($str))
        {
            return $str;
        }
        
        $src = imagecreatefromstring($str);
        ob_start();
        imagejpeg($src, null, 100);
        imagedestroy($src);
        $imgData = ob_get_contents();
        ob_end_clean();

        return $imgData;
    }

	private function resize_from_string($str = null, $width = 0, $height = 0)
	{

		if(($width <= $this->options['max_size'] && $height <= $this->options['max_size']) || !$str)
		{
			return $str;
		}
		
		if (function_exists('exif_read_data')) {
			$temp = tmpfile();
			$meta = stream_get_meta_data($temp);
			fwrite($temp, $str);
			$path = $meta['uri'];
			$exif = @exif_read_data($path);
			fclose($temp);

			if (!empty($exif['Orientation']) && in_array($exif['Orientation'], [6, 8])) {
				[$width, $height] = [$height, $width];
			}
		}

		if($width > $height)
		{
			$newWidth = $this->options['max_size'];
			$newHeight = ($this->options['max_size'] * $height) / $width;
		}
		else if($height > $width)
		{
			$newWidth = ($this->options['max_size'] * $width) / $height;
			$newHeight = $this->options['max_size'];
		}
		else
		{
			$newWidth = $this->options['max_size'];
			$newHeight = $this->options['max_size'];
		}

		$src = imagecreatefromstring($str);


		if (!empty($exif['Orientation'])) {
			switch ($exif['Orientation']) {
				case 3:
					$src = imagerotate($src, 180, 0);
					break;
				case 6:
					$src = imagerotate($src, -90, 0);
					break;
				case 8:
					$src = imagerotate($src, 90, 0);
					break;
			}
		}

		$newWidth = (int)round($newWidth);
		$newHeight = (int)round($newHeight);

		$dst = imagecreatetruecolor($newWidth, $newHeight);
		imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

		ob_start();
	    imagejpeg($dst, null, 100);
	    imagedestroy($dst);
	    $imgData = ob_get_contents();
		ob_end_clean();

	    return [
	    	'data' => $imgData,
	    	'size' => [
	    		$newWidth,
	    		$newHeight
	    	]
	    ];
	}

	public function resize_from_path($path = null)
	{

		if(!is_file($path))
		{
			return null;
		}

		list($width, $height, $type, $attr) = getimagesize($path);

		if(($width <= $this->options['max_size'] && $height <= $this->options['max_size']))
		{
			$fh = fopen($path, "r");
			$imgData = fread($fh, filesize($path));
			fclose($fh);
			return $imgData;
		}
		

		if (function_exists('exif_read_data')) {
			$exif = @exif_read_data($path);
			if (!empty($exif['Orientation']) && in_array($exif['Orientation'], [6, 8])) {
				[$width, $height] = [$height, $width];
			}
		}

		if($width > $height)
		{
			$newWidth = $this->options['max_size'];
			$newHeight = ($this->options['max_size'] * $height) / $width;
		}
		else if($height > $width)
		{
			$newWidth = ($this->options['max_size'] * $width) / $height;
			$newHeight = $this->options['max_size'];
		}
		else
		{
			$newWidth = $this->options['max_size'];
			$newHeight = $this->options['max_size'];
		}

		$newWidth = (int)round($newWidth);
		$newHeight = (int)round($newHeight);

		$src = imagecreatefromjpeg($path);

		if (!empty($exif['Orientation'])) {
			switch ($exif['Orientation']) {
				case 3:
					$src = imagerotate($src, 180, 0);
					break;
				case 6:
					$src = imagerotate($src, -90, 0);
					[$width, $height] = [$height, $width];
					break;
				case 8:
					$src = imagerotate($src, 90, 0);
					[$width, $height] = [$height, $width];
					break;
			}
		}

		$dst = imagecreatetruecolor($newWidth, $newHeight);
		imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

		ob_start();
	    imagejpeg($dst, null, 100);
	    imagedestroy($dst);
	    $imgData = ob_get_contents();
		ob_end_clean();

	    return $imgData;
	}

	public function renderUrl($url = null)
	{
		$url = str_replace(basename($url), 's' . $this->options['size']. '/' . basename($url), $url);
		return preg_replace("#^https?://(.*?)\.googleusercontent\.com/(.*?)$#si", "https://lh6.googleusercontent.com/$2", $url);
	}

	public function reDrawUrl($link = null)
	{
		if(is_null($link))
		{
			return null;
		}

		$link = trim(rawurldecode($link.''));
		$link = str_replace("%25", "%", $link);
		$link = str_replace(" ", "%20", $link);

		if (preg_match("#https?://lh([0-9])\.googleusercontent#is", $link) 
			|| preg_match("#https?://lh([0-9])\.ggpht#is", $link) 
			|| preg_match("#https?://[0-9]+(\.bp\.blogspot)#is", $link))
		{
			$link = $link.'?imgmax=0';
			$link = preg_replace("#(.*?)\?imgmax=(.*?)$#si", "$1?imgmax=0", $link);
			$link = preg_replace("#(.*?)/(w|h|s)([0-9]+)/(.*?)#si", "$1/s0/$4", $link);
			$link = preg_replace("#(.*?)\=(w|h|s)([0-9]+)(.*?)#si", "$1=s0$4", $link);
			$link = preg_replace("#https?://(.*?).googleusercontent.com/(.*?)&url=(.*?)#si", "$3", $link);
			$link = preg_replace("#^https?://(.*?)\.googleusercontent\.com/(.*?)$#si", "https://3.bp.blogspot.com/$2", $link);
		}

		$link = str_replace("&#10;", "", $link);
		return $link;
	}

	private function _buildDataPut($name = null, $size = 0, $position = 0)
	{
		$position++;
        return '{
					"protocolVersion":"0.8",
					"createSessionRequest":{
						"fields":[
							{"external":{
								"name":"file",
								"filename":"'.$this->reWriteName($name).'.jpg",
								"put":{},
								"size":'.$size.'
								}
							},
							{"inlined":{
								"name":"use_upload_size_pref",
								"content":"true",
								"contentType":"text/plain"
								}
							},
							{"inlined":{
								"name":"disable_asbe_notification",
								"content":"true",
								"contentType":"text/plain"
								}
							},
							{"inlined":{
								"name":"skip_face_detection",
								"content":"true",
								"contentType":"text/plain"
								}
							},
							{"inlined":{
								"name":"skip_face_recognition",
								"content":"true",
								"contentType":"text/plain"
								}
							},
							{"inlined":{
								"name":"silo_id",
								"content":"3",
								"contentType":"text/plain"
								}
							},
							{"inlined":{
								"name":"title",
								"content":"'.$name.'.jpg",
								"contentType":"text/plain"
								}
							},
							{"inlined":{
								"name":"batchid",
								"content":"'.$this->batchid.'",
								"contentType":"text/plain"
								}
							},
							{"inlined":{
								"name":"album_id",
								"content":"'.$this->album_id.'",
								"contentType":"text/plain"}
							},
							{"inlined":{
								"name":"album_abs_position",
								"content":"'.$position.'",
								"contentType":"text/plain"
								}
							},
							{"inlined":{
								"name":"client",
								"content":"default",
								"contentType":"text/plain"
								}
							}
						]
					}
				}';
	}

	private function _getPutInfo($data = null)
	{

		if(!$data)
		{
			return [
				'status' => false,
				'message' => 'DataPut not found.'
			];
		}

		$result = null;

		$retry = 0;
		do {
			$request = $this->request_post_api(self::URL_RESUMABLE, $data, [
				"Content-Type: application/x-www-form-urlencoded;charset=UTF-8"
			]);

			if(strpos($request, "Method Not Allowed"))
			{
				return [
					'status' => false,
					'message' => 'Cookie error.'
				];
			}

			if(strpos($request, "Request body is malformed"))
			{
				return [
					'status' => false,
					'message' => 'DataPut error.'
				];
			}

			$request_json  = json_decode($request, true);	

			$result = isset($request_json['sessionStatus']['externalFieldTransfers'][0]['putInfo']['url']) ? trim($request_json['sessionStatus']['externalFieldTransfers'][0]['putInfo']['url']) : null;
			$retry++;		
		} while(!$result && $retry < $this->options['re_put']);

		if(!$result)
		{
			return [
				'status' => false,
				'message' => 'Can\'t get PutInfo.'
			];
		}

		return [
			'status' => true,
			'message' => 'get PutInfo success.',
			'putInfo' => $result
		];
	}

	private function _start_upload($putInfo = null, $data = null, $dataPut = null)
	{

		if(!$putInfo)
		{
			return [
				'status' => false,
				'message' => 'PutInfo not found.'
			];
		}

		if(!$data)
		{
			return [
				'status' => false,
				'message' => 'Data image not found.'
			];
		}

		if(!$dataPut)
		{
			return [
				'status' => false,
				'message' => 'DataPut not found.'
			];	
		}

		$check_album = false;
		$result = null;

		$retry = 0;
		do {
			$request_json = json_decode($this->request_post_api($putInfo, $data, [
				"Content-Type: application/octet-stream"
			]), true);

			if($check_album === false)
			{
				$error = isset($request_json['errorMessage']['additionalInfo']['uploader_service.GoogleRupioAdditionalInfo']['completionInfo']['customerSpecificInfo']['statusCode']) ? trim($request_json['errorMessage']['additionalInfo']['uploader_service.GoogleRupioAdditionalInfo']['completionInfo']['customerSpecificInfo']['statusCode']) : null;
				if(in_array($error, [5,20,23]))
				{
					$this->album_id = $this->setAlbum_id();
					$putInfo = $this->_getPutInfo($dataPut);

					if($putInfo['status'] !== true)
					{
						return [
							'status' => false,
							'message' => $putInfo['message']
						];
					}
					else
					{
						$putInfo = $putInfo['putInfo'];
					}
				}
				$check_album = true;
			}

			$result = isset($request_json['sessionStatus']['additionalInfo']['uploader_service.GoogleRupioAdditionalInfo']['completionInfo']['customerSpecificInfo']['url']) ? trim($request_json['sessionStatus']['additionalInfo']['uploader_service.GoogleRupioAdditionalInfo']['completionInfo']['customerSpecificInfo']['url']) : null;

			$retry++;
		} while(!$result && $retry < $this->options['re_upload']);


		$width = isset($request_json['sessionStatus']['additionalInfo']['uploader_service.GoogleRupioAdditionalInfo']['completionInfo']['customerSpecificInfo']['width']) ? trim($request_json['sessionStatus']['additionalInfo']['uploader_service.GoogleRupioAdditionalInfo']['completionInfo']['customerSpecificInfo']['width']) : 0;
		$height = isset($request_json['sessionStatus']['additionalInfo']['uploader_service.GoogleRupioAdditionalInfo']['completionInfo']['customerSpecificInfo']['height']) ? trim($request_json['sessionStatus']['additionalInfo']['uploader_service.GoogleRupioAdditionalInfo']['completionInfo']['customerSpecificInfo']['height']) : 0;

		if(!$result)
		{
			return [
				'status' => false,
				'message' => 'Error upload.'
			];
		}

		$result = $this->renderUrl($result);

		$current_album_id = isset($request_json['sessionStatus']['additionalInfo']['uploader_service.GoogleRupioAdditionalInfo']['completionInfo']['customerSpecificInfo']['albumid']) ? trim($request_json['sessionStatus']['additionalInfo']['uploader_service.GoogleRupioAdditionalInfo']['completionInfo']['customerSpecificInfo']['albumid']) : null;

		if(!$this->album_id && $current_album_id)
		{
			$this->updateAlbum_id($current_album_id);
		}

		return [
			'status' => true,
			'message' => 'Upload success',
			'url' => $result,
			'width' => $width,
			'height' => $height
		];
	}

	private function _requestUpload($data = null, $position = 0)
	{

		if(!$data)
		{
			return [
				'status' => false
			];
		}

		$dataPut = $this->_buildDataPut($data['name'], $data['size'], $position);
		$putInfo = $this->_getPutInfo($dataPut);

		if($putInfo['status'] !== true)
		{
			return $putInfo;
		}
		else
		{
			return $this->_start_upload($putInfo['putInfo'], $data['data'], $dataPut);
		}
	}

	private function _upload($image_data = null, $image_size = [0, 0], $name = null, $i = 0)
	{
		if(!$image_data)
		{
			return self::ERROR_IMAGE;
		}

    	if($image_size['mime'] == 'image/webp')
    	{
    		$image_data = $this->convert_webp_to_jpg($image_data);
    	}

    	if($image_size[0] > $this->options['max_size'] || $image_size[1] > $this->options['max_size'])
		{
			$resize = $this->resize_from_string($image_data, $image_size[0], $image_size[1]);
			$image_data = $resize['data'];
			$image_size[0] = $resize['size'][0];
			$image_size[1] = $resize['size'][1];
		}


		if($this->options['check_size'] !== false && $this->options['check_size'] > 0)
		{
			if($image_size[0] < $this->options['check_size'])
			{
				return self::ERROR_SIZE;	
			}
		}

		if($this->options['min_length'] !== false && $this->options['min_length'] > 0)
		{
			if (!isset($image_data[$this->options['min_length']])) {
				return self::ERROR_MIN_LENGTH;
			}
		}

		if($this->options['max_length'] !== false && $this->options['max_length'] > 0)
		{
			if(isset($image_data[$this->options['max_length'] + 1]))
			{
				return self::ERROR_MAX_LENGTH;
			}
		}

		if (empty($image_size['mime'])) {
			return self::ERROR_IMAGE;
		}

		$this->batchid = time();

		if($name === null)
		{
			$name = $i;
		}

		if($this->options['prefix_name'])
		{
			$name = $this->options['prefix_name'].$name;
		}
		
        $upload = $this->_requestUpload(
        	[
        		'name' => $name,
        		'size' => strlen($image_data),
        		'data' => $image_data
        	], 
        	0
        );

        if($upload['status'] === true)
        {
        	return $upload['url'];
        }
        else
        {
        	return self::ERROR_UPLOAD;
        }
	}

	public function linkUpload($urls = null, $name = null)
	{
		if(!$urls)
		{
			return self::ERROR_IMAGE;
		}

		if(!is_array($urls))
		{
			$urls = str_replace("\r", "", $urls);
			$urls = str_replace("\n\n", "\n", $urls);
			$urls = explode("\n", $urls ?? '');
		}

		$results = [];
		if($urls)
		{
			$i = 0;
			foreach ($urls as $url) {
				$result = null;
				$image_data = null;
				$image_size = [0, 0];
				$url = $this->reDrawUrl(trim($url));

				$retry = 0;
				do {
					$image_data = $this->curl_get_contents($url);
					try {
						$image_size = getimagesizefromstring($image_data);
						$result = $this->_upload($image_data, $image_size, $name, $i);
					}
					catch(Error $error) {
						$result = self::ERROR_IMAGE;
					}
					catch(Exception $exception) {
						$result = self::ERROR_IMAGE;
					}
					$retry++;
				} while((!$image_data || $result == self::ERROR_UPLOAD) && $retry < $this->options['re_download']);

				$results[$i] = $result;
				$i++;
			}
		}

		return $results;
	}

	public function dataUpload($data_image, $name = null)
	{
		if(!$data_image)
		{
			return self::ERROR_IMAGE;
		}

		$result = null;
		$image_size = [0, 0];
		$retry = 0;
		do {
			try {
				$image_size = getimagesizefromstring($data_image);
				$result = $this->_upload($data_image, $image_size, $name);
			}
			catch(Error $error) {
				$result = self::ERROR_IMAGE;
			}
			catch(Exception $exception) {
				$result = self::ERROR_IMAGE;
			}
			$retry++;
		} while($result == self::ERROR_UPLOAD && $retry < $this->options['re_download']);

		return $result;
	}

	public function formUpload($files = null, $name = null){

		if(!is_array($files) || !isset($files['tmp_name']))
		{
			return self::ERROR_IMAGE;
		}

		$isMultiple = is_array($files['tmp_name']) ? true : false;

		
		if(!$isMultiple)
		{
			$files = [
	            'name' => [
	            	$files['name']
	            ],
	            'type' => [
	            	$files['type']
	            ],
	            'tmp_name' => [
	            	$files['tmp_name']
	            ],
	            'error' => [
	            	$files['error']
	            ],
	            'size' => [
	            	$files['size']
	            ]
			];
		}

		$results = [];
		if($files)
		{
			for ($i = 0; $i < count($files['tmp_name']); $i++) {
				$tmpName = $files['tmp_name'][$i];
				$fileName = $files['name'][$i];

				if(is_null($name))
				{
					$name = preg_replace("/^(.*?)\.(.*){3,4}$/si", "$1", $fileName);
				}
				
				$result = null;
				$image_data = null;
				$image_size = [0, 0];

				if(!file_exists($tmpName))
				{
					$results[$i] = self::ERROR_IMAGE;
					continue;
				}

				$retry = 0;
				do {
					$image_data = file_get_contents($tmpName);
					try {
						$image_size = getimagesize($tmpName);
						$result = $this->_upload($image_data, $image_size, $name, $i);
					}
					catch(Error $error) {
						$result = self::ERROR_IMAGE;
					}
					catch(Exception $exception) {
						$result = self::ERROR_IMAGE;
					}
					$retry++;
				} while((!$image_data || $result == self::ERROR_UPLOAD) && $retry < $this->options['re_download']);
				$results[$i] = $result;
			}

		}

		return $results;
	}

	public function curl_get_contents($url = null, $header = [])
	{
		$referer = preg_replace("#^(.*?)\.(.*?)/(.*?)#si","$1.$2/", $url);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
		curl_setopt($ch, CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->options['timeout_download']);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_HTTPGET, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_REFERER, $referer);

		$out = curl_exec($ch);
		curl_close ($ch);
		return $out;
	}
}


?>