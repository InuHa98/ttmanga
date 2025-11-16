<?php View::render_theme('layout.header', ['title' => $title]); ?>


<div class="section-sub-header">
	<div class="container">
        <span>Đăng ký nhóm</span>
    </div>
</div>

<div class="container">
	<div class="row">

		<div class="col-xs-12 col-md-4 col-lg-3">
			<div class="box box--list">
				<div class="box__body">
					<a class="box__body-item" href="<?=RouteMap::get('my_team');?>">
						<span class="item-icon">
							<i class="fas fa-layer-group"></i>
						</span>
						<div>
							<span class="item-title">Tham gia nhóm dịch</span>
						</div>
					</a>

					<a class="box__body-item active" href="<?=RouteMap::get('my_team', ['block' => teamController::BLOCK_CREATE_TEAM]);?>">
						<span class="item-icon">
							<i class="fas fa-layer-plus"></i>
						</span>
						<div>
							<span class="item-title">Đăng ký nhóm dịch</span>
						</div>
					</a>
				</div>
			</div>
		</div>

		<div class="col-xs-12 col-md-8 col-lg-9">

			<?php
			if($error)
			{
				echo '<div class="alert alert--error">'.$error.'</div>';
			}
			else if($success)
			{
				echo '<div class="alert alert--success">'.$success.'</div>';
			}

			?>


		<?php if($request_join_team): ?>
			<div class="alert alert--error">Bạn đang yêu cầu tham gia vào nhóm dịch khác. Vui lòng huỷ yêu cầu tham gia nhóm để có thể tạo nhóm mới.</div>
		<?php elseif($request_create_team): ?>
			<div class="alert alert--warning">Đang chờ quản trị viên chấp thuận yêu cầu tạo nhóm.</div>
		<?php else: ?>

			<div class="box">
				<form id="form-validate" method="POST">
					<?=Security::insertHiddenToken();?>
					<div class="box__header">Đăng ký nhóm dịch</div>
					<div class="box__body">
						<div class="form-group limit--width">
							<label class="control-label">Tên nhóm dịch</label>
							<div class="form-control">
								<input class="form-input" name="<?=teamController::INPUT_NAME;?>" placeholder="Tên nhóm dịch" type="text" value="<?=_echo($name);?>">
							</div>
						</div>

						<div class="form-group">
							<label class="control-label">Ghi chú</label>
							<div class="form-control">
								<textarea class="form-textarea" name="<?=teamController::INPUT_NOTE;?>" placeholder="Vui lòng nhập đủ thông tin yêu cầu"><?=_echo($note);?></textarea>
								<div class="label-desc">• Vui lòng cung cấp đầy đủ tài khoản và mật khẩu 1 gmail.</div>
								<div class="label-desc">• Chỉ chấp nhận gmail sạch thông tin chưa hoặc không sử dụng nữa.</div>
								<div class="label-desc">• Tài khoản gmail này dùng để upload ảnh lên server Google nên sẽ được đổi mật khẩu đề tiện quản lý.</div>
							</div>
						</div>
					</div>
					<div class="box__footer">
						<button type="submit" class="btn btn--round pull-right">Gửi yêu cầu</button>
						<a href="<?=Request::referer(RouteMap::get('my_team'));?>" class="btn btn--round btn--gray pull-right">Trở lại</a>
					</div>
				</form>
			</div>

		<?php endif; ?>

		</div>
	</div>
</div>

<script type="text/javascript" src="<?=APP_URL;?>/assets/script/form-validator.js?v=<?=$_version;?>"></script>

<script type="text/javascript">
    $(document).ready(function() {
        
		Validator({
            form: '#form-validate',
            selector: '.form-control',
            class_error: 'error',
            class_message: null,
            rules: {
                'input': [
					Validator.isRequired('Vui lòng nhập nội tên nhóm dịch')
				],
                'textarea': [
					Validator.isRequired('Vui lòng nhập thông tin bổ sung')
				],
            }
        });
		
        $(document).on('click', '[role="join-team"]', function(e) {
            e.preventDefault();

			const id = $(this).attr('data-id');
			if(!id) {
                return $.toastShow('Không tìm thấy id nhóm dịch', {
					type: 'error',
					timeout: 3000
				});	;
			}

            const form = $(`
            <form method="post" class="margin-b-4">
                <?=Security::insertHiddenToken();?>
                <input type="hidden" name="<?=teamController::INPUT_ACTION;?>" value="<?=teamController::ACTION_JOIN;?>">
                    <input type="hidden" name="<?=InterFaceRequest::ID;?>" value="${id}">
                <div class="dialog-label">Ghi chú:</div>
                <div class="form-group">
                    <div class="form-control">
                        <textarea class="form-textarea" name="<?=teamController::INPUT_NOTE;?>" placeholder="Có thể bỏ trống"></textarea>
                    </div>
                </div>
            </form>`);


			$.dialogShow({
				title: 'Yêu cầu tham gia nhóm dịch',
				content: form,
				button: {
					confirm: 'Gửi yêu cầu',
					cancel: 'Huỷ'
				},
				bgHide: false,
                isCenter: true,
				onBeforeConfirm: function(){
                    form.submit();
                    return false;
				}
			});
        });

		$(document).on('click', '[role="cancel-join-team"]', function(e) {
            e.preventDefault();

			const id = $(this).attr('data-id');
			if(!id) {
                return $.toastShow('Không tìm thấy id nhóm dịch', {
					type: 'error',
					timeout: 3000
				});	;
			}

            const form = $(`
            <form method="post" class="margin-b-4">
                <?=Security::insertHiddenToken();?>
                <input type="hidden" name="<?=teamController::INPUT_ACTION;?>" value="<?=teamController::ACTION_CANCEL_JOIN;?>">
                <input type="hidden" name="<?=InterFaceRequest::ID;?>" value="${id}">
                <div class="text-danger">Bạn thực sự muốn huỷ yêu cầu tham gia nhóm này?</div>
            </form>`);


			$.dialogShow({
				title: 'Huỷ yêu cầu tham gia nhóm',
				content: form,
				button: {
					confirm: 'Huỷ yêu cầu',
					cancel: 'Huỷ'
				},
				bgHide: false,
                isCenter: true,
				onBeforeConfirm: function(){
                    form.submit();
                    return false;
				}
			});
        });

    });
</script>

<?php View::render_theme('layout.footer'); ?>