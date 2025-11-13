<?php

trait Block_register_key {
	private static function block_register_key($action) {
		$success = null;
		$error = null;

		switch($action) {

			case adminpanelcontroller::ACTION_ADD:
				if(!UserPermission::has('admin_register_key_create')) {
					break;
				}
				$title = 'Tạo Register Key'; #edit_lang
				$txt_description = 'Tạo Register Key'; #edit_lang

				$key = trim(Request::post(adminpanelcontroller::INPUT_KEY, ''));
				$quantity = intval(Request::post(adminpanelcontroller::INPUT_QUANTITY, 1));
				$note = trim(Request::post(adminpanelcontroller::INPUT_NOTE, ''));


				if(Security::validate() == true)
				{

					if($key == '') {
						$key = RegisterKey::generateKey();
					}

					if(RegisterKey::has([
						'key[~]' => $key,
						'status' => RegisterKey::STATUS_LIVE
					])) {
						$error = 'Register Key đã tồn tại trên hệ thống'; #edit_lang
					}
					else if ($quantity < 1) {
						$error = 'Số lượng key không hợp lệ'; #edit_lang
					}
					else {
						if(RegisterKey::create($key, $quantity, $note)) {
							Alert::push([
								'type' => 'success',
								'message' => lang('system', 'success_create')
							]);
							return redirect_route('admin_panel', ['group' => adminpanelcontroller::GROUP_SYSTEM, 'block' => adminpanelcontroller::BLOCK_REGISTER_KEY]);
						} else {
							$error = lang('system', 'default_error');
						}
					}
				}

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.register_key.add',
					'data' => compact(
						'success',
						'error',
						'key',
						'quantity',
						'note'
					)
				];

			case adminpanelcontroller::ACTION_DELETE:
				if(!UserPermission::has('admin_register_key_delete')) {
					break;
				}
				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$register_key = RegisterKey::get([
					'id' => $id,
					'status' => RegisterKey::STATUS_LIVE
				]);

				if(!$register_key) {
					Alert::push([
						'type' => 'error',
						'message' => 'Register Key không tồn tại hoặc đã bị xoá.' #edit_lang
					]);
				}

				if(RegisterKey::delete($register_key['id'])) {
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

				return redirect(Request::referer(RouteMap::get('admin_panel', ['group' => adminpanelcontroller::GROUP_SYSTEM, 'block' => adminpanelcontroller::BLOCK_REGISTER_KEY])));

			default:
				$title = 'Quản lí mã đăng ký'; #edit_lang

				$status = intval(Request::get(adminpanelcontroller::INPUT_STATUS, RegisterKey::STATUS_LIVE));
				switch($status) {
					case RegisterKey::STATUS_DIE: break;
					default:
						$status = RegisterKey::STATUS_LIVE;
					break;
				}

				$count = RegisterKey::count();
				new Pagination($count, App::$pagination_limit);
				$pagination = Pagination::get();
				$register_key_list = RegisterKey::list([
					'status' => $status,
					'LIMIT' => [
						$pagination['start'], $pagination['limit']
					]
				]);

				$is_access_create = UserPermission::has('admin_register_key_create');
				$is_access_delete = UserPermission::has('admin_register_key_delete');

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.register_key.index',
					'data' => compact(
						'status',
						'is_access_create',
						'is_access_delete',
						'register_key_list',
						'pagination'
					)
				];
		}
		redirect_route('admin_panel', ['group' => adminpanelcontroller::GROUP_SYSTEM, 'block' => adminpanelcontroller::BLOCK_REGISTER_KEY]);
	}
}

?>