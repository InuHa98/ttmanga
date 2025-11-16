<?php

ignore_user_abort(false);

class toolLeechController {


	public const BLOCK_GET_MANGA = 'get-manga';
	public const BLOCK_GET_CHAPTER = 'get-chapter';
	public const BLOCK_SAVE_CHAPTER = 'save-chapter';
	public const BLOCK_REUPLOAD_IMAGE = 'reupload-image';

	public const INPUT_ID = 'id';
	public const INPUT_LINK = 'link';
	public const INPUT_IMAGES = 'images';
	public const INPUT_NAME = 'name';
	public const INPUT_REFERER = 'referer';

	public const REFERER_MANGADEX = 1;
	public const REFERER_TRUYENQQ = 2;
	public const REFERER_MTO = 3;
	public const REFERER_FOXTRUYEN = 4;
	public const REFERER_HANGTRUYEN = 5;
	public const REFERER_CMANGA = 6;
	public const REFERER_MINOTRUYEN = 7;
	public const REFERER_TRUYENQQCOMVN = 8;


	private static function result($code, $message = null, $data = null)
	{
		exit(json_encode(is_array($code) ? $code : [
			'code' => $code,
			'message' => $message,
			'data' => $data
		], JSON_PRETTY_PRINT));
	}

	public function index($block)
	{

		if (!Auth::$data || !UserPermission::has('tool_leech')) {
			return self::result(403, 'Truy cập bị từ chối');  #edit_lang
		}

		switch($block) {

			case self::BLOCK_GET_MANGA:
				return self::block_get_manga();

			case self::BLOCK_GET_CHAPTER:
				return self::block_get_chapter();

			case self::BLOCK_SAVE_CHAPTER:
				return self::block_save_chapter();

			case self::BLOCK_REUPLOAD_IMAGE:
				return self::block_reupload_image();

		}
	}

	private static function getRerferer($link) {
		if (!$link) {
			return false;
		}

		if(preg_match('#^https?://(?:.*?\.)?truyenvua\.(?:.*?)/#', $link)) {
			return 'https://truyenqqgo.com/';
		}

		if(preg_match('#^https?://(.*?)\.static3t\.com/#', $link)) {
			return 'https://truyenqq.com.vn/';
		}

		if(preg_match('#^https?://(?:.*?\.)?(tintruyen|hinhgg)\.(?:.*?)/#', $link)) {
			return 'https://foxtruyen.com/';
		}

		if(preg_match('#^https?://(?:.*?\.)?mbwww\.(?:.*?)/#', $link)) {
			return 'https://mto.to/';
		}

		if(preg_match('#^https?://(?:.*?\.)?mangadex\.(?:.*?)/#', $link)) {
			return 'https://mangadex.org/';
		}

		if(preg_match('#^https?://(?:.*?\.)?(hangtruyen|htrcdn)\.(?:.*?)/#', $link)) {
			return 'https://hangtruyen.top/';
		}

		if(preg_match('#^https?://(?:.*?\.)?(cmanga|mideman)\.(?:.*?)/#', $link)) {
			return 'https://cmangax7.com/';
		}

	} 

	private static function getHtmlContainer($html, $xpath) {
		$dom = new DOMDocument();
		libxml_use_internal_errors(true);
		$dom->loadHTML($html);
		libxml_clear_errors();
		$xp = new DOMXPath($dom);
		$nodes = $xp->query($xpath);
		return $nodes->length > 0 ? $dom->saveHTML($nodes[0]) : '';
	}

