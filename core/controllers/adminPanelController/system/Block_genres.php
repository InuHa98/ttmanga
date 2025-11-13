<?php

trait Block_genres {
	private static function block_genres($action) {
		$success = null;
		$error = null;

		switch($action) {

			case adminPanelController::ACTION_ADD:
				if(!UserPermission::has('admin_genres_create')) {
					break;
				}
				$title = 'Thêm thể loại mới'; #edit_lang
				$txt_description = $title; #edit_lang

				$name = trim(Request::post(adminPanelController::INPUT_NAME, ''));
				$text = trim(Request::post(adminPanelController::INPUT_TEXT, ''));

				if(Security::validate() == true)
				{

					if($name == '') {
						$error = 'Tên thể loại không được bỏ trống'; #edit_lang
					}
					else if($text == '') {
						$error = 'Mô tả không được bỏ trống'; #edit_lang
					}
					else if(Genres::has([
						'name[~]' => $name
					])) {
						$error = 'Tên thể loại đã tồn tại trên hệ thống'; #edit_lang
					}
					else {

						if(Genres::create($name, $text)) {
							Alert::push([
								'type' => 'success',
								'message' => lang('system', 'success_create')
							]);
							return redirect_route('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_GENRES]);
						} else {
							$error = lang('system', 'default_error');
						}
					}
				}

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.genres.add_edit',
					'data' => compact(
						'success',
						'error',
						'txt_description',
						'name',
						'text'
					)
				];

			case adminPanelController::ACTION_EDIT:
				if(!UserPermission::has('admin_genres_edit')) {
					break;
				}

				$title = 'Chỉnh sửa thể loại'; #edit_lang
				$txt_description = $title; #edit_lang

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$genres = Genres::get([
					'id' => $id
				]);

				if(!$genres) {
					return redirect_route('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_GENRES]);
				}

				$name = trim(Request::post(adminPanelController::INPUT_NAME, $genres['name']));
				$text = trim(Request::post(adminPanelController::INPUT_TEXT, $genres['text']));

				if(Security::validate() == true)
				{

					if($name == '') {
						$error = 'Tên thể loại không được bỏ trống'; #edit_lang
					}
					else if($text == '') {
						$error = 'Mô tả không được bỏ trống'; #edit_lang
					}
					else if(Genres::has([
						'id[!]' => $genres['id'],
						'name[~]' => $name
					])) {
						$error = 'Tên thể loại đã tồn tại trên hệ thống'; #edit_lang
					}
					else {
						if(Genres::update($genres['id'], [
							'name' => $name,
							'text' => $text
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
					'view_block' => 'admin_panel.block.system.genres.add_edit',
					'data' => compact(
						'success',
						'error',
						'txt_description',
						'name',
						'text'
					)
				];

			case adminPanelController::ACTION_DELETE:
				if(!UserPermission::has('admin_genres_delete')) {
					break;
				}

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$genres = Genres::get([
					'id' => $id
				]);

				if(!$genres) {
					Alert::push([
						'type' => 'error',
						'message' => 'Thể loại không tồn tại hoặc không thể xoá.' #edit_lang
					]);
				}

				if(Genres::delete($genres['id'])) {
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
				return redirect(Request::referer(RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_GENRES])));

			default:
				$title = 'Quản lí thể loại'; #edit_lang

				$count = Genres::count();
				new Pagination($count, App::$pagination_limit);
				$pagination = Pagination::get();
				$genres_list = Genres::list([
					'LIMIT' => [
						$pagination['start'], $pagination['limit']
					]
				]);

				$is_access_create = UserPermission::has('admin_genres_create');
				$is_access_edit = UserPermission::has('admin_genres_edit');
				$is_access_delete = UserPermission::has('admin_genres_delete');

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.genres.index',
					'data' => compact(
						'count',
						'genres_list',
						'is_access_create',
						'is_access_edit',
						'is_access_delete',
						'pagination'
					)
				];
		}
		redirect_route('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_GENRES]);
	}
}

?>