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
				<div>Truyện có yếu tố chỉ dành cho lứa tuổi <span class="text-danger"><?=(15 + $manga['type']);?>+</span> trở lên. Do đó truyện có thể chứa những nội dung bạo lực cao, máu me, tình dục. Bạn có chắc mình vẫn muốn xem nó?</div>
				<div class="d-flex justify-content-center align-items-center flex-wrap gap-2 mt-3">
					<button type="submit" name="submit" class="btn btn--rounds btn--warning btn--small">Tiếp tục <i class="fas fa-chevron-double-right"></i></button>
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