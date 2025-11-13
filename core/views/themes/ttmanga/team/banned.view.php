<?php View::render_theme('layout.header', compact('title')); ?>

<div class="section-sub-header">
	<div class="container">
        <span>Cảnh báo</span>
    </div>
</div>

<div class="container">
	<form method="POST" class="warning-manga">
		<div class="msg-error show">
			<div class="msg-error__text">
				<div>Nhóm dịch <span class="text-danger"><?=_echo($team['name']);?></span> đã bị cấm bởi <a target="_blank" class="user-infomation" href="<?=RouteMap::get('profile', ['id' => $user_ban['id']]);?>"><?=render_avatar($user_ban, null, true, true);?></a>. Hiện tại bạn sẽ không thể tham gia quản lý và upload truyện. Vui lòng liên hệ người cấm để biết thêm thông tin chi tiết!!!</div>
				<div class="d-flex justify-content-center align-items-center flex-wrap gap-2 mt-3">
					<a class="btn btn--rounds btn--gray btn--small" href="<?=APP_URL;?>"><i class="fas fa-home"></i> Về trang chủ</a>
				</div>
			</div>
			<div class="msg-error__image">
				<img src="<?=APP_URL;?>/assets/images/model.png">
			</div>
		</div>
	</form>
</div>

<?php View::render_theme('layout.footer'); ?>