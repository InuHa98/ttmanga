<?php

$title = isset($title) ? _echo($title) : env(DotEnv::APP_TITLE);
$description = isset($description) ? _echo($description) : null;
$description_image = isset($description_image) ? _echo($description_image) : null;

if(Auth::$isLogin == true)
{
	$display_name = ucwords(Auth::$data['username']);
	$username = Auth::$data['username'];
	$role_color = Auth::$data['role_color'];
	$role_name = Auth::$data['role_name'];
	$avatar = User::get_avatar();
	$render_avatar = render_avatar(Auth::$data);
}

$url_report = RouteMap::get('report');
if(isset($_chapter_id)) {
	$url_report = RouteMap::build_query([reportController::PARAM_CHAPTER_ID => $_chapter_id], 'report', ['block' => reportController::BLOCK_SUBMIT]);
} else if(isset($_manga_id)) {
	$url_report = RouteMap::build_query([reportController::PARAM_MANGA_ID => $_manga_id], 'report', ['block' => reportController::BLOCK_SUBMIT]);
}

?>
<!DOCTYPE html>
	<html lang="vi" xmlns="http://www.w3.org/1999/xhtml" prefix="fb: http://www.facebook.com/2008/fbml">
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="Content-Language" content="vi" />
		<meta http-equiv="X-UA-Compatible" content="IE=Edge" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<?php if(!isset($_chapter_id)): ?>
		<meta name="referrer" content="no-referrer" />
	<?php endif; ?>
		<!-- <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate"> -->
		<!-- <meta http-equiv="Pragma" content="no-cache">
		<meta http-equiv="Expires" content="0"> -->
		<meta name="viewport" content="<?=($_chapter_id ? 'width=device-width, initial-scale=1' : 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0');?>" />
		<!--meta name="referrer" content="no-referrer" -->

		<title><?=$title;?></title>
		<meta name="description" content="<?=$description;?>" />
		
		<meta property="og:title" content="<?=$title;?>" />
		<meta property="og:locale" content="vi_VN" />
		<meta property="og:type" content="website" />
		<meta property="og:url" content="<?=APP_URL.'/'.trim($_SERVER['REQUEST_URI'], "/");?>" />
		<meta property="og:image" content="<?=$description_image;?>" />
		<meta property="og:description" content="<?=$description;?>" />
		<meta property="og:site_name" content="<?=env(DotEnv::APP_NAME);?>" />


		<link rel="profile" href="http://gmpg.org/xfn/11">
		<link rel="icon" type="image/x-icon" href="<?=APP_URL;?>/assets/favico.ico">
		<link rel="shortcut icon" type="image/x-icon" href="<?=APP_URL;?>/assets/favico.ico">

		<link rel="stylesheet" type="text/css" href="<?=APP_URL;?>/assets/css/font-awesome/css/all.css?v=<?=$_version;?>" />
		<link rel="stylesheet" type="text/css" href="<?=APP_URL;?>/assets/themes/<?=themeController::$current_theme;?>/css/app.css?v=<?=$_version;?>" />

		<script type="text/javascript" src="<?=APP_URL;?>/assets/script/jquery-3.4.1.min.js?v=<?=$_version;?>"></script>
		<script type="text/javascript" src="<?=APP_URL;?>/assets/script/main.js?v=<?=$_version;?>"></script>
	</head>
	<body>

		<div class="side-nav-menu">
		<?php if(Auth::$isLogin == true): ?>
			<div class="profile-details">
				<?=$render_avatar;?>
				<div class="name-box">
					<div class="display-name"><strong><?=$display_name;?></strong></div>
					<div class="user-role" style="background: <?=$role_color;?>"><?=$role_name;?></div>
				</div>
				<a class="sign-out" href="<?=RouteMap::get('logout');?>">
					<i class="fal fa-sign-out"></i>
				</a>
			</div>
		<?php endif; ?>
			<ul class="side-nav-menu__items">
				<li class="side-nav-menu__items-title">
					Tài khoản:
				</li>
		<?php if(Auth::$isLogin == true): ?>
			<?php if(UserPermission::isAccessAdminPanel()): ?>
				<li class="side-nav-menu__items-link">
					<a href="<?=RouteMap::get('admin_panel');?>">
						<span><i class="fas fa-cogs"></i> Admin Panel</span>
						<?php if (isset($_count_approval_team) && $_count_approval_team > 0): ?>
							<span class="count-new-item"><?=$_count_approval_team;?></span>
						<?php endif; ?>
					</a>
				</li>
			<?php endif; ?>
				<li class="side-nav-menu__items-link">
					<a href="<?=RouteMap::get('profile', ['id' => 'me']);?>">
						<span><i class="fa fa-user"></i> Tài Khoản</span>
					</a>
				</li>
				<li class="side-nav-menu__items-link">
					<a href="<?=RouteMap::get('history');?>">
						<span><i class="fa fa-history"></i> Xem Gần Đây</span>
					</a>							
				</li>
				<li class="side-nav-menu__items-link">
					<a href="<?=RouteMap::get('bookmark');?>">
						<span><i class="fa fa-bookmark"></i> Truyện Theo Dõi</span>
						<?php if ($_count_bookmark > 0): ?>
							<span class="count-new-item"><?=$_count_bookmark;?></span>
						<?php endif; ?>
					</a>							
				</li>
				<li class="side-nav-menu__items-link">
					<a href="<?=RouteMap::get('my_team');?>">
						<span><i class="fas fa-layer-group"></i> Nhóm dịch của tôi</span>
						<?php if ($_count_team_error + $_count_team_member_approval > 0): ?>
							<span class="count-new-item"><?=$_count_team_error + $_count_team_member_approval;?></span>
						<?php endif; ?>
					</a>
				</li>
		<?php else: ?>
				<li class="side-nav-menu__items-link">
					<a href="<?=RouteMap::get('login');?>">
						<span><i class="fas fa-sign-in-alt"></i> Đăng nhập</span>
					</a>
				</li>
				<li class="side-nav-menu__items-link">
					<a href="<?=RouteMap::get('register');?>">
						<span><i class="fa fa-user-plus"></i> Đăng kí</span>
					</a>
				</li>
		<?php endif; ?>
				<li class="side-nav-menu__items-title">
					Danh sách manga:
				</li>
				<li class="side-nav-menu__items-group">
					<div class="side-nav-menu__items-group__title">
						<span class="group_text">
							<i class="fas fa-folder-open"></i>
							Thể loại
						</span>
						<span class="group_arrow">
							<i class="fas fa-chevron-right"></i>
						</span>
					</div>
					<ul class="side-nav-menu__items-group__items">
					<?php foreach($_genres as $id => $name): ?>
						<li>
                            <a href="<?=RouteMap::build_query([mangaController::PARAM_GENRES => $id], 'manga');?>"><?=_echo($name);?></a>
						</li>
					<?php endforeach; ?>
					</ul>
				</li>
				<li class="side-nav-menu__items-group">
					<div class="side-nav-menu__items-group__title">
						<span class="group_text">
							<span><i class="fas fa-list"></i> Danh sách manga</span>
						</span>
						<span class="group_arrow">
							<i class="fas fa-chevron-right"></i>
						</span>
					</div>
					<ul class="side-nav-menu__items-group__items">
						<li>
							<a href="<?=RouteMap::join('?sort=alphabet', 'manga');?>">Xếp theo Alphabet</a>
						</li>
						<li>
							<a href="<?=RouteMap::join('?sort=view', 'manga');?>">Xếp theo lượt xem</a>
						</li>
						<li>
							<a href="<?=RouteMap::join('?sort=update', 'manga');?>">Mới cập nhật</a>
						</li>
						<li>
							<a href="<?=RouteMap::join('?sort=new', 'manga');?>">Truyện mới</a>
						</li>
						<li>
							<a href="<?=RouteMap::join('?sort=follow', 'manga');?>">Theo dõi nhiều</a>
						</li>
					</ul>
				</li>
				<li class="side-nav-menu__items-link">
					<a class="p-3" href="<?=$url_report;?>">
						<span><i class="fas fa-exclamation-triangle"></i> Báo lỗi</span>
						<?php if ($_count_all_error > 0): ?>
							<span class="count-new-item"><?=$_count_all_error;?></span>
						<?php endif; ?>
					</a>
				</li>
			</ul>
		</div>

		<div class="side-nav-main">
			<div id="section-header" class="section-header">
				<div class="section-header__wrapper">
					<div id="btn_sidenav-menu" class="section-header__button">
						<i class="fas fa-bars"></i>
						<?php if ($_count_all_error + $_count_approval_team + $_count_team_error + $_count_team_member_approval > 0): ?>
							<span class="count-new-item"><?=$_count_all_error + $_count_approval_team + $_count_team_error + $_count_team_member_approval;?></span>
						<?php endif; ?>
					</div>

					<a class="section-header__logo" href="<?=APP_URL;?>">
						<img src="<?=APP_URL;?>/assets/images/logo.png">
					</a>

					<ul class="section-header__items">
						<li>
							<a href="<?=RouteMap::get('manga');?>">Danh sách</a>
						</li>
						<li <?=Router::$current_route == 'report' ? 'class="active"' : null;?>>
							<a href="<?=$url_report;?>">Báo lỗi<?=($_count_all_error > 0 ? ' <span class="count-new-item">'.$_count_all_error.'</span>' : '');?></a>
						</li>
					</ul>


					<form class="section-header__search" action="<?=RouteMap::get('search_manga');?>">
						<input data-search="input-keyword" class="section-header__search-keyword" type="text" name="<?=mangaController::INPUT_KEYWORD;?>" placeholder="Tìm kiếm...">
						<select data-search="input-type" class="js-custom-select section-header__search-type" data-max-width="100px" onchange="this.form.querySelector('.section-header__search-keyword').name = this.value">
							<option value="<?=mangaController::INPUT_KEYWORD;?>">Tên truyện</option>
							<option value="<?=mangaController::INPUT_TEAM;?>">Tên nhóm</option>
							<option value="<?=mangaController::INPUT_AUTHOR;?>">Tác giả</option>
						</select>
						<button type="submit" class="section-header__search-submit">
							<i class="fas fa-search"></i>
						</button>
						<div class="result-search-header" id="result-search"></div>
					</form>

					<div class="section-header__auth">
						<div class="section-header__auth-action">
							<div class="action__search" id="btn_mini-search">
								<i class="fas fa-search"></i>
							</div>
							<div class="mini-seach__box">
								<form method="GET" action="<?=RouteMap::get('search_manga');?>">
									<i class="fas fa-search"></i>
									<input type="text" data-search="input-keyword-mini" name="<?=mangaController::INPUT_KEYWORD;?>" placeholder="Tìm kiếm truyện...">
								</form>
							</div>

						<?php if(Auth::$isLogin == true): ?>
							<a class="action__message" href="<?=RouteMap::get('messenger');?>">
								<i class="fas fa-envelope"></i>
							<?php
								if($_count_message > 0) { 
									echo '<span class="count">'.number_format($_count_message, 0, ',', '.').'</span>';
								}
							?>
							</a>
							<div class="action__notification" id="btn_notification">
								<i class="fa fa-bell"></i>
							<?php
							
								$total_notifi = $_count_notification + $_count_bookmark + (isset($_count_approval_team) && $_count_approval_team ? 1 : 0);

								$notification_content = '';

								if($total_notifi > 0)
								{ 
									echo '<span class="count">'.number_format($total_notifi, 0, ',', '.').'</span>';
								}

								if(isset($_notification) && $_notification)
								{ 
									$notification_content = '<ul class="notification-list">';

									foreach($_notification as $item)
									{
										$user_from = [
											'id' => $item['user_from_id'],
											'name' => $item['user_from_name'],
											'username' => $item['user_from_username'],
											'avatar' => $item['user_from_avatar'],
											'user_ban' => $item['user_from_user_ban'],
											'role_color' => $item['user_from_role_color']
										];
										$notification_content .= '
										<li>
											<a class="notification-list__item" href="'.RouteMap::get('notification', ['id' => $item['id']]).'">
												'.render_avatar($user_from).'
												<div>
													<div class="notification-list__item-text">'.Notification::renderHTML($item, true).'</div>
													<div class="notification-list__item-time">'._time($item['created_at']).'</div>
												</div>
											</a>
										</li>';								
									}

									$notification_content .= '</ul>';
								}

							?>
								<div class="notification-header" id="notification-content">
									
									<div class="notification-header__content">
									<?php if(!empty($_count_approval_team) && $_count_approval_team > 0): ?>
										<a class="notification-bookmark" href="<?=RouteMap::get('admin_panel', ['group' => 'Team', 'block' => 'Approval']);?>">
											<div class="bookmark-icon">
												<i class="fas fa-layer-plus"></i>
											</div>
											<div class="bookmark-text">Có <strong><?=number_format($_count_approval_team, 0, ',', '.');?></strong> nhóm dịch mới cần xét duyệt</div>
										</a>
									<?php endif; ?>
									<?php if($_count_bookmark > 0):?>
										<a class="notification-bookmark" href="<?=RouteMap::get('bookmark', ['type' => 'new']);?>">
											<div class="bookmark-icon">
												<i class="fas fa-bookmark"></i>
											</div>
											<div class="bookmark-text"><strong><?=number_format($_count_bookmark, 0, ',', '.');?></strong> truyện đang theo dõi có chương mới</div>
										</a>
										<ul class="notification-list">
										<?php foreach($_lst_bookmark as $o): ?>
											<li>
												<a class="notification-list__item" href="<?=RouteMap::get('manga', ['id' => $o['id']]);?>">
													<img class="manga-image" src="<?=_echo($o['image']);?>" />
													<div>
														<div class="notification-list__item-text manga-name"><?=_echo($o['name']);?></div>
														<div class="notification-list__item-text"><?=_echo($o['name_last_chapter']);?></div>
														<div class="notification-list__item-time"><?=_time($o['created_last_chapter']);?></div>
													</div>
												</a>
											</li>
										<?php endforeach; ?>
										</ul>
									<?php endif; ?>

									<?php if(!$_count_bookmark && empty($_count_approval_team) && !$_count_notification): ?>
										<div class="notification-header__title">Thông báo</div>
										<span class="empty__notification">
											<div class="empty__notification-icon">
												<i class="fas fa-bell-slash"></i>
											</div>
											<div class="empty__notification-text">Không có thông báo nào.</div>
										</span>
									<?php else: ?>
										<?php if ($_count_notification): ?>
										<div class="notification-header__title <?=((!empty($_count_approval_team) && $_count_approval_team > 0) || $_count_bookmark > 0) ? 'dark' : null;?>"><i class="fa fa-bell"></i> <strong><?=number_format($_count_notification, 0, ',', '.');?></strong> thông báo mới</div>
										<?php endif; ?>
										<?=$notification_content;?>
									<?php endif; ?>
									</div>
									<div class="notification-header__footer">
										<a href="<?=RouteMap::get('notification');?>">Xem tất cả</a>
									</div>
								</div>
							</div>
						<?php endif; ?>
						</div>
						<div class="section-header__auth-infomation">
						<?php if(Auth::$isLogin != true): ?>
							<div class="auth-infomation">
								<span class="auth-infomation__username">Tài khoản</span>
								<span class="auth-infomation__avatar">
									<i class="fas fa-user-circle"></i>
								</span>
							</div>
							<ul class="auth-menu">
								<li>
									<a href="<?=RouteMap::get('login');?>">
										<span>
											<i class="fas fa-sign-in-alt"></i>
										</span>
										Đăng Nhập
									</a>
								</li>
								<li>
									<a href="<?=RouteMap::get('register');?>">
										<span>
											<i class="fa fa-user-plus"></i>
										</span>
										Đăng Kí
									</a>
								</li>
							</ul>
						<?php else: ?>
							<div class="auth-infomation">
								<span class="auth-infomation__avatar">
									<?=$render_avatar;?>
								</span>
							</div>
							<ul class="auth-menu">
								<li class="infomation">
									<?=$render_avatar;?>
									<div class="name-box">
										<div class="display-name"><strong><?=$display_name;?></strong></div>
										<div class="user-role" style="background: <?=$role_color;?>"><?=$role_name;?></div>
									</div>
								</li>
							<?php if(UserPermission::isAccessAdminPanel()): ?>
								<li>
									<a class="color-green" href="<?=RouteMap::get('admin_panel');?>">
										<span><i class="fas fa-cogs"></i> Admin Panel</span>
										<?php if (isset($_count_approval_team) && $_count_approval_team > 0): ?>
											<span class="count-new-item"><?=$_count_approval_team;?></span>
										<?php endif; ?>
									</a>
								</li>
							<?php endif; ?>
								<li>
									<a href="<?=RouteMap::get('profile', ['id' => 'me']);?>">
										<span><i class="fa fa-user"></i> Tài Khoản</span>
									</a>							
								</li>
								<li>
									<a href="<?=RouteMap::get('history');?>">
										<span><i class="fa fa-history"></i> Xem Gần Đây</span>
									</a>							
								</li>
								<li>
									<a href="<?=RouteMap::get('bookmark');?>">
										<span><i class="fa fa-bookmark"></i> Truyện Theo Dõi</span>
										<?php if ($_count_bookmark > 0): ?>
											<span class="count-new-item"><?=$_count_bookmark;?></span>
										<?php endif; ?>
									</a>							
								</li>
								<li>
									<a href="<?=RouteMap::get('my_team');?>">
										<span><i class="fas fa-layer-group"></i> Nhóm dịch của tôi</span>
										<?php if ($_count_team_error + $_count_team_member_approval > 0): ?>
											<span class="count-new-item"><?=$_count_team_error + $_count_team_member_approval;?></span>
										<?php endif; ?>
									</a>
								</li>
								<li>
									<a href="<?=RouteMap::get('logout');?>">
										<span><i class="fa fa-power-off"></i> Thoát</span>
									</a>							
								</li>
							</ul>
						<?php endif; ?>
						</div>
					</div>
				</div>
			</div>