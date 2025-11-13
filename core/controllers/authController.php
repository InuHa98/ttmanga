<?php


class authController
{

	private const FORGOT_PASSWORD_EXPIRED = 30; // thời gian tồn tại yêu cầu lấy lại mật khẩu (phút)

	public const SUBMIT_NAME = 'submit'; 

	public function __construct()
	{
		Language::load('auth.lng');
	}

	public function login()
	{
		if(Auth::isLogin())
		{
			return $this->goToHome();
		}

		$title = lang('login', 'title');

		$username = trim(Request::post(Auth::INPUT_USERNAME, ''));
		$password = trim(Request::post(Auth::INPUT_PASSWORD, ''));
		$stay     = boolval(Request::post(Auth::INPUT_STAYLOGIN, false));
		$referer = Request::server('HTTP_REFERER', APP_URL);

		$success = null;
		$error = null;

		if(isset($_POST[self::SUBMIT_NAME]))
		{
			
			Role::setup_default_role();

			if(Auth::login($username, $password, $stay) == true)
			{
				if(Auth::$data['user_ban'] != User::IS_ACTIVE) {
					$error = 'Tài khoản của bạn đã bị cấm';
					User::update(Auth::$data['id'], [
						'auth_session' => ''
					]);
				} else {
					$success = lang('login', 'success_login');
					return Router::redirect('*', $referer);
				}
			}
			else
			{
				$error = lang('login', 'error_login');
			}
		}
		return View::render_theme('auth.login', compact('title', 'username', 'password', 'stay', 'referer', 'success', 'error'));
	}

	public function register()
	{
		if(Auth::isLogin())
		{
			return $this->goToHome();
		}

		$title = lang('register', 'title');
		
		$username   = trim(Request::post(Auth::INPUT_USERNAME, ''));
		$password   = trim(Request::post(Auth::INPUT_PASSWORD, ''));
		$rePassword = trim(Request::post(Auth::INPUT_REPASSWORD, ''));
		$email      = trim(Request::post(Auth::INPUT_EMAIL, ''));
		$registerKey = trim(Request::post(Auth::INPUT_REGISTER_KEY, Request::get(Auth::INPUT_REGISTER_KEY, '')));

		$success = null;
		$error = null;
		$code_error = null;

		if(isset($_POST[self::SUBMIT_NAME]))
		{

			Role::setup_default_role();

			if(!Auth::check_length_username($username))
			{
				$code_error = 'error_username_length';
			}
			else if(!Auth::check_type_username($username))
			{
				$code_error = 'error_username_char';
			}
			else if(User::has(['username[~]' => $username]))
			{
				$code_error = 'error_username_exist';
			}
			else if(!Auth::check_length_password($password))
			{
				$code_error = 'error_password_length';
			}
			else if($password !== $rePassword)
			{
				$code_error = 'error_password_reinput';
			}
			else if(!Auth::check_type_email($email))
			{
				$code_error = 'error_email_format';
			}
			else if(User::has(['email[~]' => $email]))
			{
				$code_error = 'error_email_exist';
			}
			else
			{
				$register_key = RegisterKey::get(['key' => $registerKey]);
				if(!$register_key)
				{
					$code_error = 'error_registerkey_not_exist';
				}
				else if(RegisterKey::validate($register_key) != true)
				{
					$code_error = 'error_registerkey_is_used';
				}
			}


			if($code_error != null)
			{
				switch($code_error)
				{
					case 'error_username_length':
						$error = lang('register', $code_error, [
							'min' => Auth::USERNAME_MIN_LENGTH,
							'max' => Auth::USERNAME_MAX_LENGTH
						]);
						break;
					case 'error_password_length':
						$error = lang('register', $code_error, [
							'min' => Auth::PASSWORD_MIN_LENGTH,
							'max' => Auth::PASSWORD_MAX_LENGTH
						]);
						break;
					default:
						$error = lang('register', $code_error);
						break;
				}
			}
			else
			{
				if(User::create([
					'username' => $username,
					'password' => $password,
					'email' => $email
				]) == true)
				{
					$quantity = $register_key['quantity'] - 1;
					if ($quantity < 0) {
						$quantity = 0;
					}
					if (RegisterKeyUserRegister::create($register_key['id'], User::$insert_id)) {
						RegisterKey::update($register_key['id'], [
							'status' => $quantity < 1 ? RegisterKey::STATUS_DIE : RegisterKey::STATUS_LIVE,
							'quantity' => $quantity
						]);
					}
					$success = lang('register', 'success_register');
				}
				else
				{
					$error = lang('system', 'default_error');
				}
			}
		}
		return View::render_theme('auth.register', compact('title', 'username', 'password', 'rePassword', 'email', 'registerKey', 'success', 'error', 'code_error'));
	}

