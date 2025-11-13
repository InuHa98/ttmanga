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

	<form id="form-validate" method="POST">
        <?=Security::insertHiddenToken();?>
        <?=profileController::insertHiddenAction(Interface_controller::ACTION_CHANGEPASSWORD);?>
		<div class="box__body">
			<div class="form-group limit--width">
				<label class="control-label">Mật khẩu mới</label>
                <div class="form-control">
                    <input class="form-input" id="new_password" name="<?=Interface_controller::INPUT_FORM_NEW_PASSWORD;?>" placeholder="Nhập mật khẩu mới" type="password">
                </div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label">Nhập lại mật khẩu</label>
                <div class="form-control">
				    <input class="form-input" id="confirm_password" name="<?=Interface_controller::INPUT_FORM_CONFIRM_PASSWORD;?>" placeholder="Nhập lại mật khẩu mới" type="password">
                </div>
            </div>
			<div class="form-group limit--width">
				<label class="control-label">Mật khẩu cũ</label>
                <div class="form-control">
				    <input class="form-input" id="current_password" name="<?=Interface_controller::INPUT_FORM_PASSWORD;?>" placeholder="Mật khẩu hiện tại" type="password">
                </div>
            </div>
		</div>
		<div class="box__footer">
			<button type="submit" class="btn btn--round pull-right">Đổi mật khẩu</button>
		</div>
	</form>



<script type="text/javascript">
	$(document).ready(function() {
        var min = <?=Auth::PASSWORD_MIN_LENGTH;?>;
        var max = <?=Auth::PASSWORD_MAX_LENGTH;?>;
        Validator({
            form: '#form-validate',
            selector: '.form-control',
            class_error: 'error',
            rules: {
                '#new_password': [
                    Validator.minLength(min, 'Mật khẩu mới phải từ '+min+' kí tự trở lên'),
                    Validator.maxLength(max, 'Mật khẩu mới phải ít hơn '+max+' kí tự')
                ],
                '#confirm_password': [
                    Validator.isRequired(),
                    Validator.isConfirmed(document.querySelector('#new_password'), 'Mật khẩu nhập lại không chính xác')
                ],
                '#current_password': [
                    Validator.isRequired()
                ]
            }
        });
    });
</script>