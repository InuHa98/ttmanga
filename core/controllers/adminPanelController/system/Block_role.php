<?php

trait Block_role {
	private static function block_role($action) {
		$success = null;
		$error = null;

		switch($action) {

			case adminpanelcontroller::ACTION_ADD:
				if(!UserPermission::has('admin_role_create')) {
					break;
				}
				$title = 'Thêm chức vụ mới'; #edit_lang
				$txt_description = $title; #edit_lang

				$name = trim(Request::post(adminpanelcontroller::INPUT_NAME, ''));
				$color = trim(Request::post(adminpanelcontroller::INPUT_COLOR, '#000000'));
				$level = intval(Request::post(adminpanelcontroller::INPUT_LEVEL, Role::MAX_LEVEL));
				$perms = Request::post(adminpanelcontroller::INPUT_PERMISSION, []);


				if(!is_array($perms)) {
					$perms = [$perms];
				}
				$perms = array_filter($perms, function($value, $key) {
					return array_key_exists($key, UserPermission::list()) && $value;
				}, ARRAY_FILTER_USE_BOTH);

				$perms = array_keys($perms);

				if(Security::validate() == true)
				{

					if($name == '') {
						$error = 'Tên chức vụ không được bỏ trống'; #edit_lang
					}
					else if(Role::has([
						'name[~]' => $name
					])) {
						$error = 'Tên chức vụ đã tồn tại trên hệ thống'; #edit_lang
					}
					else {

						if(Role::create([
							'name' => $name,
							'level' => $level,
							'color' => $color,
							'perms' => $perms
						])) {
							Alert::push([
								'type' => 'success',
								'message' => lang('system', 'success_create')
							]);
							return redirect_route('admin_panel', ['group' => adminpanelcontroller::GROUP_SYSTEM, 'block' => adminpanelcontroller::BLOCK_ROLE]);
						} else {
							$error = lang('system', 'default_error');
						}
					}
				}

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.role.add_edit',
					'data' => compact(
						'success',
						'error',
						'txt_description',
						'name',
						'level',
						'color',
						'perms'
					)
				];

			case adminpanelcontroller::ACTION_EDIT:
				if(!UserPermission::has('admin_role_edit')) {
					break;
				}

				$title = 'Chỉnh sửa chức vụ'; #edit_lang
				$txt_description = $title; #edit_lang

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$role = Role::get([
					'id' => $id
				]);

				if(!$role) {
					return redirect_route('admin_panel', ['group' => adminpanelcontroller::GROUP_SYSTEM, 'block' => adminpanelcontroller::BLOCK_ROLE]);
				}

				$name = trim(Request::post(adminpanelcontroller::INPUT_NAME, $role['name']));
				$color = trim(Request::post(adminpanelcontroller::INPUT_COLOR, $role['color']));
				$level = intval(Request::post(adminpanelcontroller::INPUT_LEVEL, $role['level']));
				$perms = json_decode($role['perms'], true);

				$all_perms = false;
				if(in_array(UserPermission::ALL_PERMS, $perms)) {
					$all_perms = true;
					$perms = array_keys(UserPermission::list());
				}

				if(Security::validate() == true)
				{

					if(!$all_perms) {
						$perms = Request::post(adminpanelcontroller::INPUT_PERMISSION, []);
				
						if(!is_array($perms)) {
							$perms = [$perms];
						}
						$perms = array_filter($perms, function($value, $key) {
							return array_key_exists($key, UserPermission::list()) && $value;
						}, ARRAY_FILTER_USE_BOTH);
		
						$perms = array_keys($perms);						
					}

					
					if($name == '') {
						$error = 'Tên chức vụ không được bỏ trống'; #edit_lang
					}
					else if(Role::has([
						'id[!]' => $role['id'],
						'name[~]' => $name
					])) {
						$error = 'Tên chức vụ đã tồn tại trên hệ thống'; #edit_lang
					}
					else {
						if(Role::update($role['id'], [
							'name' => $name,
							'level' => $level,
							'color' => $color,
							'perms' => $all_perms ? [UserPermission::ALL_PERMS] : $perms
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
					'view_block' => 'admin_panel.block.system.role.add_edit',
					'data' => compact(
						'success',
						'error',
						'txt_description',
						'name',
						'level',
						'color',
						'perms'
					)
				];

			case adminpanelcontroller::ACTION_DELETE:
				if(!UserPermission::has('admin_role_delete')) {
					break;
				}

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$role = Role::get([
					'id' => $id,
					'is_default[!]' => Role::IS_DEFAULT
				]);

				if(!$role) {
					Alert::push([
						'type' => 'error',
						'message' => 'Role không tồn tại hoặc không thể xoá.' #edit_lang
					]);
				}

				User::update(['role_id' => $role['id']], ['role_id' => Role::DEFAULT_ROLE_VIEWER]);

				if(Role::delete($role['id'])) {
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
				return redirect(Request::referer(RouteMap::get('admin_panel', ['group' => adminpanelcontroller::GROUP_SYSTEM, 'block' => adminpanelcontroller::BLOCK_ROLE])));

			default:
				$title = 'Quản lí chức vụ'; #edit_lang

				$count = Role::count();
				new Pagination($count, App::$pagination_limit);
				$pagination = Pagination::get();
				$role_list = Role::list([
					'LIMIT' => [
						$pagination['start'], $pagination['limit']
					]
				]);

				$is_access_create = UserPermission::has('admin_role_create');
				$is_access_edit = UserPermission::has('admin_role_edit');
				$is_access_delete = UserPermission::has('admin_role_delete');

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.role.index',
					'data' => compact(
						'role_list',
						'is_access_create',
						'is_access_edit',
						'is_access_delete',
						'pagination'
					)
				];
		}
		redirect_route('admin_panel', ['group' => adminpanelcontroller::GROUP_SYSTEM, 'block' => adminpanelcontroller::BLOCK_ROLE]);
	}
}

?>