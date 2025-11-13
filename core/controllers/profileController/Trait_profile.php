<?php

trait Trait_profile {

    public static function block_infomation()
    {
        $title = 'Thông tin cá nhân'; #edit_lang

        $data_form = [
            'name' => trim(Request::post(profilecontroller::INPUT_FORM_NAME, Auth::$data['name'])),
            'date_of_birth' => trim(Request::post(profilecontroller::INPUT_FORM_DATE_OF_BIRTH, Auth::$data['date_of_birth'])),
            'email' => Auth::$data['email'],
            'sex' => trim(Request::post(profilecontroller::INPUT_FORM_SEX, Auth::$data['sex'])),
            'facebook' => trim(Request::post(profilecontroller::INPUT_FORM_FACEBOOK, Auth::$data['facebook'])),
            'bio' => trim(Request::post(profilecontroller::INPUT_FORM_BIO, Auth::$data['bio']))
        ];

        $error = null;
        $success = null;

        $form_action = Request::post(profilecontroller::INPUT_FORM_ACTION, null);

        if(Security::validate() == true)
        {
            switch($form_action)
            {
                case profilecontroller::ACTION_INFOMATION:
                    if(!in_array($data_form['sex'], [User::SEX_UNKNOWN, User::SEX_MALE, User::SEX_FEMALE]))
                    {
                        $data_form['sex'] = User::SEX_UNKNOWN;
                    }
        
                    if($data_form['date_of_birth'] && !preg_match("/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4,".strlen(date('Y'))."})$/u", $data_form['date_of_birth']))
                    {
                        $error = 'Ngày sinh không hợp lệ'; #edit_lang
                    }
                    else if($data_form['facebook'] && !preg_match("/^https?:\/\/((.*)\.)?(fb\.com|facebook\.com)\/(.*?)$/u", $data_form['facebook']))
                    {
                        $error = 'Facebook liên hệ không hoặc lệ'; #edit_lang
                    }
                    else
                    {
                        if(User::update(Auth::$id, $data_form))
                        {
                            Auth::trigger_data('name', $data_form['name']);
                            $success = lang('system', 'success_update');
                        }
                        else
                        {
                            $error = lang('system', 'error_update');
                        }
                    }
                    break;

                case profilecontroller::ACTION_CHANGE_EMAIL:
                    $newEmail = trim(Request::post(profilecontroller::INPUT_FORM_EMAIL, Auth::$data['email']));
                    $password = Request::post(Auth::INPUT_PASSWORD, null);
        
                    if(Auth::verify_password($password, Auth::$data['password']) != true)
                    {
                        $error = 'Mật khẩu không chính xác'; #edit_lang
                    }
                    else if($newEmail == "")
                    {
                        $error = 'Email mới không được bỏ trống'; #edit_lang
                    }
                    else if(!Auth::check_type_email($newEmail))
                    {
                        $error = 'Định dạng email không hợp lệ'; #edit_lang
                    }
                    else if(User::has(['email[~]' => $newEmail, 'id[!]' => Auth::$id]))
                    {
                        $error = 'Email đã tồn tại trên hệ thống'; #edit_lang
                    }
                    else 
                    {
                        if(User::update(Auth::$id, ['email' => $newEmail]) > 0)
                        {
                            $data_form['email'] = $newEmail;
                            $success = lang('system', 'success_update');
                        }
                        else
                        {
                            $error = lang('system', 'error_update');
                        }				
                    }
                    break;
            }            
        }


        $data_form['success'] = $success;
        $data_form['error'] = $error;

        return [
            'title' => $title,
            'view' => 'profile.block.infomation',
            'data' => $data_form
        ];
    }

    public static function block_logindevice()
    {
        $title = 'Thiết bị đã đăng nhập';

        $error = null;
        $success = null;

        $form_action = Request::post(profilecontroller::INPUT_FORM_ACTION, null);

        if(Security::validate() == true)
        {
            switch($form_action)
            {
                case profilecontroller::ACTION_LOGOUT_ALL:
                    if(Auth::delete_auth_session() === true)
                    {
                        $success = 'Đăng xuất tất cả thiết bị thành công.'; #edit_lang
                    }
                    else
                    {
                        $error = 'Có lỗi xảy ra. Không thể đăng xuất những thiết bị này.'; #edit_lang
                    }
                    break;

                case profilecontroller::ACTION_LOGOUT_DEVICE:

                    $auth_session = Request::post(profilecontroller::INPUT_FORM_AUTH_SESSION, null);
                    if($auth_session != "" && Auth::delete_auth_session($auth_session) === true)
                    {
                        $success = 'Thiết bị đăng xuất thành công.'; #edit_lang
                    }
                    else
                    {
                        $error = 'Không thể đăng xuất thiết bị này.'; #edit_lang
                    }
                    break;
            }
        }

        $auth_sessions = array_filter(explode("\n", trim(Auth::$data['auth_session'] ?? '')));
		$count = count($auth_sessions);

        new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();

        $auth_sessions = array_values(array_slice($auth_sessions, $pagination['start'], $pagination['limit']));

        $pass_auth_session = Auth::pass_auth_session();

        if($auth_sessions)
        {
            $auth_sessions = array_map(function($session) use ($pass_auth_session) {
                $auth_session = Auth::escape_session($session);
                $decrypt = Auth::decrypt_login($auth_session, $pass_auth_session);
                $decrypt['user_agent'] = getBrowser($decrypt['user_agent']);
                $decrypt['auth_session'] = $auth_session;
                return $decrypt;
            }, array_reverse($auth_sessions));            

        }

        return [
            'title' => $title,
            'view' => 'profile.block.logindevice',
            'data' => compact('success', 'error', 'auth_sessions', 'pagination', 'count')
        ];
    }

