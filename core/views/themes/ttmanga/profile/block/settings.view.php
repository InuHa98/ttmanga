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
        <?=profileController::insertHiddenAction(Interface_controller::ACTION_SETTINGS);?>
		<div class="box__body">
			<div class="form-group">
				<label class="control-label">Ẩn thông tin:</label>
				<div class="form-control">
                    <div class="form-switch">
                        <input type="checkbox" id="hide_info" name="<?=Interface_controller::INPUT_FORM_HIDE_INFO;?>" value="1" <?=($hide_info == true ? 'checked' : null);?>>
                        <label for="hide_info">Bật/Tắt</label>
                    </div>
					<div class="label-desc">Người khác sẽ không thể thấy thông tin cá nhân của bạn (họ tên, ngày sinh, giới tính).</div>
				</div>
			</div>

			<!-- <div class="form-group limit--width">
				<label class="control-label">Giới hạn truyện:</label>
				<div class="form-control">
					<select class="form-select js-custom-select" name="<?=Interface_controller::INPUT_FORM_LIMIT_AGE;?>">
						<option value="0">Không giới hạn độ tuổi</option>
						<option <?=($limit_age == 16 ? 'selected': null);?> value="16">Không hiển thị manga 16+</option>
						<option <?=($limit_age == 17 ? 'selected': null);?> value="17">Không hiển thị manga 17+</option>
						<option <?=($limit_age == 18 ? 'selected': null);?> value="18">Không hiển thị manga 18+</option>
					</select>
					<div class="label-desc">Ẩn manga theo độ tuổi.</div>
				</div>
			</div> -->

			<div class="form-group limit--width">
				<label class="control-label">Ngôn ngữ:</label>
				<div class="form-control">
					<select class="form-select js-custom-select" name="<?=Interface_controller::INPUT_FORM_LANGUAGE;?>">
                    <?php 

                        foreach(Language::list() as $lang => $name)
                        {
                            echo '<option value="'.$lang.'" '.($language == $lang ? 'selected' : null).'>'.$name.'</option>';
                        }

                    ?>
                    </select>
				</div>
			</div>

			<div class="form-group limit--width">
				<label class="control-label">Giao diện:</label>
				<div class="form-control">
					<select class="form-select js-custom-select" name="<?=Interface_controller::INPUT_FORM_THEME;?>">
                    <?php 

                        foreach(themeController::list() as $arr => $name)
                        {
                            echo '<option value="'.$arr.'" '.($theme == $arr ? 'selected' : null).'>'.$name.'</option>';
                        }

                    ?>
						
                    </select>
				</div>
			</div>
		</div>
		<div class="box__footer">
			<button type="submit" class="btn btn--round pull-right">Lưu lại</button>
		</div>
	</form>



<script type="text/javascript">
	$(document).ready(function() {
        Validator({
            form: '#form-validate',
            selector: '.form-control',
            class_error: 'error',
            rules: {
                'input[type=radio]': Validator.isRequired(),
                'select': Validator.isRequired()
            }
        });
    });
</script>