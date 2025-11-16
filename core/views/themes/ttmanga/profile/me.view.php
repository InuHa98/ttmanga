<?php View::render_theme('layout.header', ['title' => $block_view['title']]); ?>

<?=themeController::load_css('css/profile.css');?>

<div class="section-profile-cover">
	<div id="preview_cover" class="section-profile-cover__bg-cover" style="background-image: url(<?=User::get_cover();?>);">
	</div>
	<div class="section-profile-cover__bg-alpha"></div>
	<div class="container">	
		<div role="btn-change-cover" class="container__cover-btn-change">
			<i class="fas fa-camera"></i>
			<span class="text">Chỉnh sửa ảnh bìa</span>
		</div>
	</div>
</div>
<div class="section-profile-infomation">
	<div class="container">
		<div id="preview_avatar">
			<?=render_avatar(Auth::$data, null, false, false, ['section-profile-infomation__avatar'], '<div role="btn-change-avatar" class="container__avatar-btn-change"><i class="fas fa-camera"></i></div>');?>
		</div>
		<div class="section-profile-infomation__info">
			<div class="section-profile-infomation__info-left">
				<div class="name-box">
					<div class="display-name"><?=ucwords($display_name);?></div>
				</div>
				<div>
					<span class="user-role" style="background: <?=_echo(Auth::$data['role_color']);?>">
						<?=_echo(Auth::$data['role_name']);?>
					</span>
				</div>
			</div>
			<div class="section-profile-infomation__info-right">

				<form method="POST">
					<input type="hidden" name="<?=profileController::INPUT_FORM_ACTION;?>" value="<?=profileController::ACTION_UPLOAD_IMAGE;?>">
					<input type="hidden" id="data-upload" name="<?=profileController::INPUT_FORM_DATA_IMAGE;?>">
					<input type="submit" class="save-upload" role="btn-save-upload">
				</form>
			</div>
		</div>
	</div>
</div>

<div class="container">
	<div class="row">
		<div class="col-xs-12 col-md-4 col-lg-3">
			<div class="box">
				<div class="box__body">
					<div class="username">@<?=ucwords(Auth::$data['username']);?></div>
					<div class="box__body-item">
						<span class="item-icon">
							<i class="fas fa-comments"></i>
						</span>
						<div>
							<span class="item-title">Bình luận:</span>
							<span class="item-text"><?=number_format($total_comments, 0, ',', '.');?></span>
						</div>
					</div>
					<div class="box__body-item">
						<span class="item-icon">
							<i class="far fa-calendar-alt"></i>
						</span>
						<div>
							<span class="item-title">Ngày tham gia:</span>
							<span class="item-text"><?=date('d/m/Y', Auth::$data['created_at']);?></span>
						</div>
					</div>
					<div class="box__body-item">
						<span class="item-icon">
							<i class="fas fa-mobile-alt"></i>
						</span>
						<div>
							<span class="item-title">Giới hạn thiết bị:</span>
							<span class="item-text"><?=User::count_limit_device();?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-md-8 col-lg-9">
			<div class="box">
				<div class="box__header">
					<div class="tabmenu-horizontal">
						<div class="tabmenu-horizontal__item <?=($block == profileController::BLOCK_INFOMATION ? 'active' : null);?>">
							<a href="<?=RouteMap::join('/'.profileController::BLOCK_INFOMATION, 'profile', ['id' => 'me']);?>">Thông tin cá nhân</a>
						</div>
						<div class="tabmenu-horizontal__item <?=($block == profileController::BLOCK_LOGINDEVICE ? 'active' : null);?>">
							<a href="<?=RouteMap::join('/'.profileController::BLOCK_LOGINDEVICE, 'profile', ['id' => 'me']);?>">Thiết bị đăng nhập</a>
						</div>
						<div class="tabmenu-horizontal__item <?=($block == profileController::BLOCK_SMILEY ? 'active' : null);?>">
							<a href="<?=RouteMap::join('/'.profileController::BLOCK_SMILEY, 'profile', ['id' => 'me']);?>">Nhãn dán</a>
						</div>
						<div class="tabmenu-horizontal__item <?=($block == profileController::BLOCK_CHANGEPASSWORD ? 'active' : null);?>">
							<a href="<?=RouteMap::join('/'.profileController::BLOCK_CHANGEPASSWORD, 'profile', ['id' => 'me']);?>">Đổi mật khẩu</a>
						</div>
						<div class="tabmenu-horizontal__item <?=($block == profileController::BLOCK_SETTINGS ? 'active' : null);?>">
							<a href="<?=RouteMap::join('/'.profileController::BLOCK_SETTINGS, 'profile', ['id' => 'me']);?>">Cài đặt</a>
						</div>
					</div>
				</div>
				<?php 
					if($block_view)
					{
						View::render_theme($block_view['view'], $block_view['data']);
					}
				?>
			</div>


		</div>
	</div>
</div>



<input id="input-upload-image" type="file" accept="image/*" style="display: none;">

<link rel="stylesheet" href="<?=APP_URL;?>/assets/css/cropper.css?v=<?=$_version;?>">
<script type="text/javascript" src="<?=APP_URL;?>/assets/script/cropper.js?v=<?=$_version;?>"></script>


<?=themeController::load_js('script/avatar-cover-upload.js');?>

<script type="text/javascript" src="<?=APP_URL;?>/assets/script/form-validator.js?v=<?=$_version;?>"></script>

<?php View::render_theme('layout.footer'); ?>