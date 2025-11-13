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
        <?=profileController::insertHiddenAction(Interface_controller::ACTION_INFOMATION);?>
		<div class="box__body">
			<div class="form-group limit--width">
				<label class="control-label">Name</label>
                <div class="form-control">
                    <input class="form-input" name="<?=Interface_controller::INPUT_FORM_NAME;?>" placeholder="Tên hiển thị" type="text" value="<?=_echo($name);?>">
                </div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label">Date of birth</label>
                <div class="form-control">
				    <input class="form-input" id="valid_date_of_birth" name="<?=Interface_controller::INPUT_FORM_DATE_OF_BIRTH;?>" placeholder="day/month/year" type="text" value="<?=_echo($date_of_birth);?>">
                </div>
            </div>
			<div class="form-group">
				<label class="control-label">Giới tính</label>
                
                <div class="form-control">
                    <div class="form-radio">
                        <input type="radio" id="sex_unknown" name="<?=Interface_controller::INPUT_FORM_SEX;?>" value="<?=User::SEX_UNKNOWN;?>" checked>
                        <label for="sex_unknown">Không xác định</label>
                    </div>
                    <div class="form-radio">
                        <input type="radio" id="sex_male" name="<?=Interface_controller::INPUT_FORM_SEX;?>" value="<?=User::SEX_MALE;?>" <?=($sex == User::SEX_MALE ? 'checked' : null);?>>
                        <label for="sex_male">Nam</label>
                    </div>
                    <div class="form-radio">
                        <input type="radio" id="sex_female" name="<?=Interface_controller::INPUT_FORM_SEX;?>" value="<?=User::SEX_FEMALE;?>" <?=($sex == User::SEX_FEMALE ? 'checked' : null);?>>
                        <label for="sex_female">Nữ</label>
                    </div>
				</div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label">Email</label>
                <div class="form-control">
				    <input id="email" class="form-input disabled" placeholder="Địa chỉ email" type="text" value="<?=_echo($email);?>">
				    <button class="btn btn--small" role="change-email">Thay đổi</button>
                </div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label">Facebook</label>
                <div class="form-control">
				    <input id="facebook" class="form-input" name="<?=Interface_controller::INPUT_FORM_FACEBOOK;?>" placeholder="https://facebook.com/xxx" type="text" value="<?=_echo($facebook);?>">
                </div>
			</div>
			<div class="form-group">
				<label class="control-label">Giới thiệu</label>
                <div class="form-control">
				    <textarea class="form-textarea" name="<?=Interface_controller::INPUT_FORM_BIO;?>" placeholder="Giới thiệu bản thân" type="text"><?=_echo($bio);?></textarea>
                </div>
			</div>
		</div>
		<div class="box__footer">
			<button type="submit" class="btn btn--round pull-right">Cập nhật</button>
		</div>
	</form>



<script type="text/javascript">
	$(document).ready(function(){

        Validator({
            form: '#form-validate',
            selector: '.form-control',
            class_error: 'error',
            class_message: null,
            rules: {
                '#valid_date_of_birth': function(value) {
                    return !value || /^([0-9]+){1,2}\/([0-9]+){1,2}\/([0-9]+){4,4}$/g.test(value) ? undefined : 'Định dạng ngày sinh không hợp lệ';
                },
                '#facebook': function(value) {
                    return !value || /^(\s+)?https?:\/\/((.*)\.)?(fb\.com|facebook\.com)\/(.*?)$/u.test(value) ? undefined : 'Liên kết không hợp lệ';
                }
            }
        });

        $(document).on('click', '[role=change-email]', function() {
            var idForm = 'dialogForm';

            $.dialogShow({
                title: 'Change email',
                content: '\
                    <form id="'+idForm+'" method="post">\
                        <?=Security::insertHiddenToken();?>\
                        <?=profileController::insertHiddenAction(Interface_controller::ACTION_CHANGE_EMAIL);?>\
                        <div class="form-group">\
                            <label class="control-label--mini">New Email:</label>\
                            <div class="form-control">\
                                <input type="email" class="form-input" name="<?=Interface_controller::INPUT_FORM_EMAIL;?>" placeholder="Nhập email mới" value="'+$('#email').val()+'">\
                            </div>\
                        </div>\
                        <div class="form-group">\
                            <label class="control-label--mini">Password:</label>\
                            <div class="form-control">\
                                <input type="password" class="form-input" name="password" placeholder="Mật khẩu cũ">\
                            </div>\
                        </div>\
                    </form>',
                button: {
                    confirm: 'Change',
                    cancel: 'Cancel'
                },
                isCenter: false,
                bgHide: false,
                onInit: function() {
                    Validator({
                        form: '#'+idForm,
                        selector: '.form-control',
                        class_error: 'error',
                        class_message: null,
                        rules: {
                            '[type=email]': [
                                Validator.isRequired(),
                                Validator.isEmail()
                            ],
                            '[type=password]': Validator.isRequired()
                        }
                    });
                },
                onBeforeConfirm: function() {
                    $('#' + idForm).submit();
                    return false;
                }
            });
        });

});
</script>