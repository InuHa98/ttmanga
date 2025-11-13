<?php View::render_theme('layout.header', ['title' => $title]); ?>

<?php

echo themeController::load_css('css/admin_panel.css');

?>

<div class="section-sub-header">
	<div class="container">
        <span>Admin Panel</span>
    </div>
</div>

<div class="container">
	<div class="row">
		<div class="col-xs-12">
			<div class="tabmenu-horizontal margin-b-2">
			<?php if(UserPermission::is_access_group_system()): ?>
				<div class="tabmenu-horizontal__item <?=($group_name == adminPanelController::GROUP_SYSTEM ? 'active' : null);?>">
					<a href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM]);?>">Cài đặt hệ thống</a>
				</div>
			<?php endif;?>

			<?php if(UserPermission::is_access_group_user()): ?>
				<div class="tabmenu-horizontal__item <?=($group_name == adminPanelController::GROUP_USER ? 'active' : null);?>">
					<a href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_USER]);?>">Quản lí thành viên</a>
				</div>
			<?php endif;?>

			<?php if(UserPermission::is_access_group_team()): ?>
				<div class="tabmenu-horizontal__item <?=($group_name == adminPanelController::GROUP_TEAM ? 'active' : null);?>">
					<a href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_TEAM]);?>">
						<span>Quản lí nhóm dịch</span>
					<?php if($group_name != adminPanelController::GROUP_TEAM && $notification_approval_team > 0): ?>
						<span class="count-new-item"><?=number_format($notification_approval_team, 0, ',', '.');?></span>
					<?php endif; ?>
					</a>
				</div>
			<?php endif;?>

			<?php if(UserPermission::is_access_group_manga()): ?>
				<div class="tabmenu-horizontal__item <?=($group_name == adminPanelController::GROUP_MANGA ? 'active' : null);?>">
					<a href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_MANGA]);?>">
						<span>Quản lí truyện</span>
					</a>
				</div>
			<?php endif;?>

			</div>
		</div>

		<div class="col-xs-12 col-md-4 col-lg-3">
		<?php 
			if(isset($block_view['view_group']))
			{
				View::render_theme($block_view['view_group'], compact('block_name', 'notification_approval_team'));
			}
		?>
		</div>

		<div class="col-xs-12 col-md-8 col-lg-9">
		<?php 
			if(isset($block_view['view_block']))
			{
				View::render_theme($block_view['view_block'], $block_view['data']);
			}
		?>
		</div>
	</div>
</div>

<script type="text/javascript" src="<?=APP_URL;?>/assets/js/form-validator.js?v=<?=$_version;?>"></script>

<?php View::render_theme('layout.footer'); ?>