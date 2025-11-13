<?php View::render_theme('layout.header', ['title' => $title]); ?>

<?=themeController::load_css('css/profile.css');?>

<div class="section-profile-cover">
	<div id="preview_cover" class="section-profile-cover__bg-cover" style="background-image: url(<?=User::get_cover($user);?>);">
	</div>
	<div class="section-profile-cover__bg-alpha"></div>
</div>
<div class="section-profile-infomation">
	<div class="container">
		<div id="preview_avatar">
			<?=render_avatar($user, null, false, false, ['section-profile-infomation__avatar']);?>
		</div>
		<div class="section-profile-infomation__info">
			<div class="section-profile-infomation__info-left">
				<div class="name-box">
					<div class="display-name <?=($user['user_ban'] != User::IS_ACTIVE ? 'user-banned' : null);?>"><?=ucwords($display_name);?></div>
				</div>
				<div>
					<?=User::get_role($user);?>
				</div>
				
			</div>
			<div class="section-profile-infomation__info-right">
			<?php if(Auth::$isLogin == true): ?>
				<a class="send-message" href="<?=RouteMap::get('messenger', ['block' => 'new', 'id' => $user['id']]);?>">
					<i class="far fa-envelope"></i>
					<span> Gửi tin nhắn</span>
				</a>
			<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<div class="container">
	<div class="row">
		<div class="col-xs-12 col-md-4 col-lg-3">
			<div class="box">
				<div class="box__body">
					<div class="username">@<?=ucwords($user['username'] ?? '');?></div>
					<div class="box__body-item">
						<span class="item-icon">
							<i class="fas fa-comments"></i>
						</span>
						<div>
							<span class="item-title">Bình luận:</span>
							<span class="item-text"><?=number_format($total_comments, 0, ',', '.');?></span>
						</div>
					</div>
				<?php if(User::get_setting($user, 'hide_info') != true): ?>

				<?php if(!empty($user['name'])): ?>
					<div class="box__body-item">
						<span class="item-icon">
							<i class="fab fa-autoprefixer"></i>
						</span>
						<div>
							<span class="item-title">Họ tên:</span>
							<span class="item-text"><?=_echo($user['name']);?></span>
						</div>
					</div>
				<?php endif; ?>

					<div class="box__body-item">
						<span class="item-icon">
							<i class="fas fa-venus-mars"></i>
						</span>
						<div>
							<span class="item-title">Giới tính:</span>
							<span class="item-text"><?=User::get_sex($user);?></span>
						</div>
					</div>

				<?php if(!empty($user['date_of_birth'])): ?>
					<div class="box__body-item">
						<span class="item-icon">
							<i class="fas fa-birthday-cake"></i>
						</span>
						<div>
							<span class="item-title">Sinh nhật:</span>
							<span class="item-text"><?=_echo($user['date_of_birth']);?></span>
						</div>
					</div>
				<?php endif; ?>

				<?php endif; ?>

					<div class="box__body-item">
						<span class="item-icon">
							<i class="far fa-calendar-alt"></i>
						</span>
						<div>
							<span class="item-title">Ngày tham gia:</span>
							<span class="item-text"><?=date('d/m/Y', $user['created_at']);?></span>
						</div>
					</div>

				<?php if(!empty($user['facebook'])): ?>
					<div class="box__body-item">
						<span class="item-icon">
							<i class="fab fa-facebook"></i>
						</span>
						<div>
							<span class="item-title">Facebook:</span>
							<span class="item-text"><a target="_blank" href="<?=$facebook = _echo($user['facebook']);?>"><?=$facebook;?></a></span>
						</div>
					</div>
				<?php endif; ?>

				<?php if(!empty($team['name'])): ?>
					<div class="box__body-item">
						<span class="item-icon">
							<i class="fas fa-layer-group"></i>
						</span>
						<div>
							<span class="item-title">Nhóm dịch:</span>
							<span class="item-text">
								<a href="<?=RouteMap::get('team', ['name' => $team['name']]);?>"><?=_echo($team['name']);?></a>
							</span>
						</div>
					</div>
				<?php endif; ?>

				<?php if(!empty($user['bio'])): ?>
					<div class="user-bio">
						<?=_echo($user['bio'], true);?>
					</div>
				<?php endif; ?>
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-md-8 col-lg-9">
			<div class="box">
				<div class="box__header">
					<div class="tabmenu-horizontal">
						<div class="tabmenu-horizontal__item <?=($block == profileController::BLOCK_MANGA_UPLOAD ? 'active' : null);?>">
							<a href="<?=RouteMap::join('/'.profileController::BLOCK_MANGA_UPLOAD, 'profile', ['id' => $user['id']]);?>">Truyện đã đăng</a>
						</div>
						<div class="tabmenu-horizontal__item <?=($block == profileController::BLOCK_MANGA_JOIN ? 'active' : null);?>">
							<a href="<?=RouteMap::join('/'.profileController::BLOCK_MANGA_JOIN, 'profile', ['id' => $user['id']]);?>">Truyện đang tham gia</a>
						</div>
					</div>
				</div>
				<div class="box__body">
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
</div>

<?php View::render_theme('layout.footer'); ?>