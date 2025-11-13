<?php

trait Block_configuration {

	private static function block_configuration($action = null) {
		$title = 'Cấu hình hệ thống'; #edit_lang

		$success = null;
		$error = null;

		$app_name = trim(Request::post(DotEnv::APP_NAME, env(DotEnv::APP_NAME)));
		$app_title = trim(Request::post(DotEnv::APP_TITLE, env(DotEnv::APP_TITLE)));
		$app_description = trim(Request::post(DotEnv::APP_DESCRIPTION, env(DotEnv::APP_DESCRIPTION)));
		$app_email = trim(Request::post(DotEnv::APP_EMAIL, env(DotEnv::APP_EMAIL)));
		$profile_upload_mode = trim(Request::post(DotEnv::PROFILE_UPLOAD_MODE, env(DotEnv::PROFILE_UPLOAD_MODE)));
		$limit_login = env(DotEnv::LIMIT_LOGIN, false);
		$required_login = env(DotEnv::APP_REQUIRED_LOGIN, false);
		$encode_url_image = env(DotEnv::ENCODE_URL_IMAGE, false);
		$default_language = trim(Request::post(DotEnv::DEFAULT_LANGUAGE, env(DotEnv::DEFAULT_LANGUAGE)));
		$default_theme = trim(Request::post(DotEnv::DEFAULT_THEME, env(DotEnv::DEFAULT_THEME)));
		$limit_item_page = intval(Request::post(DotEnv::APP_LIMIT_ITEM_PAGE, env(DotEnv::APP_LIMIT_ITEM_PAGE, 60)));
		$view_hot = intval(Request::post(DotEnv::VIEW_HOT, env(DotEnv::VIEW_HOT, 10000)));
		$imgur_client_id = Request::post(DotEnv::IMGUR_CLIENT_ID, env(DotEnv::IMGUR_CLIENT_ID, []));

		switch($profile_upload_mode) {
			case  App::PROFILE_UPLOAD_MODE_IMGUR:
				$profile_upload_mode = App::PROFILE_UPLOAD_MODE_IMGUR;
				break;
			default:
				$profile_upload_mode = App::PROFILE_UPLOAD_MODE_LOCALHOST;
				break;
		}

		$limit_login = filter_var($limit_login, FILTER_VALIDATE_BOOLEAN);
		$required_login = filter_var($required_login, FILTER_VALIDATE_BOOLEAN);
		$encode_url_image = filter_var($encode_url_image, FILTER_VALIDATE_BOOLEAN);

		if(!is_array($imgur_client_id)) {
			$imgur_client_id = [$imgur_client_id];
		}
		
		$imgur_client_id = array_filter($imgur_client_id);

		if(Security::validate() == true)
        {
			$limit_login = !!intval(Request::post(DotEnv::LIMIT_LOGIN, false));
			$required_login = !!intval(Request::post(DotEnv::APP_REQUIRED_LOGIN, false));
			$encode_url_image = !!intval(Request::post(DotEnv::ENCODE_URL_IMAGE, false));
			if(!is_numeric($limit_item_page) || $limit_item_page < 1) {
				$error = 'Số lượng item một trang phải lớn hơn 0'; #edit_lang
			} else if(!is_numeric($view_hot) || $view_hot < 1) {
				$error = 'Số lượt xem hót phải lớn hơn 0'; #edit_lang
			} else if(!array_key_exists($default_language, Language::list())) {
				$error = 'Ngôn ngữ không tồn tại hoặc đã bị xoá'; #edit_lang
			} else if(!array_key_exists($default_theme, themeController::list())) {
				$error = 'Giao diện không tồn tại hoặc đã bị xoá'; #edit_lang
			} else {
				if(App::update_config([
					DotEnv::APP_NAME => $app_name,
					DotEnv::APP_TITLE => $app_title,
					DotEnv::APP_DESCRIPTION => $app_description,
					DotEnv::APP_EMAIL => $app_email,
					DotEnv::PROFILE_UPLOAD_MODE => $profile_upload_mode,
					DotEnv::LIMIT_LOGIN => $limit_login,
					DotEnv::APP_REQUIRED_LOGIN => $required_login,
					DotEnv::ENCODE_URL_IMAGE => $encode_url_image,
					DotEnv::DEFAULT_LANGUAGE => $default_language,
					DotEnv::DEFAULT_THEME => $default_theme,
					DotEnv::APP_LIMIT_ITEM_PAGE => $limit_item_page,
					DotEnv::VIEW_HOT => $view_hot,
					DotEnv::IMGUR_CLIENT_ID => $imgur_client_id
				])) {
					$success = lang('system', 'success_save');
				} else {
					$error = lang('system', 'default_error');
				}
			}				
		}

		return [
			'title' => $title,
			'view_group' => 'admin_panel.group.system',
			'view_block' => 'admin_panel.block.system.configuration',
			'data' => compact(
				'success',
				'error',
				'app_name',
				'app_title',
				'app_description',
				'app_email',
				'profile_upload_mode',
				'limit_login',
				'required_login',
				'default_language',
				'default_theme',
				'limit_item_page',
				'view_hot',
				'imgur_client_id',
				'encode_url_image'
			)
		];
	}
}

?>