	public function forgot_password_request()
	{
		if(Auth::isLogin())
		{
			return $this->goToHome();
		}

		$title = lang('forgot_password', 'title');

		$email = trim(Request::post(Auth::INPUT_EMAIL, ''));

		$success = null;
		$error = null;
		$code_error = null;



		if(isset($_POST[self::SUBMIT_NAME]))
		{
			
			if($email == "")
			{
				$code_error = 'error_email_empty';
			}
			else if(!Auth::check_type_email($email))
			{
				$code_error = 'error_email_format';
			}
			else if(!$user = User::get(['email' => $email]))
			{
				$code_error = 'error_email_not_exist';
			}

			if($code_error != null)
			{
				$error = lang('forgot_password', $code_error);
			}
			else
			{
				$key = md5(uniqid($email ? $email : time(), true));
				$time = time() + (self::FORGOT_PASSWORD_EXPIRED * 60);
				$url = RouteMap::join('/'.$key, 'forgot_password');
								
		    	if(User::update($user['id'], [
			        'forgot_key' => $key,
			        'forgot_time' => $time
			    ]) > 0)
			    {
					$mailer = new Mailer();

					$subject = lang('forgot_password', 'mail_request_title', ['title' => env(DotEnv::APP_NAME)]);
					$message = lang('forgot_password', 'mail_request_content', [
						'username' => $user['username'],
						'expired' => self::FORGOT_PASSWORD_EXPIRED,
						'time' => date('H:i d/m/Y', $time),
						'url' => $url
					]);
					$footer = lang('forgot_password', 'mail_request_footer', ['url' => $url]);

					if($mailer::send($email, $subject, $mailer::template($message, ["footer" => $footer])) == true)
					{
						$success = lang('forgot_password', 'success_send_request', ['email' => $email, 'time' => self::FORGOT_PASSWORD_EXPIRED]);
					}
					else
					{
						$error = lang('system', 'default_error');
					}
			    }
			}
		}
		return View::render_theme('auth.forgot_password_request', compact('title', 'email', 'success', 'error', 'code_error'));
	}

	public function forgot_password_change($key = null)
	{
		if(Auth::isLogin())
		{
			return $this->goToHome();
		}

		$user = User::get(['forgot_key' => $key]);

		if(!$user || $user['forgot_time'] < time())
		{
			return $this->goToHome();
		}

		$title = lang('forgot_password', 'title');

		$password = trim(Request::post(Auth::INPUT_PASSWORD, ''));
		$rePassword = trim(Request::post(Auth::INPUT_REPASSWORD, ''));

		$success = null;
		$error = null;
		$code_error = null;


		if(isset($_POST[self::SUBMIT_NAME]))
		{
			if(!Auth::check_length_password($password))
			{
				$code_error = 'error_password_length';
				$error = lang('forgot_password', $code_error, [
					'min' => Auth::PASSWORD_MIN_LENGTH,
					'max' => Auth::PASSWORD_MAX_LENGTH
				]);
			}
			else if($password !== $rePassword)
			{
				$code_error = 'error_password_reinput';
				$error = lang('forgot_password', $code_error);
			}

			if($code_error == null)
			{
		    	if(User::update($user['id'], [
			        'forgot_key' => '',
			        'forgot_time' => 0,
			        'password' => Auth::encrypt_password($password)
			    ]) > 0)
			    {
			    	$success = lang('forgot_password', 'success_change_password');
			    }
			    else
			    {
			    	$error = lang('system', 'default_error');
			    }
			}
		}
		return View::render_theme('auth.forgot_password_verify', compact('title', 'password', 'rePassword', 'success', 'error', 'code_error'));
	}

