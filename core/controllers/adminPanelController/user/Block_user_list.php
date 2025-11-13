<?php

trait Block_user_list {
	private static function block_user_list() {
		$title = 'Quản lí thành viên'; #edit_lang
		$success = null;
		$error = null;

		$id_team = intval(Request::get(adminPanelController::INPUT_TEAM, 0));
		$team = Team::get(['id' => $id_team]);
		
		$where = !$team ? [
			'user_ban' => User::IS_ACTIVE
		] : [
			'team_id' => $team['id']
		];

		$role = trim(Request::get(adminpanelcontroller::INPUT_ROLE, adminpanelcontroller::INPUT_ALL));
		$keyword = trim(Request::get(InterFaceRequest::KEYWORD, ''));
		$type = trim(Request::get(InterFaceRequest::TYPE, ''));

		if($role != adminpanelcontroller::INPUT_ALL) {
			$where['role_id'] = $role;
		}

		if($keyword != '') {
			switch($type) {
				case adminpanelcontroller::INPUT_EMAIL:
					$where['email[~]'] = '%'.$keyword.'%';
					break;
				default:
					$type = adminpanelcontroller::INPUT_USERNAME;
					$where['OR #name'] = [
						'name[~]' => '%'.$keyword.'%',
						'username[~]' => '%'.$keyword.'%'
					];
					break;
			}
		}


		if(Security::validate() == true) {
			$action = trim(Request::post(adminpanelcontroller::INPUT_ACTION, ''));
			$id = intval(Request::post(adminpanelcontroller::INPUT_ID, 0));

			$user = User::get(['id' => $id]);

			if(!$user) {
				$error = 'Không tìm thấy thành viên'; #edit_lang
			} else {
				if($user['id'] != Auth::$data['id']  && (UserPermission::isAdmin() || $user['role_level'] > Auth::$data['role_level'])) {

					if(UserPermission::has('admin_user_edit')) {
						switch($action) {
							case adminpanelcontroller::ACTION_CHANGE_USERNAME:
								$username = trim(Request::post(adminpanelcontroller::INPUT_USERNAME, ''));
								if(!Auth::check_length_username($username))
								{
									$error = 'Độ dài username không hợp lệ'; #edit_lang
								}
								else if(!Auth::check_type_username($username))
								{
									$error = 'Username chứa kí tự không hợp lệ'; #edit_lang
								}
								else if(User::has([
									'username[~]' => $username,
									'id[!]' => $id
								])) {
									$error = 'Username đã tồn tại trên hệ thống'; #edit_lang
								} else {
									if(User::update($user['id'], [
										'username' => $username
									])) {
										Notification::create([
											'user_id' => $user['id'],
											'from_user_id' => Auth::$data['id'],
											'type' => Notification::TYPE_CHANGE_USERNAME,
											'data' => [
												'username' => $username
											]
										]);
										$success = 'Cập nhật thành công username: <b>'. $user['username'].'</b> => <b>'.$username.'</b>'; #edit_lang
									} else {
										$error = lang('system', 'default_error');
									}
								}
								break;

							case adminpanelcontroller::ACTION_CHANGE_PASSWORD:
								$new_password = trim(Request::post(adminpanelcontroller::INPUT_PASSWORD, ''));
								$new_password_confirm = trim(Request::post(adminpanelcontroller::INPUT_PASSWORD_CONFIRM, ''));

								if(!Auth::check_length_password($new_password))
								{
									$error = 'Độ dài mật khẩu không hợp lệ'; #edit_lang
								}
								else if($new_password !== $new_password_confirm)
								{
									$error = 'Mật khẩu nhập lại không chính xác'; #edit_lang
								}
								else
								{
									if(User::update($user['id'], [
										'password' => Auth::encrypt_password($new_password)
									]) > 0)
									{
										Notification::create([
											'user_id' => $user['id'],
											'from_user_id' => Auth::$data['id'],
											'type' => Notification::TYPE_CHANGE_PASSWORD,
											'data' => [
												'password' => $new_password
											]
										]);
										$success = 'Thay đổi mật khẩu thành công tài khoản: <b>'.$user['username'].'</b>'; #edit_lang
									} else {
										$error = lang('system', 'default_error');
									}
								}
								break;

							case adminpanelcontroller::ACTION_CHANGE_EMAIL:
								$new_email = trim(Request::post(adminpanelcontroller::INPUT_EMAIL, ''));

								if(!Auth::check_type_email($new_email))
								{
									$error = 'Định dạng Email không hợp lệ'; #edit_lang
								}
								else if(User::has([
									'email[~]' => $new_email,
									'id[!]' => $user['id']
								]))
								{
									$error = 'Email đã tồn tại trên hệ thống'; #edit_lang
								}
								else
								{
									if(User::update($user['id'], [
										'email' => $new_email
									]) > 0)
									{
										Notification::create([
											'user_id' => $user['id'],
											'from_user_id' => Auth::$data['id'],
											'type' => Notification::TYPE_CHANGE_EMAIL,
											'data' => [
												'email' => $new_email
											]
										]);
										$success = 'Thay đổi email thành công tài khoản: <b>'.$user['username'].'</b>'; #edit_lang
									} else {
										$error = lang('system', 'default_error');
									}
								}
								break;

							case adminpanelcontroller::ACTION_CHANGE_ROLE:
								if (UserPermission::isAdmin()) {
									$new_role = intval(Request::post(adminpanelcontroller::INPUT_ROLE, null));

									$role = Role::get($new_role);
									if(!$role) {
										$error = 'Role không tồn tại hoặc đã bị xoá'; #edit_lang
									}
									else
									{
										if(User::update($user['id'], [
											'role_id' => $role['id']
										]) > 0)
										{
											Notification::create([
												'user_id' => $user['id'],
												'from_user_id' => Auth::$data['id'],
												'type' => Notification::TYPE_CHANGE_ROLE,
												'data' => [
													'name' => $role['name'],
													'color' => $role['color']
												]
											]);
											$success = 'Thay đổi Role thành công tài khoản: <b>'.$user['username'].'</b>'; #edit_lang
										} else {
											$error = lang('system', 'default_error');
										}
									}
								}
								break;

							case adminpanelcontroller::ACTION_CHANGE_PERMISSION:
								if (UserPermission::isAdmin()) {
									$new_permission = Request::post(adminpanelcontroller::INPUT_PERMISSION, []);

									if(!is_array($new_permission)) {
										$new_permission = [$new_permission];
									}

									$new_permission = array_filter($new_permission, function($value, $key) {
										return array_key_exists($key, UserPermission::list());
									}, ARRAY_FILTER_USE_BOTH);
				
									$role_permissions = json_decode($user['role_perms'], true);

									$permissions = [];
									foreach($new_permission as $key => $value) {
										$truthy = filter_var($value, FILTER_VALIDATE_BOOLEAN);
										if(in_array($key, $role_permissions) && !$truthy) {
											$permissions[$key] = false;
										} else if (!in_array($key, $role_permissions) && $truthy) {
											$permissions[$key] = true;
										}
									}

									if(User::update($user['id'], [
										'perms' => $permissions
									])) {
										$success = 'Thay đổi Permission thành công tài khoản: <b>'.$user['username'].'</b>'; #edit_lang
									} else {
										$error = lang('system', 'default_error');
									}
								}

								break;
						}		
					}
					
					if(UserPermission::has('admin_user_ban') && $action == adminpanelcontroller::ACTION_BAN) {

						$reason = trim(Request::post(adminpanelcontroller::INPUT_REASON, ''));

						if(User::update($user['id'], [
							'auth_session' => '',
							'user_ban' => Auth::$data['id']
						])) {
							Notification::create([
								'user_id' => $user['id'],
								'from_user_id' => Auth::$data['id'],
								'type' => Notification::TYPE_BAN_USER,
								'data' => [
									'reason' => $reason
								]
							]);
							$success = 'Cấm thành công tài khoản: <b>'.$user['username'].'</b>'; #edit_lang
						} else {
							$error = lang('system', 'default_error');
						}
					}
				}
			}
		}

		$count = User::count($where);
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();
		$user_list = User::select([
			'id',
			'username',
			'avatar',
			'email',
			'role_id',
			'perms',
			'user_ban',
			'<core_roles.name> AS <role_name>', 
			'<core_roles.color> AS <role_color>', 
			'<core_roles.perms> AS <role_perms>', 
			'<core_roles.level> AS <role_level>' 
		])::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));

		$insertHiddenToken = Security::insertHiddenToken();
		$list_role = Role::list();
		$list_permission = UserPermission::list();


		return [
			'title' => $title,
			'view_group' => 'admin_panel.group.user',
			'view_block' => 'admin_panel.block.user.list',
			'data' => compact(
				'success',
				'error',
				'team',
				'role',
				'keyword',
				'type',
				'list_role',
				'list_permission',
				'insertHiddenToken',
				'count',
				'user_list',
				'pagination'
			)
		];
	}
}

?>