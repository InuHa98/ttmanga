<?php


class Auth {

    public static $id = null;
    public static $data = [];
    public static $isLogin = false;
    public static $limit_device = false;
	public static $permissions = null;
	
    public const UNLIMITED_DEVICE = -1;

    public const USERNAME_MAX_LENGTH = 25;
    public const USERNAME_MIN_LENGTH = 4;
    public const PASSWORD_MAX_LENGTH = 120;
    public const PASSWORD_MIN_LENGTH = 4;


    private const COOKIE_NAME = 'auth_session';
    public const INPUT_USERNAME = 'username';
    public const INPUT_NEW_USERNAME = 'new_username';
    public const INPUT_PASSWORD = 'password';
    public const INPUT_NEWPASSWORD = 'newpassword';
    public const INPUT_REPASSWORD = 'repassword';
    public const INPUT_EMAIL = 'email';
	public const INPUT_REGISTER_KEY = 'register-key';
    public const INPUT_STAYLOGIN = 'stay_login';

    function __construct($options= [])
    {
    	if(isset($options['limit_device']))
    	{
    		self::$limit_device = $options['limit_device'];
    	}
        self::checkLogin();
    }

    public static function encrypt_password($password = null)
    {
    	return password_hash(md5($password), PASSWORD_DEFAULT);
    }

    public static function verify_password($password = null, $verifyPassword = null)
    {
        if(is_null($verifyPassword) || !password_verify(md5($password), $verifyPassword))
        {
        	return false;
        }
        return true;
    }

    public static function current_auth_session()
    {
    	return isset($_COOKIE[self::COOKIE_NAME]) ? $_COOKIE[self::COOKIE_NAME] : (isset($_SESSION[self::COOKIE_NAME]) ? $_SESSION[self::COOKIE_NAME] : null);
    }

	public static function pass_auth_session()
	{
		return self::$data['username'].'@'.self::$data['id'];
	}

	public static function escape_session($session = null)
	{
		return str_replace('--session:', '', $session);
	}


	public static function join_session($session = null)
	{
		return '--session:'.$session;
	}

    private static function checkLogin($data = null)
    {

    	if(!is_null($data))
    	{
        	$user_name =  isset($data[self::INPUT_USERNAME]) ? $data[self::INPUT_USERNAME] : null;
        	$user_pass =  isset($data[self::INPUT_PASSWORD]) ? $data[self::INPUT_PASSWORD] : null;


		    if($user_name == "" || $user_pass == "")
		    {
		    	return false;
		    }

	        $dataUser = User::get([
	        	strpos($user_name, '@') ? 'email[~]' : 'username[~]' => $user_name,
	        	'LIMIT' => 1
	        ]);

	        $verifyPassword = isset($dataUser['password']) ? $dataUser['password'] : null;

			if (!strpos($user_pass, '$') && md5(md5($user_pass)) == $verifyPassword) {
				$_SESSION['user_reset_password'] = $dataUser;
				return Router::redirect('*', RouteMap::get('reset_password'));
			}

	        if(self::verify_password($user_pass, $verifyPassword) !== true)
	        {
	        	return false;
	        }

        }
        else
        {
        	$auth_session = self::current_auth_session();

        	if($auth_session == "")
        	{
        		return false;
        	}

	        $dataUser = User::get([
	        	'auth_session[~]' => '%'.self::join_session($auth_session)."\n%",
	        	'LIMIT' => 1
	        ]);

	        if(!$dataUser)
	        {
	        	return false;
	        }

        }
		self::$id = $dataUser['id'];
		self::$data = $dataUser;
		self::$isLogin = true;
		self::$data['settings'] = json_decode($dataUser['settings'], true);
		self::$limit_device = $dataUser['limit_device'];
		//self::$permissions = UserPermission::get();
		return true;
    }

	public static function isLogin()
	{
		return self::$isLogin === true ? true : false;
	}

	public static function login($username = "", $password = "", $stay = false)
	{

		if(self::isLogin())
		{
			return true;
		}

		$data = [
			'username' => $username,
			'password' => $password
		];

		if(self::checkLogin($data) === true)
		{

			$login_info = [
				'time' => time(),
				'username' => self::$data['username'],
				'user_agent' => self::get_user_agent(),
				'ip' => self::get_client_ip()
			];

			$auth_session = self::encrypt_login($login_info, self::pass_auth_session());

			if(self::$limit_device !== false && self::$data['limit_device'] != self::UNLIMITED_DEVICE)
			{
				if(self::$data['limit_device'] >= 2)
				{
					$limit_device = max(1, self::$data['limit_device']);
					$current_auth_session = explode("\n", trim(self::$data['auth_session'].''));	
					$current_auth_session = array_slice($current_auth_session, -($limit_device + 1), $limit_device - 1);
					$update_auth_session = [
						'auth_session' => implode("\n", $current_auth_session)."\n".self::join_session($auth_session)."\n"
					];
				}
				else
				{
					$update_auth_session = [
						'auth_session' => self::join_session($auth_session)."\n"
					];
				}
			}
			else
			{
				$update_auth_session = [
					'auth_session' => trim(self::$data['auth_session'].'')."\n".self::join_session($auth_session)."\n"
				];
			}


			if(User::update(['id' => self::$data['id']], $update_auth_session) !== false)
			{
				if($stay != false)
				{
					if(isset($_SESSION[self::COOKIE_NAME]))
					{
						unset($_SESSION[self::COOKIE_NAME]);
					}
					setcookie(self::COOKIE_NAME, $auth_session, time()+ 3600 * 24 * 365, '/');			
				}
				else
				{
					$_SESSION[self::COOKIE_NAME] = $auth_session;
			        setcookie(self::COOKIE_NAME, '', time()-1000);
			        setcookie(self::COOKIE_NAME, '', time()-1000, '/');	
				}

				return true;
			}
		}
		return false;
	}