	public function reset_password()
	{
		$user = isset($_SESSION['user_reset_password']) ? $_SESSION['user_reset_password'] : null;
		if(Auth::isLogin() || !$user)
		{
			return $this->goToHome();
		}

		$title = 'Cập nhật lại thông tin tài khoản'; #edit_lang

		$username = trim(Request::post(Auth::INPUT_USERNAME, $user['username']));
		$email = trim(Request::post(Auth::INPUT_EMAIL, $user['email']));
		$password = trim(Request::post(Auth::INPUT_PASSWORD, ''));
		$rePassword = trim(Request::post(Auth::INPUT_REPASSWORD, ''));

		$success = null;
		$error = null;
		$code_error = null;
		if(Security::validate())
		{
			if(!Auth::check_length_username($username))
			{
				$code_error = 'error_username_length';
			}
			else if(!Auth::check_type_username($username))
			{
				$code_error = 'error_username_char';
			}
			else if(User::has(['id[!]' => $user['id'], 'username[~]' => $username]))
			{
				$code_error = 'error_username_exist';
			}
			else if(!Auth::check_length_password($password))
			{
				$code_error = 'error_password_length';
			}
			else if($password !== $rePassword)
			{
				$code_error = 'error_password_reinput';
			}
			else if(!Auth::check_type_email($email))
			{
				$code_error = 'error_email_format';
			}
			else if(User::has(['id[!]' => $user['id'], 'email[~]' => $email]))
			{
				$code_error = 'error_email_exist';
			}


			if($code_error != null)
			{
				switch($code_error)
				{
					case 'error_username_length':
						$error = lang('register', $code_error, [
							'min' => Auth::USERNAME_MIN_LENGTH,
							'max' => Auth::USERNAME_MAX_LENGTH
						]);
						break;
					case 'error_password_length':
						$error = lang('register', $code_error, [
							'min' => Auth::PASSWORD_MIN_LENGTH,
							'max' => Auth::PASSWORD_MAX_LENGTH
						]);
						break;
					default:
						$error = lang('register', $code_error);
						break;
				}
			}
			else
			{
				if(User::update($user['id'], [
					'username' => $username,
					'password' => Auth::encrypt_password($password),
					'email' => $email
				]) == true)
				{
					unset($_SESSION['user_reset_password']);
					$success = 'Cập nhật thông tin tài khoản thành công'; #edit_lang
				}
				else
				{
					$error = lang('system', 'default_error');
				}
			}
		}
		$insertHiddenToken = Security::insertHiddenToken();
		return View::render_theme('auth.reset_password', compact('title', 'insertHiddenToken', 'username', 'email', 'password', 'rePassword', 'success', 'error', 'code_error'));
	}


	public function change_password($new_password = null)
	{
		if(!Auth::isLogin())
		{
			return false;
		}

		if(!Auth::check_length_password($new_password))
		{
			return false;
		}

		if(User::update(Auth::$data['id'], [
			'password' => Auth::encrypt_password($new_password)
		]) > 0)
		{
			return true;
		}

		return false;
	}

	public function logout()
	{
		Security::clear();
		Auth::logout();
		return $this->goToHome();
	}

	private function goToHome()
	{
		return Router::redirect('*', RouteMap::get('home'));
	}

}





?>