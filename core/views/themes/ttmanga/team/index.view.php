<?php View::render_theme('layout.header', ['title' => $block_view['title']]); ?>

<?php

echo themeController::load_css('css/team.css');

?>

<div class="section-sub-header">
	<div class="container">
        <span>Nhóm dịch của tôi</span>
    </div>
</div>


<div class="container">
	<div class="row">

		<div class="col-md-12 col-lg-8">
			<div class="tabmenu-horizontal margin-b-2">

				<div class="tabmenu-horizontal__item <?=($block == teamController::BLOCK_LIST_MANGA ? 'active' : null);?>">
					<a href="<?=RouteMap::get('my_team', ['block' => teamController::BLOCK_LIST_MANGA]);?>">Danh sách truyện</a>
				</div>

				<div class="tabmenu-horizontal__item <?=($block == teamController::BLOCK_LIST_MEMBER ? 'active' : null);?>">
					<a href="<?=RouteMap::get('my_team', ['block' => teamController::BLOCK_LIST_MEMBER]);?>">
						<span>Thành viên</span>
					<?php if($is_own && $block != teamController::BLOCK_LIST_MEMBER && $notification_request_join > 0): ?>
						<span class="count-new-item"><?=$notification_request_join;?></span>
					<?php endif; ?>
					</a>
				</div>

				<div class="tabmenu-horizontal__item <?=($block == teamController::BLOCK_REPORT ? 'active' : null);?>">
					<a href="<?=RouteMap::get('my_team', ['block' => teamController::BLOCK_REPORT]);?>">
						<span>Báo lỗi</span>
					<?php if($block != teamController::BLOCK_REPORT && $notification_report > 0): ?>
						<span class="count-new-item"><?=number_format($notification_report, 0, ',', '.');?></span>
					<?php endif; ?>
					</a>
				</div>

			<?php if($is_own): ?>
				<div class="tabmenu-horizontal__item <?=($block == teamController::BLOCK_SETTING ? 'active' : null);?>">
					<a href="<?=RouteMap::get('my_team', ['block' => teamController::BLOCK_SETTING]);?>">Tuỳ chỉnh</a>
				</div>
			<?php endif; ?>
			
			</div>

			<?php 
				if($block_view)
				{
					View::render_theme($block_view['view'], $block_view['data']);
				}
			?>

		</div>

		<div class="col-md-12 col-lg-4">
			<div class="team-infomation">
				<div class="section-team-cover">
					<div id="preview_cover" class="section-team-cover__bg-cover" style="background-image: url(<?=Team::get_cover($team);?>);">
					</div>
					<div class="section-team-cover__bg-alpha"></div>
					<div class="team-name" id="preview_avatar">
						<?=render_avatar($team, Team::get_avatar($team));?>
						<div class="name"><?=_echo($team['name']);?></div>
					</div>

				</div>
			<?php if($is_own): ?>
				<div class="button-change-avatar-cover">
					<div role="btn-change-avatar">
						<i class="fas fa-camera"></i>
						<span class="text">Đổi Avatar</span>
					</div>
					<div role="btn-change-cover">
						<i class="fas fa-camera"></i>
						<span class="text">Đổi ảnh bìa</span>
					</div>
					<div class="save-upload">
						<form method="POST">
							<input type="hidden" name="<?=teamController::INPUT_ACTION;?>" value="<?=teamController::ACTION_UPLOAD_IMAGE;?>">
							<input type="hidden" id="data-upload" name="<?=teamController::INPUT_DATA_IMAGE;?>">
							<input type="submit" role="btn-save-upload">
						</form>
					</div>
				</div>
			<?php endif; ?>
				<div class="team-infomation__line">
					<span class="label"><i class="fab fa-empire"></i> Trưởng nhóm:</span>
					<span>
						<a target="_blank" class="user-infomation" href="<?=RouteMap::get('profile', ['id' => $own['id']]);?>">
							<?=render_avatar($own, null, true, true);?>
						</a>
					</span>
				</div>
				<div class="team-infomation__line">
					<span class="label"><i class="fas fa-users"></i> Thành viên:</span>
					<span class="number"><?=number_format($team['total_members'], 0, ',', '.');?></span>
				</div>
				<div class="team-infomation__line">
					<span class="label"><i class="fas fa-books"></i> Số truyện:</span>
					<span class="number"><?=number_format($team['total_mangas'], 0, ',', '.');?></span>
				</div>
				<div class="team-infomation__line">
					<span class="label"><i class="fas fa-database"></i> Số chương:</span>
					<span class="number"><?=number_format($team['total_chapters'], 0, ',', '.');?></span>
				</div>
			<?php if($team['facebook']): ?>
				<div class="team-infomation__line">
					<span class="label"><i class="fab fa-facebook-square"></i> Facebook:</span>
					<span class="text"><a target="_blank" href="<?=_echo($team['facebook']);?>"><?=_echo($team['facebook']);?></a></span>
				</div>
			<?php endif; ?>
			</div>
		<?php if(!empty($team['desc'])): ?>
			<div class="info-box mb-4"><?=_echo($team['desc']);?></div>
		<?php endif; ?>
		</div>


	</div>
</div>


<?php if($is_own): ?>
<input id="input-upload-image" type="file" accept="image/*" style="display: none;">
<link rel="stylesheet" href="<?=APP_URL;?>/assets/css/cropper.css?v=<?=$_version;?>">
<script type="text/javascript" src="<?=APP_URL;?>/assets/js/cropper.js?v=<?=$_version;?>"></script>
<?=themeController::load_js('js/avatar-cover-upload.js');?>
<?php endif; ?>

<?php View::render_theme('layout.footer'); ?>