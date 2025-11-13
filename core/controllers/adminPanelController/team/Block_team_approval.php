<?php

trait Block_team_approval {
	private static function block_team_approval() {
		$title = 'Xét duyệt nhóm dịch mới'; #edit_lang
		$success = null;
		$error = null;


		$where = [
			'active[!]' => Team::IS_ACTIVE
		];


		if(Security::validate() == true) {
			$action = trim(Request::post(adminpanelcontroller::INPUT_ACTION, ''));
			$id = intval(Request::post(adminpanelcontroller::INPUT_ID, 0));

			$team = Team::get([
				'id' => $id,
				'active[!]' => Team::IS_ACTIVE
			]);

			if(!$team) {
				$error = 'Không tìm thấy nhóm dịch'; #edit_lang
			} else {

				if(UserPermission::has('admin_team_approval')) {
					switch($action) {
						case adminpanelcontroller::ACTION_ACCEPT:
							$config_id = intval(Request::post(adminpanelcontroller::INPUT_CONFIG, null));

							$config = ConfigUpload::get($config_id);

							if(!$config) {
								$error = 'Cấu hình không tồn tại hoặc đã bị xoá'; #edit_lang
							} else {
								if(Team::update($team['id'], [
									'active' => Team::IS_ACTIVE,
									'config_id' => $config['id']
								])) {
									User::update($team['own_id'], [
										'team_id' => $team['id']
									]);
									Notification::create([
										'user_id' => $team['own_id'],
										'from_user_id' => Auth::$data['id'],
										'type' => Notification::TYPE_ACCEPT_TEAM,
										'data' => [
											'team_name' => $team['name']
										]
									]);
									$success = 'chấp thuận thành công nhóm dịch: <b>'.$team['name'].'</b>'; #edit_lang
								} else {
									$error = lang('system', 'default_error');
								}								
							}

							break;

						case adminpanelcontroller::ACTION_REJECT:
							$reason = trim(Request::post(adminpanelcontroller::INPUT_REASON, ''));

							if(Team::delete($team['id'])) {
								Notification::create([
									'user_id' => $team['own_id'],
									'from_user_id' => Auth::$data['id'],
									'type' => Notification::TYPE_REJECT_TEAM,
									'data' => [
										'team_name' => $team['name'],
										'reason' => $reason
									]
								]);
								$success = 'Từ chối thành công nhóm dịch: <b>'.$team['name'].'</b>'; #edit_lang
							} else {
								$error = lang('system', 'default_error');
							}								
							
							break;
					}

				}
			}
		}

		$count = Team::count($where);
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();
		$team_list = Team::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));

		$insertHiddenToken = Security::insertHiddenToken();
		$list_config = ConfigUpload::list();

		return [
			'title' => $title,
			'view_group' => 'admin_panel.group.team',
			'view_block' => 'admin_panel.block.team.approval',
			'data' => compact(
				'success',
				'error',
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