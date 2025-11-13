<?php


trait Block_manga_list {
	private static function block_manga_list() {
		$title = 'Quản lí truyện'; #edit_lang


		$where = [
			'is_trash' => Manga::IS_ACTIVE,
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

		$count = Manga::count($where);
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();
		$lst_manga = Manga::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));

		$lst_manga = array_map(function($o) {
			$teamManga = TeamManga::join([
				'INNER JOIN <core_teams> ON <core_team_mangas.team_id> = <core_teams.id>'
			])::select([
				'<core_teams.name>'
			])::list(['manga_id' => $o['id']]);
			$o['team_owns'] = implode(Manga::SEPARATOR, array_column($teamManga, 'name'));
			return $o;
		}, $lst_manga);

		return [
			'title' => $title,
			'view_group' => 'admin_panel.group.manga',
			'view_block' => 'admin_panel.block.manga.list',
			'data' => compact(
				'keyword',
				'type',
				'count',
				'lst_manga',
				'pagination'
			)
		];
	}
}

?>