<?php

trait Block_manga_trash {
	private static function block_manga_trash() {
		$title = 'Quản lí truyện đã xoá'; #edit_lang
		$success = null;
		$error = null;

		$where = [
			'is_trash' => Manga::IS_INACTIVE,
		];

		$keyword = trim(Request::get(InterFaceRequest::KEYWORD, ''));
		$type = trim(Request::get(InterFaceRequest::TYPE, ''));

		if($keyword != '') {
			switch($type) {
				case adminPanelController::INPUT_TEAM:
					$team = Team::select(['id'])::list(['name[~]' => '%'.$keyword.'%']);
					$teamManga = TeamManga::select(['manga_id'])::list(['team_id' => $team ? array_column($team, 'id') : 0]);
					$where['id'] = $teamManga ? array_column($teamManga, 'manga_id') : 0;
					break;
				default:
					$type = adminPanelController::INPUT_NAME;
					$where['OR #keyword'] = [
						'name[~]' => '%'.$keyword.'%'
					];
					$otherName = MangaOtherName::list(['name[~]' => '%'.$keyword.'%']);
					if ($otherName) {
						$where['OR #keyword']['OR'] = [];
						$i = 0;
						foreach($otherName as $o) {
							$where['OR #keyword']['OR']['[RAW] FIND_IN_SET(:id'.($i).', <{table}.name_other_ids>)'] = ['id'.$i++ => $o['id']];
						}
					}
					break;
			}
		}


		if(Security::validate() == true) {
			$action = trim(Request::post(adminPanelController::INPUT_ACTION, ''));
			$id = intval(Request::post(adminPanelController::INPUT_ID, 0));

			$manga = Manga::get(['id' => $id, 'is_trash' => Manga::IS_INACTIVE]);

			if(!$manga) {
				$error = 'Không tìm thấy truyện cần khôi phục'; #edit_lang
			} else {
				switch($action) {
					case adminPanelController::ACTION_RESTORE:
						if(UserPermission::has('admin_manga_delete') && Manga::update($manga['id'], [
							'is_trash' => Manga::IS_ACTIVE
						])) {
							Notification::create([
								'user_id' => $manga['user_upload'],
								'from_user_id' => Auth::$data['id'],
								'type' => Notification::TYPE_RESTORE_MANGA,
								'data' => [
									'manga_id' => $manga['id']
								]
							]);
							$success = 'Khôi phục thành công truyện: <b>'.$manga['name'].'</b>'; #edit_lang
						} else {
							$error = lang('system', 'default_error');
						}
						break;

					case adminPanelController::ACTION_DELETE_FOREVER:
						if(UserPermission::has('admin_manga_delete') && Manga::delete($manga['id']) > 0) {
							Comment::delete(['manga_id' => $manga['id']]);
							Chapter::delete(['manga_id' => $manga['id']]);
							$lst_history = History::list([
								'data[~]' => '"'.$manga['id'].'":['
							]);
							foreach($lst_history as $o) {
								$data = json_decode($o['data'], true);
								unset($data[$manga['id']]);
								History::update($o['id'], $data);
							}
							$success = 'Xoá vĩnh viễn thành công truyện: <b>'.$manga['name'].'</b>'; #edit_lang
						} else {
							$error = lang('system', 'default_error');
						}
						break;
				}
			}
		}

		$count = Manga::count($where);
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();
		$lst_manga = Manga::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));

		$lst_manga = array_map(function($o) {
			$teamManga = TeamManga::select(['team_id'])::list(['manga_id' => $o['id']]);
			$team = Team::join([])::select(['id', 'name'])::list(['id' => $teamManga ? array_column($teamManga, 'team_id') : 0]);
			$o['team_owns'] = implode(Manga::SEPARATOR, array_column($team, 'name'));
			return $o;
		}, $lst_manga);

		$insertHiddenToken = Security::insertHiddenToken();

		return [
			'title' => $title,
			'view_group' => 'admin_panel.group.manga',
			'view_block' => 'admin_panel.block.manga.trash',
			'data' => compact(
				'error',
				'success',
				'keyword',
				'type',
				'insertHiddenToken',
				'count',
				'lst_manga',
				'pagination'
			)
		];
	}
}

?>