<?php

trait Block_user_ban_list {
	private static function block_user_ban_list() {
		$title = 'Danh sách thành viên bị cấm'; #edit_lang

		$success = null;
		$error = null;

		$where = [
			'user_ban[!]' => User::IS_ACTIVE
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

					if(UserPermission::has('admin_user_unban') && $action == adminpanelcontroller::ACTION_UNBAN) {
						if(User::update($user['id'], [
							'user_ban' => User::IS_ACTIVE
						])) {
							Notification::create([
								'user_id' => $user['id'],
								'from_user_id' => Auth::$data['id'],
								'type' => Notification::TYPE_UNBAN_USER,
								'data' => []
							]);
							$success = 'Bỏ cấm thành công tài khoản: <b>'.$user['username'].'</b>'; #edit_lang
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
			'user_ban',
			'<core_roles.name> AS <role_name>', 
			'<core_roles.color> AS <role_color>', 
			'<core_roles.level> AS <role_level>',
			'<user_ban.id> AS <user_ban_id>',
			'<user_ban.username> AS <user_ban_username>',
			'<user_ban.avatar> AS <user_ban_avatar>',
			'<user_ban.user_ban> AS <user_ban_ban_id>',
			'(SELECT <color> FROM <core_roles> WHERE <id> = <user_ban.role_id>) AS <user_ban_role_color>'
		])::join([
			'LEFT JOIN <core_users> AS <user_ban> ON <{table}.user_ban> = <user_ban.id>'
		], false)::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));

		$insertHiddenToken = Security::insertHiddenToken();
		$list_role = Role::list();

		return [
			'title' => $title,
			'view_group' => 'admin_panel.group.user',
			'view_block' => 'admin_panel.block.user.ban_list',
			'data' => compact(
				'success',
				'error',
				'role',
				'keyword',
				'type',
				'list_role',
				'insertHiddenToken',
				'count',
				'user_list',
				'pagination'
			)
		];
	}
}

?>