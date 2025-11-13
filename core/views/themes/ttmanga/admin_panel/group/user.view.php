<div class="box box--list">
	<div class="box__body">
	<?php if(UserPermission::is_access_user_list()): ?>
		<a class="box__body-item <?=($block_name == adminPanelController::BLOCK_USER_LIST ? 'active' : null);?>" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_USER, 'block' => adminPanelController::BLOCK_USER_LIST]);?>">
			<span class="item-icon">
				<i class="fas fa-users"></i>
			</span>
			<div>
				<span class="item-title">Danh sách thành viên</span>
			</div>
		</a>
	<?php endif; ?>

	<?php if(UserPermission::is_access_user_ban_list()): ?>
		<a class="box__body-item <?=($block_name == adminPanelController::BLOCK_USER_BAN_LIST ? 'active' : null);?>" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_USER, 'block' => adminPanelController::BLOCK_USER_BAN_LIST]);?>">
			<span class="item-icon">
				<i class="fas fa-ban"></i>
			</span>
			<div>
				<span class="item-title">Danh sách cấm</span>
			</div>
		</a>
	<?php endif; ?>
	</div>
</div>