    public static function block_changepassword()
    {
        $title = 'Đổi mật khẩu'; #edit_lang

        $error = null;
        $success = null;
        $new_password = trim(Request::post(profilecontroller::INPUT_FORM_NEW_PASSWORD, ''));
        $confirm_password = trim(Request::post(profilecontroller::INPUT_FORM_CONFIRM_PASSWORD, ''));
        $password = trim(Request::post(profilecontroller::INPUT_FORM_PASSWORD, ''));

        $form_action = Request::post(profilecontroller::INPUT_FORM_ACTION, '');

        if(Security::validate() == true)
        {
            if($form_action == profilecontroller::ACTION_CHANGEPASSWORD)
            {
                if(Auth::verify_password($password, Auth::$data['password']) != true)
                {
                    $error = 'Mật khẩu cũ không chính xác!'; #edit_lang
                }
                else if(!Auth::check_length_password($new_password))
                {
                    $error = "Độ dài mật khẩu không hợp lệ"; #edit_lang
                }
                else if($new_password !== $confirm_password)
                {
                    $error = "Mật khẩu nhập lại không chính xác"; #edit_lang
                }
                else
                {
                    if(Controller::load('authController@change_password', $new_password) == true)
                    {
                        $success = 'Thay đổi mật khẩu thành công!'; #edit_lang
                    }
                    else
                    {
                        $error = lang('system', 'default_error');
                    }
                }
            }
        }

        return [
            'title' => $title,
            'view' => 'profile.block.changepassword',
            'data' => compact('error', 'success')
        ];
    }

    public static function block_settings()
    {
        $title = 'Cài đặt tài khoản'; #edit_lang

        $error = null;
        $success = null;

        $hide_info = filter_var(User::get_setting('hide_info'), FILTER_VALIDATE_BOOLEAN);
        $limit_age = Request::post(profilecontroller::INPUT_FORM_LIMIT_AGE, User::get_setting('limit_age'));
        $language = Request::post(profilecontroller::INPUT_FORM_LANGUAGE, User::get_setting('language'));
        $theme = Request::post(profilecontroller::INPUT_FORM_THEME, User::get_setting('theme'));

        $form_action = Request::post(profilecontroller::INPUT_FORM_ACTION, null);

        if(Security::validate() == true)
        {
            if($form_action == profilecontroller::ACTION_SETTINGS)
            {
                $hide_info = !!intval(Request::post(profilecontroller::INPUT_FORM_HIDE_INFO, false));
                if(User::update_setting(Auth::$id, [
                    'hide_info' => $hide_info,
                    'limit_age' => $limit_age,
                    'language' => $language,
                    'theme' => $theme
                ]) == true)
                {
                    $success = lang('system', 'success_save');
                }
                else
                {
                    $error = lang('system', 'default_error');
                }
            }
        }

        return [
            'title' => $title,
            'view' => 'profile.block.settings',
            'data' => compact('error', 'success', 'hide_info', 'limit_age', 'language', 'theme')
        ];
    }

