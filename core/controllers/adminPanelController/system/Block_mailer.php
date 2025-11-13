<?php

trait Block_mailer {
	private static function block_mailer($action = null) {
		$title = 'Cấu hình Mailer'; #edit_lang
		$success = null;
		$error = null;

		$list_template = Mailer::list_template();
		$mailer_smtp = env(DotEnv::MAILER_SMTP, []);
		$mailer_api = env(DotEnv::MAILER_API, []);


		$mailer_mode = trim(Request::post(DotEnv::MAILER_MODE, env(DotEnv::MAILER_MODE)));
		$mailer_name = trim(Request::post(DotEnv::MAILER_NAME, env(DotEnv::MAILER_NAME)));
		$mailer_from = trim(Request::post(DotEnv::MAILER_FROM, env(DotEnv::MAILER_FROM)));
		$mailer_template = trim(Request::post(DotEnv::MAILER_TEMPLATE, env(DotEnv::MAILER_TEMPLATE)));

		$smtp_authencation = trim(Request::post(adminpanelcontroller::INPUT_MAILER_SMTP_AUTHENTICATION, isset($mailer_smtp[Mailer::VAR_AUTHENTICATION]) ? $mailer_smtp[Mailer::VAR_AUTHENTICATION] : false));
		$smtp_username = trim(Request::post(adminpanelcontroller::INPUT_MAILER_SMTP_USERNAME, isset($mailer_smtp[Mailer::VAR_USERNAME]) ? $mailer_smtp[Mailer::VAR_USERNAME] : ''));
		$smtp_password = trim(Request::post(adminpanelcontroller::INPUT_MAILER_SMTP_PASSWORD, isset($mailer_smtp[Mailer::VAR_PASSWORD]) ? $mailer_smtp[Mailer::VAR_PASSWORD] : ''));
		$smtp_host = trim(Request::post(adminpanelcontroller::INPUT_MAILER_SMTP_HOST, isset($mailer_smtp[Mailer::VAR_HOST]) ? $mailer_smtp[Mailer::VAR_HOST] : ''));
		$smtp_secure = trim(Request::post(adminpanelcontroller::INPUT_MAILER_SMTP_SECURE, isset($mailer_smtp[Mailer::VAR_SECURE]) ? $mailer_smtp[Mailer::VAR_SECURE] : ''));
		$smtp_port = trim(Request::post(adminpanelcontroller::INPUT_MAILER_SMTP_PORT, isset($mailer_smtp[Mailer::VAR_PORT]) ? $mailer_smtp[Mailer::VAR_PORT] : ''));

		$api_server = trim(Request::post(adminpanelcontroller::INPUT_MAILER_API_SERVER, isset($mailer_api[Mailer::VAR_SERVER]) ? $mailer_api[Mailer::VAR_SERVER] : ''));
		$api_key = trim(Request::post(adminpanelcontroller::INPUT_MAILER_API_KEY, isset($mailer_api[Mailer::VAR_KEY]) ? $mailer_api[Mailer::VAR_KEY] : ''));
		$api_secret = trim(Request::post(adminpanelcontroller::INPUT_MAILER_API_SECRET, isset($mailer_api[Mailer::VAR_SECRET]) ? $mailer_api[Mailer::VAR_SECRET] : ''));

		switch($mailer_mode) {
			case  Mailer::MODE_API:
				$mailer_mode = Mailer::MODE_API;
				break;
			default:
				$mailer_mode = Mailer::MODE_SMTP;
				break;
		}

		if(!in_array($mailer_template, $list_template)) {
			$mailer_template = isset($list_template[0]) ? $list_template[0] : null;
		}

		$smtp_authencation = filter_var($smtp_authencation, FILTER_VALIDATE_BOOLEAN);

		if(Security::validate() == true)
        {
			if($mailer_from != '' && !Auth::check_type_email($mailer_from)) {
				$error = 'Email không hợp lệ'; #edit_lang
			} else {
				if(App::update_config([
					DotEnv::MAILER_MODE => $mailer_mode,
					DotEnv::MAILER_TEMPLATE => $mailer_template,
					DotEnv::MAILER_NAME => $mailer_name,
					DotEnv::MAILER_FROM => $mailer_from,
					DotEnv::MAILER_SMTP => [
						Mailer::VAR_AUTHENTICATION => $smtp_authencation,
						Mailer::VAR_HOST => $smtp_host,
						Mailer::VAR_SECURE => $smtp_secure,
						Mailer::VAR_PORT => $smtp_port,
						Mailer::VAR_USERNAME => $smtp_username,
						Mailer::VAR_PASSWORD => $smtp_password
					],
					DotEnv::MAILER_API => [
						Mailer::VAR_SERVER => $api_server,
						Mailer::VAR_KEY => $api_key,
						Mailer::VAR_SECRET => $api_secret
					]
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
			'view_block' => 'admin_panel.block.system.mailer',
			'data' => compact(
				'success',
				'error',
				'list_template',
				'mailer_mode',
				'mailer_name',
				'mailer_from',
				'mailer_template',
				'smtp_authencation',
				'smtp_username',
				'smtp_password',
				'smtp_host',
				'smtp_secure',
				'smtp_port',
				'api_server',
				'api_key',
				'api_secret'
			)
		];
	}
}

?>