	public static function curl_get_content($url, $referer = null, $cookie = false, $txtCookie = null) {

		if($url == '') {
			return false;
		}
			
		if (!$referer) {
			$referer = preg_replace("#^(.*?)\.(.*?)/(.*?)$#si", "$1.$2/", $url);
			$referer = preg_replace("#^(.*?)\://(.*?)\.(.*?)\.(.*?)/$#si", "$1://$3.$4/", $referer);			
		}

		$ch = curl_init();

		$header = [
			// 'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
			// 'accept-language: vi-VN,vi;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5',
			// 'cache-control: no-cache',
			// 'dnt: 1',
			// 'pragma: no-cache',
		];
		if($txtCookie) {
			$header[] = "Cookie: ".$txtCookie;
		}

		curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36");
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_HEADER, $cookie == false ? 0 : 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

		curl_setopt($ch, CURLOPT_HTTPGET, true);
		curl_setopt($ch, CURLOPT_REFERER, $referer);
		$out = curl_exec($ch);
		curl_close($ch);
		if($cookie != false){
			preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $out, $matches);
			return $matches[1];
		}
		return $out;
	}

	public static function curl_post_content($url, $data = '', $header = [], $referer = false, $cookie = false){

		if($url == '') {
			return false;
		}

		if(!$referer){
			$referer = preg_replace("#^(.*?)\.(.*?)/(.*?)$#si", "$1.$2/", $url);
			$referer = preg_replace("#^(.*?)\://(.*?)\.(.*?)\.(.*?)/$#si", "$1://$3.$4/", $referer);        
		}

		if ($cookie) {
			$cookies = is_array($cookie) ? implode('; ', $cookie) : $cookie;
			$header[] = "Cookie: ".$cookies;			
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		if($data) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36");
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT ,20);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,10);

		curl_setopt($ch, CURLOPT_REFERER, $referer);
		$out = curl_exec($ch);
		curl_close($ch);
		return $out;
	}

	private static function decryptoMinoTruyen($password, $encrypted) {
		$ciphertext_raw = base64_decode($encrypted);
		$salted = substr($ciphertext_raw, 0, 8);
		if ($salted !== "Salted__") {
			return false;
		}
		$salt = substr($ciphertext_raw, 8, 8);
		$ciphertext_data = substr($ciphertext_raw, 16);
		$concatenatedHashes = '';
		$prev = '';
		while (strlen($concatenatedHashes) < 48) {
			$prev = md5($prev . $password . $salt, true);
			$concatenatedHashes .= $prev;
		}
		$key = substr($concatenatedHashes, 0, 32);
		$iv  = substr($concatenatedHashes, 32, 16);

		$plaintext = openssl_decrypt($ciphertext_data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
		return $plaintext ? json_decode($plaintext, true) : null;
	}

	private function block_get_chapter() {
		$link = trim(Request::post(self::INPUT_LINK, ''));
		$response = [
			'code' => 429,
			'message' => 'Link chapter không chính xác hoặc chưa được hỗ trợ' #edit_lang
		];

		//mto
		if(preg_match('#^https?://(?:.*?\.)?(?:mto)\.(?:.*?)/chapter/([0-9]+)(?:/[0-9]+)?$#', $link)) {
			$html = self::curl_get_content($link);
			preg_match('#const imgHttps = \[(.*)\]#', $html, $m);
			$data = [];
			if (empty($m[1])) {
				$response = [
					'code' => 429,
					'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau ít phút', #edit_lang
					'data' => null
				];
			} else {
				$response = [
					'code' => 200,
					'message' => self::REFERER_MTO,
					'data' => json_decode('['.$m[1].']', true)
				];
			}
		}

		//foxtruyen
		else if(preg_match('#^https?://(?:.*?\.)?(?:foxtruyen|foxtruyen\w{2})\.(?:.*?)/truyen-tranh/(?:.*?)-chap-([0-9]+(?:-[0-9]+)?)\.html$#', $link)) {
			$html = self::curl_get_content($link);
			preg_match_all('#<img[^>]+class="lazy"[^>]+src="([^"]+)"#i', $html, $m);
			if (empty($m[1])) {
				$response = [
					'code' => 429,
					'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau ít phút', #edit_lang
					'data' => null
				];
			} else {
				$response = [
					'code' => 200,
					'message' => self::REFERER_FOXTRUYEN,
					'data' => $m[1]
				];
			}
		}


		//truyenqq
		else if(preg_match('#^https?://(?:.*?\.)?(?:truyenqq|truyenqq\w{2})\.(?:.*?)/truyen-tranh/(?:.*?)-chap-([0-9]+(?:-[0-9]+)?)\.html$#', $link)) {
			$html = self::curl_get_content($link);
			preg_match_all('#<img[^>]+class="lazy"[^>]+src="([^"]+)"#i', $html, $m);
			if (empty($m[1])) {
				$response = [
					'code' => 429,
					'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau ít phút', #edit_lang
					'data' => null
				];
			} else {
				$response = [
					'code' => 200,
					'message' => self::REFERER_TRUYENQQ,
					'data' => $m[1]
				];
			}
		}

		//truyenqq.com.vn
		else if(preg_match('#^https?://(truyenqq\.com\.vn|truyenqqgo\.net)/(?:[^/]+)/chapter-([0-9]+)$#', $link)) {
			$link = str_replace('truyenqq.com.vn', 'truyenqqgo.net', $link);
			$html = self::getHtmlContainer(self::curl_get_content($link), "//div[@class='reading-content']");
			preg_match_all('#<img[^>]+class="lazy"[^>]+data-src="([^"]+)"#i', $html, $m);
			if (empty($m[1])) {
				$response = [
					'code' => 429,
					'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau ít phút', #edit_lang
					'data' => null
				];
			} else {
				$response = [
					'code' => 200,
					'message' => self::REFERER_TRUYENQQCOMVN,
					'data' => $m[1]
				];
			}
		}

		//mangadex
		else if(preg_match('#^https?://(?:.*?\.)?mangadex\.(?:.*?)/chapter/([^/]+)(?:/[0-9]+)?$#', $link, $m)) {
			$id_chapter = isset($m[1]) ? $m[1] : 0;
			if ($id_chapter) {
				$fetch = self::curl_get_content('https://api.mangadex.org/at-home/server/'.$id_chapter.'?forcePort443=false');
				$result = json_decode($fetch, true);
				$data = [];

				$baseUrl = $result['baseUrl'] ?? null;
				$hash = $result['chapter']['hash'] ?? null;
			

				if (!$baseUrl || !$hash) {
					$response = [
						'code' => 429,
						'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau ít phút', #edit_lang
						'data' => null
					];
				} else {
					foreach($result['chapter']['data'] as $page) {
						$data[] = $baseUrl.'/data/'.$hash.'/'.$page;
					}
					$response = [
						'code' => 200,
						'message' => self::REFERER_MANGADEX,
						'data' => $data
					];
				}
			}
		}


		//hangtruyen
		else if(preg_match('#^https?://(?:.*?\.)?(?:hangtruyen|hangtruyen\w{2})\.(?:.*?)/truyen-tranh/(?:[^/]+)/chapter-([0-9]+)$#', $link)) {
			$html = self::curl_get_content($link);
			preg_match('#const chapterDetail = \{(.*)\};#si', $html, $m);
			$data = [];
			if (empty($m[1])) {
				$response = [
					'code' => 429,
					'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau ít phút', #edit_lang
					'data' => null
				];
			} else {
				$json = json_decode('{'.$m[1].'}', true);
				$images = $json['images'];
				usort($images, function($a, $b) {
					return $a['index'] <=> $b['index'];
				});

				$response = [
					'code' => 200,
					'message' => self::REFERER_HANGTRUYEN,
					'data' => array_column($images, 'path')
				];
			}
		}

		//minotruyen
		else if(preg_match('#^https?://(?:.*?\.)?(?:minotruyen|minotruyen\w{2})\.(?:.*?)/manga/truyen/(?:[^/]+)/chapter(?:.*)-([0-9]+)$#', $link)) {
			$html = self::curl_get_content($link);
			preg_match('#<script src="/_next/static/chunks/app/[^/]+/.+?/layout-([^.]+)\.js" async=""></script>#si', $html, $m);
			if (!empty($m[1])) {
				$link_layout = 'https://minotruyengg.xyz/_next/static/chunks/app/%5Bcontent%5D/(root)/(client)/layout-'.$m[1].'.js';
				$layout_js = self::curl_get_content($link_layout);
				preg_match('#NEXT_PUBLIC_SECRET_DATA_CHAPTER\:"([^"]+)"#si', $layout_js, $m);
				$secret_key = isset($m[1]) ? $m[1] : null;
				if ($secret_key) {
					preg_match('#self\.__next_f\.push\(\[(?:[0-9]),"(?:[^\:]+):([^"]{500,})"\]\)</script>#si', $html, $m);
					$encrypted = isset($m[1]) ? $m[1] : null;
					if ($encrypted) {
						$decrypted = self::decryptoMinoTruyen($secret_key, $encrypted);
						if (!$decrypted) {
							$response = [
								'code' => 429,
								'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau ít phút', #edit_lang
								'data' => null
							];
						} else {
							$response = [
								'code' => 200,
								'message' => self::REFERER_MINOTRUYEN,
								'data' => array_column($decrypted[0]['content'], 'imageUrl')
							];
						}
					}
				}
			} else {
				$response = [
					'code' => 429,
					'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau ít phút', #edit_lang
					'data' => null
				];
			}
		}

		return self::result($response);	
	}

	private function block_get_manga() {
		$link = trim(Request::post(self::INPUT_LINK, ''));
		$response = [
			'code' => 429,
			'message' => 'Link chapter không chính xác hoặc chưa được hỗ trợ' #edit_lang
		];

		//mto
		if(preg_match('#^https?://(?:.*?\.)?(?:mto)\.(?:.*?)/series/([0-9]+)/(.*?)$#', $link)) {
			$html = self::getHtmlContainer(self::curl_get_content($link), "//div[@class='main']");

			preg_match_all('#<a class="visited chapt" href="(.*?)">(?:\s*)<b>(.*?)</b>(?:\s*)</a>#si', $html, $m);
			$lst_link = isset($m[1]) ? $m[1] : [];
			$lst_name = isset($m[2]) ? $m[2] : [];
			
			if (!$lst_link || !$lst_name) {
				$response = [
					'code' => 429,
					'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau ít phút', #edit_lang
					'data' => null
				];
			} else {
				$data = [];
				$i = 0;
				foreach($lst_link as $link) {
					$data[] = [
						'name' => trim($lst_name[$i] ?? ''),
						'link' => 'https://mto.to'.$link
					];
					$i++;
				}
				$response = [
					'code' => 200,
					'message' => self::REFERER_MTO,
					'data' => $data
				];
			}
		}

		//foxtruyen
		else if(preg_match('#^https?://(?:.*?\.)?(?:foxtruyen|foxtruyen\w{2})\.(?:.*?)/truyen-tranh/(?:.*?)-([0-9]+)\.html$#', $link)) {
			$html = self::getHtmlContainer(self::curl_get_content($link), "//ul[@class='list_chap']");

			preg_match_all('#<a(?:\s+[^>]*)?href="(.*?)"(?:[^>]*)>(.*?)</a>#si', $html, $m);
			$lst_link = isset($m[1]) ? $m[1] : [];
			$lst_name = isset($m[2]) ? $m[2] : [];

			if (!$lst_link || !$lst_name) {
				$response = [
					'code' => 429,
					'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau ít phút', #edit_lang
					'data' => null
				];
			} else {
				$data = [];
				$i = 0;
				foreach($lst_link as $link) {
					$data[] = [
						'name' => trim($lst_name[$i] ?? ''),
						'link' => $link
					];
					$i++;
				}
				$response = [
					'code' => 200,
					'message' => self::REFERER_FOXTRUYEN,
					'data' => $data
				];
			}
		}

		//truyenqq
		else if(preg_match('#^https?://(?:.*?\.)?(?:truyenqq|truyenqq\w{2})\.(?:.*?)/truyen-tranh/(?:.*?)-([0-9]+)$#', $link)) {
			$html = self::getHtmlContainer(self::curl_get_content($link), "//div[@class='list_chapter']");

			preg_match_all('#<a(?:\s+[^>]*)?href="(.*?)"(?:[^>]*)>(.*?)</a>#si', $html, $m);
			$lst_link = isset($m[1]) ? $m[1] : [];
			$lst_name = isset($m[2]) ? $m[2] : [];

			if (!$lst_link || !$lst_name) {
				$response = [
					'code' => 429,
					'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau ít phút', #edit_lang
					'data' => null
				];
			} else {
				$data = [];
				$i = 0;
				foreach($lst_link as $link) {
					$data[] = [
						'name' => trim($lst_name[$i] ?? ''),
						'link' => 'https://truyenqqgo.com'.$link
					];
					$i++;
				}
				$response = [
					'code' => 200,
					'message' => self::REFERER_TRUYENQQ,
					'data' => $data
				];
			}
		}

		//truyenqq.com.vn
		else if(preg_match('#^https?://(truyenqq\.com\.vn|truyenqqgo\.net)/(.*?)$#', $link)) {
			$link = str_replace('truyenqq.com.vn', 'truyenqqgo.net', $link);
			$html = self::getHtmlContainer(self::curl_get_content($link), "//div[@class='reading-list']");

			preg_match_all('#<a(?:\s+[^>]*)?href="(.*?)"(?:[^>]*)>(.*?)</a>#si', $html, $m);
			$lst_link = isset($m[1]) ? $m[1] : [];
			$lst_name = isset($m[2]) ? $m[2] : [];

			if (!$lst_link || !$lst_name) {
				$response = [
					'code' => 429,
					'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau ít phút', #edit_lang
					'data' => null
				];
			} else {
				$data = [];
				$i = 0;
				foreach($lst_link as $link) {
					$data[] = [
						'name' => trim($lst_name[$i] ?? ''),
						'link' => 'https://truyenqqgo.net'.$link
					];
					$i++;
				}
				$response = [
					'code' => 200,
					'message' => self::REFERER_TRUYENQQCOMVN,
					'data' => $data
				];
			}
		}

		//hangtruyen
		else if(preg_match('#^https?://(?:.*?\.)?(?:hangtruyen|hangtruyen\w{2})\.(?:.*?)/truyen-tranh/(?:[^/]+)$#', $link)) {
			$html = self::getHtmlContainer(self::curl_get_content($link), "//div[@class='list-chapters']");

			preg_match_all('#<a(?:\s+[^>]*)?href="(.*?)"(?:[^>]*)>(.*?)</a>#si', $html, $m);
			$lst_link = isset($m[1]) ? $m[1] : [];
			$lst_name = isset($m[2]) ? $m[2] : [];

			if (!$lst_link || !$lst_name) {
				$response = [
					'code' => 429,
					'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau ít phút', #edit_lang
					'data' => null
				];
			} else {
				$data = [];
				$i = 0;
				foreach($lst_link as $link) {
					$data[] = [
						'name' => trim(strip_tags($lst_name[$i]) ?? ''),
						'link' => 'https://hangtruyen.top'.$link
					];
					$i++;
				}
				$response = [
					'code' => 200,
					'message' => self::REFERER_HANGTRUYEN,
					'data' => $data
				];
			}
		}

		//minotruyen
		else if(preg_match('#^https?://(?:.*?\.)?(?:minotruyen|minotruyen\w{2})\.(?:.*?)/manga/truyen/(?:[^/]+)-([0-9]+)$#', $link, $m)) {
			$id_manga = isset($m[1]) ? $m[1] : 0;
			if ($id_manga) {
				$fetch = self::curl_get_content('https://api.cloudkk.art/api/chapters/'.$id_manga.'?order=desc&take=5000');
				$result = json_decode($fetch, true);

				$chapters = $result['chapters'] ?? null;
				if (!$chapters) {
					$response = [
						'code' => 429,
						'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau ít phút', #edit_lang
						'data' => null
					];
				} else {
					$data = [];
					$i = 0;
					foreach($chapters as $chapter) {
						$data[] = [
							'name' => 'Chapter '.$chapter['num'].($chapter['title'] ? ' - '.$chapter['title'] : ''),
							'link' => trim($link, '/').'/chapter-'.$chapter['num'].'-'.$chapter['chapterNumber']
						];
						$i++;
					}
					$response = [
						'code' => 200,
						'message' => self::REFERER_MINOTRUYEN,
						'data' => $data
					];
				}
			}
		}

		//mangadex
		else if(preg_match('#^https?://(?:.*?\.)?mangadex\.(?:.*?)/title/([^/]+)(?:/.*)?$#', $link, $m)) {
			$id_manga = isset($m[1]) ? $m[1] : 0;
			if ($id_manga) {
				$limit = 100;
				$offset = 0;
				$data = [];
				while(true) {
					$fetch = self::curl_get_content('https://api.mangadex.org/manga/'.$id_manga.'/feed?translatedLanguage[]=vi&limit='.$limit.'&order[volume]=desc&order[chapter]=desc&offset='.$offset);
					$json = json_decode($fetch, true);
					$data_chapter = isset($json['data']) ? $json['data'] : [];
					if (!$data_chapter) {
						break;
					}

					foreach($data_chapter as $o) {
						if ($o['type'] == 'chapter' && $o['attributes']['translatedLanguage'] == 'vi') {
							$data[] = [
								'name' => 'Chapter '.$o['attributes']['chapter'].($o['attributes']['title']? ' - '.$o['attributes']['title'] : ''),
								'link' => 'https://mangadex.org/chapter/'.$o['id']
							];
						}
					}
					$offset = $json['offset'] + $limit;
					if ($offset >= $json['total']) {
						break;
					}
				}

				if (!$data) {
					$response = [
						'code' => 429,
						'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau ít phút', #edit_lang
						'data' => null
					];
				} else {
					$response = [
						'code' => 200,
						'message' => self::REFERER_MANGADEX,
						'data' => $data
					];
				}
			}
		}

		return self::result($response);	
	}

	private function block_save_chapter() {
		$manga_id = intval(Request::post(self::INPUT_ID, 0));
		$images = Request::post(self::INPUT_IMAGES, []);
		$name = trim(Request::post(self::INPUT_NAME, ''));

		if (!is_array($images)) {
			$images = json_decode($images, true);
		}

		$manga = Manga::get(['id' => $manga_id]);
		if (!$manga) {
			return self::result(404, 'Không tìm thấy truyện');
		}

		if ($name == '') {
			return self::result(429, 'Tên truyện không được bỏ trống');
		}

		if (!$images) {
			return self::result(429, 'Link ảnh không được bỏ trống');
		}

		$chapter = Chapter::get(['name[~]' => $name, 'manga_id' => $manga['id']]);
		if ($chapter) {
			if (Chapter::update($chapter['id'], ['image' => implode(Chapter::SEPARATOR, $images)]) > 0) {
				return self::result(200, 'Cập nhật thành công', 'update');
			} else {
				return self::result(429, 'Cập nhật thất bại');
			}
		}

		if (Chapter::create([
			'name' => $name,
			'images' => $images,
			'index' => Chapter::POSITION_TOP,
			'manga_id' => $manga['id']
		]) > 0) {
			return self::result(200, 'Thêm mới thành công', 'add');
		} else {
			return self::result(429, 'Thêm mới thất bại');
		}
	}

	private function block_reupload_image() {
		if (connection_aborted()) {
			exit;
		}

		$name = trim(Request::post(self::INPUT_NAME, 0));
		$link = trim(Request::post(self::INPUT_LINK, ''));
		$referer = intval(Request::post(self::INPUT_REFERER, 0));

		if ($name == '' || $link == '') {
			return self::result(429, 'Không tìm thấy dữ liệu ảnh'); #edit_lang
		}

		$dataImage = null;
		switch($referer) {

			case self::REFERER_MANGADEX:
				$dataImage = self::curl_get_content($link, 'https://mangadex.org/');
				break;

			case self::REFERER_TRUYENQQ:
				$dataImage = self::curl_get_content($link, 'https://truyenqqgo.com/');
				break;

			case self::REFERER_MTO:
				$dataImage = self::curl_get_content($link, 'https://mto.to/');
				break;

			case self::REFERER_FOXTRUYEN:
				$dataImage = self::curl_get_content($link, 'https://foxtruyen.com/');
				break;

			case self::REFERER_HANGTRUYEN:
				$dataImage = self::curl_get_content($link, 'https://hangtruyen.top/');
				break;

			case self::REFERER_CMANGA:
				$dataImage = self::curl_get_content($link, 'https://cmangax7.com/');
				break;

			default:
				$dataImage = self::curl_get_content($link, self::getRerferer($link));
				break;
		}

		$ajax = new ajaxController();
		return $ajax->upload_image($dataImage, $name);
	}

}





?>