    public static function block_smiley($action)
    {
		$success = null;
		$error = null;

		switch($action) {

			case profilecontroller::ACTION_ADD:
				$title = 'Thêm nhãn dán mới'; #edit_lang
				$txt_description = 'Thêm nhãn dán mới'; #edit_lang

				$name = trim(Request::post(profilecontroller::INPUT_SMILEY_NAME, ''));
				$images = Request::post(profilecontroller::INPUT_SMILEY_IMAGES, []);
				if(!is_array($images)) {
					$images = [$images];
				}
				$images = array_filter($images);

				if(Security::validate() == true)
				{

					if($name == '') {
						$error = 'Tên nhãn dán không được bỏ trống'; #edit_lang
					}
					else if(Smiley::has([
						'name[~]' => $name,
						'type' => Smiley::TYPE_USER,
                        'user_id' => Auth::$data['id']
					])) {
						$error = 'Tên nhãn dán đã tồn tại trên hệ thống'; #edit_lang
					}
					else if(!$images) {
						$error = 'Nhãn dán phải có ít nhất một link ảnh'; #edit_lang
					}
					else {
						if(Smiley::create($name, $images)) {
                            Alert::push([
								'type' => 'success',
								'message' => lang('system', 'success_create')
							]);
							return redirect_route('profile', ['id' => 'me', 'block' => profileController::BLOCK_SMILEY]);
						} else {
							$error = lang('system', 'default_error');
						}
					}
				}

				return [
					'title' => $title,
					'view' => 'profile.block.smiley.add_edit',
					'data' => compact(
						'success',
						'error',
						'txt_description',
						'name',
						'images'
					)
				];

			case profilecontroller::ACTION_EDIT:

				$title = 'Chỉnh sửa nhãn dán'; #edit_lang
				$txt_description = 'Chỉnh sửa nhãn dán'; #edit_lang

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$smiley = Smiley::get([
					'id' => $id,
                    'user_id' => Auth::$data['id'],
                    'type' => Smiley::TYPE_USER
				]);

				if(!$smiley) {
					return redirect_route('profile', ['id' => 'me', 'block' => profilecontroller::BLOCK_SMILEY]);
				}

				$name = trim(Request::post(profilecontroller::INPUT_SMILEY_NAME, $smiley['name']));
				$images = json_decode($smiley['images'], true);


				if(Security::validate() == true)
				{

					$images = Request::post(profilecontroller::INPUT_SMILEY_IMAGES, []);
			
					if(!is_array($images)) {
						$images = [$images];
					}
					
					$images = array_filter($images);
					
					if($name == '') {
						$error = 'Tên nhãn dán không được bỏ trống'; #edit_lang
					}
					else if(Smiley::has([
						'id[!]' => $smiley['id'],
						'name[~]' => $name,
						'type' => Smiley::TYPE_USER,
                        'user_id' => Auth::$data['id']
					])) {
						$error = 'Tên nhãn dán đã tồn tại trên hệ thống'; #edit_lang
					}
					else if(!$images) {
						$error = 'Nhãn dán phải có ít nhất một link ảnh'; #edit_lang
					}
					else {
						if(Smiley::update($smiley['id'], [
							'name' => $name,
							'images' => $images
						])) {
							$success = lang('system', 'success_update');
						} else {
							$error = lang('system', 'default_error');
						}
					}
				}

				return [
					'title' => $title,
					'view' => 'profile.block.smiley.add_edit',
					'data' => compact(
						'success',
						'error',
						'txt_description',
						'name',
						'images'
					)
				];

			case profilecontroller::ACTION_DELETE:
				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$smiley = Smiley::get([
					'id' => $id,
                    'user_id' => Auth::$data['id'],
                    'type' => Smiley::TYPE_USER
				]);

				if(!$smiley) {
					Alert::push([
						'type' => 'error',
						'message' => 'Smiley không tồn tại hoặc đã bị xoá.' #edit_lang
					]);
				}

				if(Smiley::delete($smiley['id'])) {
					Alert::push([
						'type' => 'success',
						'message' => lang('system', 'success_delete')
					]);
				} else {
					Alert::push([
						'type' => 'error',
						'message' => lang('system', 'default_error')
					]);
				}
				return redirect(Request::referer(RouteMap::get('profile', ['id' => 'me', 'block' => profilecontroller::BLOCK_SMILEY])));

			default:
				$title = 'Quản lý nhãn dán của bạn'; #edit_lang

				$where = [
					'type' => Smiley::TYPE_USER,
                    'user_id' => Auth::$data['id']
				];

                $count = Smiley::count($where);
				new Pagination($count, App::$pagination_limit);
				$pagination = Pagination::get();
				$smiley_list = Smiley::list(array_merge($where, [
					'LIMIT' => [
						$pagination['start'], $pagination['limit']
					]
				]));

				return [
					'title' => $title,
					'view' => 'profile.block.smiley.index',
					'data' => compact(
						'smiley_list',
                        'pagination'
					)
				];
		}
        redirect_route('profile', ['id' => 'me', 'block' => profilecontroller::BLOCK_SMILEY]);
    }

    public static function block_manga_upload($user)
    {

        if(!$user)
        {
            return null;
        }

		$count = Manga::count([
            'user_upload' => $user['id']
        ]);

        new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();

        $manga_items = Manga::list([
            'user_upload' => $user['id'],
            'LIMIT' => [
                $pagination['start'], $pagination['limit']
            ]
        ]);

        $view_mode = App::view_mode();

        return [
            'view' => 'profile.block.manga',
            'data' => compact('count', 'manga_items', 'pagination', 'view_mode')
        ];
    }

    public static function block_manga_join($user)
    {
        if(!$user)
        {
            return null;
        }

        $lst_manga = Chapter::select(['manga_id'])::list([
            'user_upload' => $user['id'],
            'GROUP' => ['manga_id']
        ]);

        $where = [
            'user_upload[!]' => $user['id'],
            'id' => $lst_manga ? array_column($lst_manga, 'manga_id') : -1
        ];

		$count = Manga::count($where);

        new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();

        $manga_items = Manga::list(array_merge($where, [
            'LIMIT' => [
                $pagination['start'], $pagination['limit']
            ]
        ]));

        $view_mode = App::view_mode();

        return [
            'view' => 'profile.block.manga',
            'data' => compact('count', 'manga_items', 'pagination', 'view_mode')
        ];
    }

}

?>