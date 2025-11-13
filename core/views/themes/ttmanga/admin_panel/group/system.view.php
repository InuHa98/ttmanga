<div class="box box--list">
	<div class="box__body">
	<?php if(UserPermission::is_access_configuration()): ?>
		<a class="box__body-item <?=($block_name == adminPanelController::BLOCK_CONFIGURATION ? 'active' : null);?>" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_CONFIGURATION]);?>">
			<span class="item-icon">
				<i class="fas fa-cog"></i>
			</span>
			<div>
				<span class="item-title">Cấu hình hệ thống</span>
			</div>
		</a>
	<?php endif; ?>

	<?php if(UserPermission::is_access_register_key()): ?>
		<a class="box__body-item <?=($block_name == adminPanelController::BLOCK_REGISTER_KEY ? 'active' : null);?>" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_REGISTER_KEY]);?>">
			<span class="item-icon">
				<i class="fas fa-key"></i>
			</span>
			<div>
				<span class="item-title">Mã đăng ký</span>
			</div>
		</a>
	<?php endif; ?>

	<?php if(UserPermission::is_access_mailer_setting()): ?>
		<a class="box__body-item <?=($block_name == adminPanelController::BLOCK_MAILER ? 'active' : null);?>" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_MAILER]);?>">
			<span class="item-icon">
				<i class="fas fa-envelope"></i>
			</span>
			<div>
				<span class="item-title">Mailer</span>
			</div>
		</a>
	<?php endif; ?>

	<?php if(UserPermission::is_access_role()): ?>
		<a class="box__body-item <?=($block_name == adminPanelController::BLOCK_ROLE ? 'active' : null);?>" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_ROLE]);?>">
			<span class="item-icon">
				<i class="fab fa-joomla"></i>
			</span>
			<div>
				<span class="item-title">Chức vụ</span>
			</div>
		</a>
	<?php endif; ?>

	<?php if(UserPermission::is_access_genres()): ?>
		<a class="box__body-item <?=($block_name == adminPanelController::BLOCK_GENRES ? 'active' : null);?>" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_GENRES]);?>">
			<span class="item-icon">
				<i class="fal fa-bars"></i>
			</span>
			<div>
				<span class="item-title">Thể loại</span>
			</div>
		</a>
	<?php endif; ?>

	<?php if(UserPermission::is_access_config_upload()): ?>
		<a class="box__body-item <?=($block_name == adminPanelController::BLOCK_CONFIG_UPLOAD ? 'active' : null);?>" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_CONFIG_UPLOAD]);?>">
			<span class="item-icon">
				<i class="fas fa-hdd"></i>
			</span>
			<div>
				<span class="item-title">Cấu hình upload</span>
			</div>
		</a>
	<?php endif; ?>

	<?php if(UserPermission::is_access_smiley()): ?>
		<a class="box__body-item <?=($block_name == adminPanelController::BLOCK_SMILEY ? 'active' : null);?>" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_SMILEY]);?>">
			<span class="item-icon">
				<i class="fas fa-smile"></i>
			</span>
			<div>
				<span class="item-title">Nhãn dán</span>
			</div>
		</a>
	<?php endif; ?>
	</div>
</div>