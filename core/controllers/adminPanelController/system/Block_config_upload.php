<?php

trait Block_config_upload {
	private static function Block_config_upload($action) {
		$success = null;
		$error = null;

		if($action == adminPanelController::ACTION_CHECK_CONFIG) {
			return self::api_check_status();
		}

		switch($action) {

			case adminPanelController::ACTION_ADD:
				if(!UserPermission::has('admin_config_upload_create')) {
					break;
				}
				$title = 'Thêm chức cấu hình upload ảnh'; #edit_lang
				$txt_description = $title; #edit_lang

				$name = trim(Request::post(adminPanelController::INPUT_NAME, ''));
				$cookie = trim(Request::post(adminPanelController::INPUT_COOKIE, ''));
				$note = trim(Request::post(adminPanelController::INPUT_NOTE, ''));

				if(Security::validate() == true)
				{

					if($name == '') {
						$error = 'Tên cấu hình không được bỏ trống'; #edit_lang
					}
					else if($cookie == '') {
						$error = 'Cookie không được bỏ trống'; #edit_lang
					}
					else if(ConfigUpload::has([
						'name[~]' => $name
					])) {
						$error = 'Tên cấu hình đã tồn tại trên hệ thống'; #edit_lang
					}
					else if(!ConfigUpload::check_status($cookie))
					{
						$error = 'Cookie không chính xác hoặc đã hết hạn'; #edit_lang
					}
					else {

						if(ConfigUpload::create($name, $cookie, $note)) {
							Alert::push([
								'type' => 'success',
								'message' => lang('system', 'success_create')
							]);
							return redirect_route('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_CONFIG_UPLOAD]);
						} else {
							$error = lang('system', 'default_error');
						}
					}
				}

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.config_upload.add_edit',
					'data' => compact(
						'success',
						'error',
						'txt_description',
						'name',
						'cookie',
						'note'
					)
				];

			case adminPanelController::ACTION_EDIT:
				if(!UserPermission::has('admin_config_upload_edit')) {
					break;
				}

				$title = 'Chỉnh sửa cấu hình upload ảnh'; #edit_lang
				$txt_description = $title; #edit_lang

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$config_upload = ConfigUpload::get([
					'id' => $id
				]);

				if(!$config_upload) {
					return redirect_route('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_CONFIG_UPLOAD]);
				}

				$name = trim(Request::post(adminPanelController::INPUT_NAME, $config_upload['name']));
				$cookie = trim(Request::post(adminPanelController::INPUT_COOKIE, $config_upload['cookie']));
				$note = trim(Request::post(adminPanelController::INPUT_NOTE, $config_upload['note']));

				if(Security::validate() == true)
				{
					
					if($name == '') {
						$error = 'Tên cấu hình không được bỏ trống'; #edit_lang
					}
					else if($cookie == '') {
						$error = 'Cookie không được bỏ trống'; #edit_lang
					}
					else if(ConfigUpload::has([
						'id[!]' => $config_upload['id'],
						'name[~]' => $name
					])) {
						$error = 'Tên cấu hình đã tồn tại trên hệ thống'; #edit_lang
					}
					else if(!ConfigUpload::check_status($cookie))
					{
						$error = 'Cookie không chính xác hoặc đã hết hạn'; #edit_lang
					}
					else {
						if(ConfigUpload::update($config_upload['id'], [
							'name' => $name,
							'cookie' => $cookie,
							'note' => $note
						])) {
							$success = lang('system', 'success_update');
						} else {
							$error = lang('system', 'default_error');
						}
					}
				}

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.config_upload.add_edit',
					'data' => compact(
						'success',
						'error',
						'txt_description',
						'name',
						'cookie',
						'note'
					)
				];

			case adminPanelController::ACTION_DELETE:
				if(!UserPermission::has('admin_config_upload_delete')) {
					break;
				}

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$config_upload = ConfigUpload::get([
					'id' => $id
				]);

				if(!$config_upload) {
					Alert::push([
						'type' => 'error',
						'message' => 'Config Upload không tồn tại hoặc không thể xoá.' #edit_lang
					]);
				}

				if(ConfigUpload::delete($config_upload['id'])) {
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
				return redirect(Request::referer(RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_CONFIG_UPLOAD])));

			default:
				$title = 'Quản lí cấu hình upload ảnh'; #edit_lang

				$count = ConfigUpload::count();
				new Pagination($count, App::$pagination_limit);
				$pagination = Pagination::get();
				$config_upload_list = ConfigUpload::select([
					'<{table}.*>',
					'CONCAT(\'"\', (SELECT GROUP_CONCAT(DISTINCT <name> ORDER BY <name> ASC SEPARATOR \'", "\') FROM <core_teams> WHERE FIND_IN_SET(<{table}.id>, <config_id>)), \'"\') AS <teams_used>'
				])::list([
					'LIMIT' => [
						$pagination['start'], $pagination['limit']
					]
				]);

				$is_access_create = UserPermission::has('admin_config_upload_create');
				$is_access_edit = UserPermission::has('admin_config_upload_edit');
				$is_access_delete = UserPermission::has('admin_config_upload_delete');

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.config_upload.index',
					'data' => compact(
						'count',
						'config_upload_list',
						'is_access_create',
						'is_access_edit',
						'is_access_delete',
						'pagination'
					)
				];
		}
		redirect_route('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_CONFIG_UPLOAD]);
	}


	private static function api_check_status() {
		$id = intval(Request::post(InterFaceRequest::ID, 0));
		$config_upload = ConfigUpload::get($id);

		if(!$config_upload) {
			exit(json_api(404, 'Không tìm thấy config upload'));
		}

		$google_upload = new GoogleUpload($config_upload);
		if($google_upload->check_status() === true)
		{
			exit(json_api(200, 'LIVE'));
		}
		exit(json_api(403, 'DIE'));
	}
}

?>