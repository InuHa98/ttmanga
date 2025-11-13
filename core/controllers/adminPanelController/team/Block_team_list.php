<?php

trait Block_team_list {
	private static function block_team_list() {
		$title = 'Quản lí nhóm dịch'; #edit_lang
		$success = null;
		$error = null;


		$where = [
			'active' => Team::IS_ACTIVE,
			'user_ban' => Team::IS_NOT_BAN
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

				if(UserPermission::has('admin_team_edit')) {
					switch($action) {
						case adminpanelcontroller::ACTION_CHANGE_NAME:
							$name = trim(Request::post(adminpanelcontroller::INPUT_NAME, ''));
							if($name == '')
							{
								$error = 'Tên nhóm không được bỏ trống'; #edit_lang
							}
							else if(Team::has([
								'name[~]' => $name,
								'id[!]' => $team['id']
							])) {
								$error = 'Tên nhóm đã tồn tại trên hệ thống'; #edit_lang
							} else {
								if(Team::update($team['id'], [
									'name' => $name
								])) {
									Notification::create([
										'user_id' => $team['own_id'],
										'from_user_id' => Auth::$data['id'],
										'type' => Notification::TYPE_CHANGE_NAME_TEAM,
										'data' => [
											'name' => $name
										]
									]);
									$success = 'Cập nhật thành công tên nhóm: <b>'. $team['name'].'</b> => <b>'.$name.'</b>'; #edit_lang
								} else {
									$error = lang('system', 'default_error');
								}
							}
							break;

						case adminpanelcontroller::ACTION_CHANGE_OWN:
							$new_own = intval(Request::post(adminpanelcontroller::INPUT_OWN, null));

							$own = User::get($new_own);
							if(!$own) {
								$error = 'Thành viên không tồn tại'; #edit_lang
							}
							else if(Team::has([
								'own_id' => $own['id']
							])) {
								$error = 'Thành viên này đã là trưởng một nhóm dịch khác'; #edit_lang
							}
							else if($own['team_id'] != '') {
								$error = 'Thành viên này đã tham gia một nhóm dịch khác'; #edit_lang
							}
							else
							{
								if(Team::update($team['id'], [
									'own_id' => $own['id']
								]) > 0)
								{
									Notification::create([
										'user_id' => $own['id'],
										'from_user_id' => Auth::$data['id'],
										'type' => Notification::TYPE_CHANGE_OWN_TEAM,
										'data' => [
											'own_id' => $own['id'],
											'team_id' => $team['id'],
											'team_name' => $team['name']
										]
									]);
									Notification::create([
										'user_id' => $team['own_id'],
										'from_user_id' => Auth::$data['id'],
										'type' => Notification::TYPE_CHANGE_OWN_TEAM,
										'data' => [
											'own_id' => $own['id'],
											'team_id' => $team['id'],
											'team_name' => $team['name']
										]
									]);
									$success = 'Thay đổi trưởng nhóm dịch thành công: <b>'.$team['name'].'</b>'; #edit_lang
								} else {
									$error = lang('system', 'default_error');
								}
							}
							break;

						case adminpanelcontroller::ACTION_CHANGE_CONFIG:
							$config_id = intval(Request::post(adminpanelcontroller::INPUT_CONFIG, null));

							$config = ConfigUpload::get($config_id);
							if(!$config) {
								$error = 'Cấu hình không tồn tại hoặc đã bị xoá'; #edit_lang
							}
							else
							{
								if(Team::update($team['id'], [
									'config_id' => $config['id']
								]) > 0)
								{
									$success = 'Thay cấu hình upload ảnh nhóm dịch thành công: <b>'.$team['name'].'</b>'; #edit_lang
								} else {
									$error = lang('system', 'default_error');
								}
							}
							break;
					}		
				}
				
				if(UserPermission::has('admin_team_ban') && $action == adminpanelcontroller::ACTION_BAN) {

					$reason = trim(Request::post(adminpanelcontroller::INPUT_REASON, ''));

					if(Team::update($team['id'], [
						'user_ban' => Auth::$data['id']
					])) {
						Notification::create([
							'user_id' => $team['own_id'],
							'from_user_id' => Auth::$data['id'],
							'type' => Notification::TYPE_BAN_TEAM,
							'data' => [
								'reason' => $reason
							]
						]);
						$success = 'Cấm thành công nhóm dịch: <b>'.$team['name'].'</b>'; #edit_lang
					} else {
						$error = lang('system', 'default_error');
					}
				}
			}
		}

		$count = Team::count($where);
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();
		$team_list = Team::join([
			'LEFT JOIN <core_config_upload> ON <core_config_upload.id> = <{table}.config_id>',
		], false)::select([
			'<core_config_upload.name> AS <config_name>'
		], false)::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));

		$insertHiddenToken = Security::insertHiddenToken();
		$list_config = ConfigUpload::list();
		return [
			'title' => $title,
			'view_group' => 'admin_panel.group.team',
			'view_block' => 'admin_panel.block.team.list',
			'data' => compact(
				'success',
				'error',
				'keyword',
				'insertHiddenToken',
				'list_config',
				'count',
				'team_list',
				'pagination'
			)
		];
	}
}

?>