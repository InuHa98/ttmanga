<?php

trait Block_smiley {
	private static function block_smiley($action = null) {
		
		$success = null;
		$error = null;

		switch($action) {

			case adminpanelcontroller::ACTION_ADD:
				if(!UserPermission::has('admin_smiley_create')) {
					break;
				}
				$title = 'Thêm nhãn dán mới'; #edit_lang
				$txt_description = 'Thêm nhán dán mới'; #edit_lang

				$name = trim(Request::post(adminpanelcontroller::INPUT_SMILEY_NAME, ''));
				$images = Request::post(adminpanelcontroller::INPUT_SMILEY_IMAGES, []);
				if(!is_array($images)) {
					$images = [$images];
				}
				$images = array_filter($images);

				if(Security::validate() == true)
				{

					if($name == '') {
						$error = 'Tên nhãn dán không được bỏ trống'; #edit_lang
					}
					else if(Smiley::has([
						'name[~]' => $name,
						'type' => Smiley::TYPE_SYSTEM
					])) {
						$error = 'Tên nhãn dán đã tồn tại trên hệ thống'; #edit_lang
					}
					else if(!$images) {
						$error = 'Nhãn dán phải có ít nhất một link ảnh'; #edit_lang
					}
					else {
						if(Smiley::create($name, $images, true)) {
							Alert::push([
								'type' => 'success',
								'message' => lang('system', 'success_create')
							]);
							return redirect_route('admin_panel', ['group' => adminpanelcontroller::GROUP_SYSTEM, 'block' => adminpanelcontroller::BLOCK_SMILEY]);
						} else {
							$error = lang('system', 'default_error');
						}
					}
				}

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.smiley.add_edit',
					'data' => compact(
						'success',
						'error',
						'txt_description',
						'name',
						'images'
					)
				];

			case adminpanelcontroller::ACTION_EDIT:
				if(!UserPermission::has('admin_smiley_edit')) {
					break;
				}
				$title = 'Chỉnh sửa nhãn dán'; #edit_lang
				$txt_description = 'Chỉnh sửa nhãn dán'; #edit_lang

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$smiley = Smiley::get([
					'id' => $id,
					'type' => Smiley::TYPE_SYSTEM
				]);

				if(!$smiley) {
					return redirect_route('admin_panel', ['group' => adminpanelcontroller::GROUP_SYSTEM, 'block' => adminpanelcontroller::BLOCK_SMILEY]);
				}

				$name = trim(Request::post(adminpanelcontroller::INPUT_SMILEY_NAME, $smiley['name']));
				$images = json_decode($smiley['images'], true);


				if(Security::validate() == true)
				{

					$images = Request::post(adminpanelcontroller::INPUT_SMILEY_IMAGES, []);
			
					if(!is_array($images)) {
						$images = [$images];
					}
					
					$images = array_filter($images);
					
					if($name == '') {
						$error = 'Tên nhãn dán không được bỏ trống'; #edit_lang
					}
					else if(Smiley::has([
						'id[!]' => $smiley['id'],
						'name[~]' => $name,
						'type' => Smiley::TYPE_SYSTEM
					])) {
						$error = 'Tên nhãn dán đã tồn tại trên hệ thống'; #edit_lang
					}
					else if(!$images) {
						$error = 'Nhãn dán phải có ít nhất một link ảnh'; #edit_lang
					}
					else {
						if(Smiley::update($smiley['id'], [
							'name' => $name,
							'images' => $images
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
					'view_block' => 'admin_panel.block.system.smiley.add_edit',
					'data' => compact(
						'success',
						'error',
						'txt_description',
						'name',
						'images'
					)
				];

			case adminpanelcontroller::ACTION_DELETE:
				if(!UserPermission::has('admin_smiley_delete')) {
					break;
				}

				$id = intval(Request::get(InterFaceRequest::ID, 0));
				$smiley = Smiley::get([
					'id' => $id,
					'type' => Smiley::TYPE_SYSTEM
				]);

				if(!$smiley) {
					Alert::push([
						'type' => 'error',
						'message' => 'Smiley không tồn tại hoặc đã bị xoá.' #edit_lang
					]);
				}

				if(Smiley::delete($smiley['id'])) {
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
				return redirect(Request::referer(RouteMap::get('admin_panel', ['group' => adminpanelcontroller::GROUP_SYSTEM, 'block' => adminpanelcontroller::BLOCK_SMILEY])));

			default:
				$title = 'Quản lí nhãn dán'; #edit_lang

				$where = [
					'type' => Smiley::TYPE_SYSTEM
				];

				$count = Smiley::count($where);
				new Pagination($count, App::$pagination_limit);
				$pagination = Pagination::get();
				$smiley_list = Smiley::list(array_merge($where, [
					'LIMIT' => [
						$pagination['start'], $pagination['limit']
					]
				]));

				$is_access_create = UserPermission::has('admin_smiley_create');
				$is_access_edit = UserPermission::has('admin_smiley_edit');
				$is_access_delete = UserPermission::has('admin_smiley_delete');

				return [
					'title' => $title,
					'view_group' => 'admin_panel.group.system',
					'view_block' => 'admin_panel.block.system.smiley.index',
					'data' => compact(
						'smiley_list',
						'is_access_create',
						'is_access_edit',
						'is_access_delete',
						'pagination'
					)
				];
		}
		redirect_route('admin_panel', ['group' => adminpanelcontroller::GROUP_SYSTEM, 'block' => adminpanelcontroller::BLOCK_SMILEY]);
	}
}

?>