	public static function delete_auth_session($auth_session = null)
	{
		if($auth_session)
		{
			$delete_auth_session = str_replace(self::join_session($auth_session)."\n", '', self::$data['auth_session']);
		}
		else
		{
			$delete_auth_session = '';
		}

		if(User::update(['id' => self::$data['id']], ['auth_session' => $delete_auth_session]))
		{
			self::$data['auth_session'] = $delete_auth_session;
			return true;
		}
		return false;
	}

	public static function logout()
	{

		$auth_session = self::current_auth_session();

		if($auth_session)
		{
			if(self::$limit_device !== false)
			{
				$delete_auth_session = self::delete_auth_session();
			}
			else
			{
				$delete_auth_session = self::delete_auth_session($auth_session);
			}
			

			if($delete_auth_session === true)
			{

				if(isset($_SESSION[self::COOKIE_NAME]))
				{
					unset($_SESSION[self::COOKIE_NAME]);
				}
				
				if (isset($_SERVER['HTTP_COOKIE']))
				{
				    $cookies = explode(';', $_SERVER['HTTP_COOKIE'] ?? '');
				    foreach($cookies as $cookie) {
				        $parts = explode('=', $cookie ?? '');
				        $name = trim($parts[0].'');
				        if($name !== 'PHPSESSID')
				        {
					        setcookie($name, '', time()-1000);
					        setcookie($name, '', time()-1000, '/');		        	
				        }

				    }
				}
				return true;
			}

		}

		return false;
	}

	public static function get_user_agent()
	{
	    $user_agent = 'UNKNOWN';
	    if(isset($_SERVER["HTTP_USER_AGENT"]))
	    {
	        $user_agent = $_SERVER["HTTP_USER_AGENT"];
	    }
	    return $user_agent;
	}

	public static function get_client_ip()
	{
	    $ipaddress = '';
	    if(isset($_SERVER['HTTP_CLIENT_IP']))
	    {
	        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	    }
	    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
	    {
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	    }
	    else if(isset($_SERVER['HTTP_X_FORWARDED']))
	    {
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	    }
	    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
	    {
	        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	    }
	    else if(isset($_SERVER['HTTP_FORWARDED']))
	    {
	        $ipaddress = $_SERVER['HTTP_FORWARDED'];
	    }
	    else if(isset($_SERVER['REMOTE_ADDR']))
	    {
	        $ipaddress = $_SERVER['REMOTE_ADDR'];
	    }
	    else
	    {
	        $ipaddress = 'UNKNOWN';
	    }
	    return $ipaddress;
	}

	public static function encrypt_login($data_array = null, $key = null)
	{
	    $method = 'aes-128-cbc';
	    $data = json_encode($data_array);
	    $ivSize = openssl_cipher_iv_length($method);
	    $iv = openssl_random_pseudo_bytes($ivSize);
	    $encrypted = openssl_encrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv);
	    return base64_encode(json_encode([
	        "iv" => base64_encode($iv),
	        "data" => base64_encode($encrypted)
	    ]));
	}

	public static function decrypt_login($data_encrypt = null, $key = null)
	{
	    $data_encrypt = json_decode(base64_decode($data_encrypt), true);
	    $method = 'aes-128-cbc';
	    $data = isset($data_encrypt['data']) ? base64_decode($data_encrypt['data']) : null;
	    $iv = isset($data_encrypt['iv']) ? base64_decode($data_encrypt['iv']) : null;
	    $decrypted = openssl_decrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv); 
	    return json_decode($decrypted, true);
	}

	public static function check_type_username($str = null)
	{
	    return preg_match('/^(?![_.])(?!.*[_.]{2})[a-zA-Z0-9._]+(?<![_.])$/', $str);
	}

	public static function check_type_email($str = null)
	{
	    return preg_match("#^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$#si", $str);
	}

	public static function check_length_username($str = null)
	{
	    if(mb_strlen($str) < self::PASSWORD_MIN_LENGTH || mb_strlen($str) > self::PASSWORD_MAX_LENGTH)
	    {
	        return false;
	    }
	    return true;
	}

	public static function check_length_password($str = null)
	{
	    if(mb_strlen($str) < self::USERNAME_MIN_LENGTH || mb_strlen($str) > self::USERNAME_MAX_LENGTH)
	    {
	        return false;
	    }
	    return true;
	}

	public static function trigger_data($key, $value)
	{
		if(isset(self::$data[$key]))
		{
			return self::$data[$key] = $value;
		}
		return false;
	}
}


?>