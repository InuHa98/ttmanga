<?php

trait Block_team_ban_list {
	private static function block_team_ban_list() {
		$title = 'Danh sách nhóm dịch bị cấm'; #edit_lang

		$success = null;
		$error = null;

		$where = [
			'active' => Team::IS_ACTIVE,
			'user_ban[!]' => Team::IS_NOT_BAN
		];

		$keyword = trim(Request::get(InterFaceRequest::KEYWORD, ''));

		if($keyword != '') {
			$where['name[~]'] = '%'.$keyword.'%';
		}


		if(Security::validate() == true) {
			$action = trim(Request::post(adminpanelcontroller::INPUT_ACTION, ''));
			$id = intval(Request::post(adminpanelcontroller::INPUT_ID, 0));

			$team = Team::get($id);

			if(!$team) {
				$error = 'Không tìm thấy nhóm dịch'; #edit_lang
			} else {

				if(UserPermission::has('admin_team_unban') && $action == adminpanelcontroller::ACTION_UNBAN) {
					if(Team::update($team['id'], [
						'user_ban' => Team::IS_NOT_BAN
					])) {
						Notification::create([
							'user_id' => $team['own_id'],
							'from_user_id' => Auth::$data['id'],
							'type' => Notification::TYPE_UNBAN_TEAM,
							'data' => []
						]);
						$success = 'Bỏ cấm thành công nhóm dịch: <b>'.$team['name'].'</b>'; #edit_lang
					} else {
						$error = lang('system', 'default_error');
					}
				}
			}
		}



		$count = Team::count($where);
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();
		$team_list = Team::select([
			'<user_ban.username> AS <user_ban_username>',
			'<user_ban.avatar> AS <user_ban_avatar>',
			'<user_ban.user_ban> AS <user_ban_id>',
			'<roles_ban.color> AS <user_ban_role_color>',
			'<core_config_upload.name> AS <config_name>',
		], false)::join([
			'LEFT JOIN <core_users> AS <user_ban> ON <{table}.user_ban> = <user_ban.id>',
			'LEFT JOIN <core_roles> AS <roles_ban> ON <roles_ban.id> = <user_ban.role_id>',
			'LEFT JOIN <core_config_upload> ON <core_config_upload.id> = <{table}.config_id>',
		], false)::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));

		$insertHiddenToken = Security::insertHiddenToken();
		$list_role = Role::list();

		return [
			'title' => $title,
			'view_group' => 'admin_panel.group.team',
			'view_block' => 'admin_panel.block.team.ban_list',
			'data' => compact(
				'success',
				'error',
				'keyword',
				'insertHiddenToken',
				'count',
				'team_list',
				'pagination'
			)
		];
	}
}

?>