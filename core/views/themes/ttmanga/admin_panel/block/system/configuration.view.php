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
			<div class="form-group limit--width">
				<label class="control-label">Tên trang</label>
                <div class="form-control">
                    <input class="form-input" name="<?=DotEnv::APP_NAME;?>" placeholder="Tên website" type="text" value="<?=_echo($app_name);?>">
                </div>
			</div>
			<div class="form-group limit--width">
				<label class="control-label">Tiêu đề trang</label>
                <div class="form-control">
				    <input class="form-input" name="<?=DotEnv::APP_TITLE;?>" placeholder="Tiêu đề website" type="text" value="<?=_echo($app_title);?>">
                </div>
            </div>
			<div class="form-group">
				<label class="control-label">Giới thiệu trang</label>
                <div class="form-control">
				    <textarea class="form-textarea" name="<?=DotEnv::APP_DESCRIPTION;?>" placeholder="Mô tả website" type="text"><?=_echo($app_description);?></textarea>
                </div>
			</div>
            <div class="form-group limit--width">
				<label class="control-label">Giời hạn phân trang</label>
                <div class="form-control">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" role="btn-minus" class="btn btn-outline-light btn-dim"><i class="fa fa-minus"></i></button>
                        </div>
                        <input class="form-input only-number" name="<?=DotEnv::APP_LIMIT_ITEM_PAGE;?>" placeholder="Giới hạn phân tử trong một trang" type="text" value="<?=_echo($limit_item_page);?>">
                        <div class="input-group-append">
                            <button type="button" role="btn-plus" class="btn btn-outline-light btn-dim"><i class="fa fa-plus"></i></button>
                        </div>                        
                    </div>
                </div>
            </div>

			<div class="form-group limit--width">
				<label class="control-label">Email liên hệ</label>
                <div class="form-control">
				    <input class="form-input" name="<?=DotEnv::APP_EMAIL;?>" placeholder="Email liên hệ" type="text" value="<?=_echo($app_email);?>">
                </div>
            </div>

            <div class="form-group limit--width">
				<label class="control-label">Số lượt xem hot</label>
                <div class="form-control">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" role="btn-minus" class="btn btn-outline-light btn-dim"><i class="fa fa-minus"></i></button>
                        </div>
                        <input class="form-input only-number" name="<?=DotEnv::VIEW_HOT;?>" placeholder="Số lượt xem được coi là truyện hot" type="text" value="<?=_echo($view_hot);?>">
                        <div class="input-group-append">
                            <button type="button" role="btn-plus" class="btn btn-outline-light btn-dim"><i class="fa fa-plus"></i></button>
                        </div>                        
                    </div>
                </div>
            </div>

			<div class="form-group">
				<label class="control-label">Profile Upload Mode</label>
                
                <div class="form-control">
                    <div class="form-radio">
                        <input type="radio" id="profile_upload_mode_localhost" name="<?=DotEnv::PROFILE_UPLOAD_MODE;?>" value="<?=App::PROFILE_UPLOAD_MODE_LOCALHOST;?>" checked>
                        <label for="profile_upload_mode_localhost">Localhost</label>
                    </div>
                    <div class="form-radio">
                        <input type="radio" id="profile_upload_mode_imgur" name="<?=DotEnv::PROFILE_UPLOAD_MODE;?>" value="<?=App::PROFILE_UPLOAD_MODE_IMGUR;?>" <?=($profile_upload_mode == App::PROFILE_UPLOAD_MODE_IMGUR ? 'checked' : null);?>>
                        <label for="profile_upload_mode_imgur">Imgur</label>
                    </div>
                    <div class="label-desc">Lưu ảnh vào Localhost hoặc server thứ 3.</div>
				</div>
			</div>

            <div class="form-group">
				<label class="control-label">Yêu cầu đăng nhập</label>
                <div class="form-control">
                    <div class="form-switch">
                        <input type="checkbox" id="required_login" name="<?=DotEnv::APP_REQUIRED_LOGIN;?>" value="1" <?=($required_login ? 'checked' : null);?>>
                        <label for="required_login">Bật/Tắt</label>
                    </div>
                    <div class="label-desc">Bắt buộc đăng nhập để sử dụng các tính năng.</div>
				</div>
			</div>

            <div class="form-group">
				<label class="control-label">Hạn chế đăng nhập</label>
                <div class="form-control">
                    <div class="form-switch">
                        <input type="checkbox" id="limit_login" name="<?=DotEnv::LIMIT_LOGIN;?>" value="1" <?=($limit_login ? 'checked' : null);?>>
                        <label for="limit_login">Bật/Tắt</label>
                    </div>
                    <div class="label-desc">Giới hạn chỉ một thiết bị đăng nhập cùng lúc.</div>
				</div>
			</div>

            <div class="form-group">
				<label class="control-label">Mã hoá link ảnh</label>
                <div class="form-control">
                    <div class="form-switch">
                        <input type="checkbox" id="ENCODE_URL_IMAGE" name="<?=DotEnv::ENCODE_URL_IMAGE;?>" value="1" <?=($encode_url_image ? 'checked' : null);?>>
                        <label for="ENCODE_URL_IMAGE">Bật/Tắt</label>
                    </div>
                    <div class="label-desc">Chỉ áp dụng cho link ảnh blogger.</div>
				</div>
			</div>

            <div class="form-group limit--width">
				<label class="control-label">Ngôn ngữ mặc định:</label>
				<div class="form-control">
					<select class="form-select js-custom-select" name="<?=DotEnv::DEFAULT_LANGUAGE;?>" data-placeholder="Vui lòng chọn một ngôn ngữ" data-max-width="300px">
                    <?php 

                        foreach(Language::list() as $lang => $name)
                        {
                            echo '<option value="'.$lang.'" '.($default_language == $lang ? 'selected' : null).'>'.$name.'</option>';
                        }

                    ?>
						
                    </select>
				</div>
			</div>

            <div class="form-group limit--width">
				<label class="control-label">Giao diện mặc định:</label>
				<div class="form-control">
					<select class="form-select js-custom-select" name="<?=DotEnv::DEFAULT_THEME;?>" data-placeholder="Vui lòng chọn một giao diện" data-max-width="300px">
                    <?php 

                        foreach(themeController::list() as $arr => $name)
                        {
                            echo '<option value="'.$arr.'" '.($default_theme == $arr ? 'selected' : null).'>'.$name.'</option>';
                        }


                    ?>
						
                    </select>
				</div>
			</div>

            <div class="form-group">
				<label class="control-label">Imgur API</label>
                <div class="form-control">
                    <div class="input-group">
                        <input class="form-input" name="<?=DotEnv::IMGUR_CLIENT_ID;?>[]" placeholder="Client-ID" type="text" value="<?=(isset($imgur_client_id[0]) ? _echo($imgur_client_id[0]) : null);?>">
                        <div class="input-group-append">
                            <button type="button" role="btn-add-input" class="btn btn-outline-warning btn-dim"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                <?php if(isset($imgur_client_id[1])): ?>
                <?php
                    array_shift($imgur_client_id); foreach($imgur_client_id as $key): ?>
                    <div class="input-group">
                        <input class="form-input" name="<?=DotEnv::IMGUR_CLIENT_ID;?>[]" placeholder="Client-ID" type="text" value="<?=_echo($key);?>">
                        <div class="input-group-append">
                            <button type="button" role="btn-remove-input" class="btn btn-outline-danger btn-dim"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php endif; ?>
                </div>
            </div>
		<div class="d-flex justify-content-end my-4">
			<button type="submit" class="btn btn--round pull-right">Lưu lại</button>
		</div>
	</form>

<script type="text/javascript">
    (function() {
        $(document).ready(function() {
            $('[role="btn-add-input"]').on('click', function() {
                let input_group = $(this).parents('.input-group');
                let new_input = input_group.clone();
                new_input.find('input').val('');
                new_input.find('.input-group-append').html('<button type="button" role="btn-remove-input" class="btn btn-outline-danger btn-dim"><i class="fa fa-trash"></i></button>');
                input_group.parent().append(new_input);
            });

            $(document).on('click', '[role="btn-remove-input"]', function() {
                let input_group = $(this).parents('.input-group');
                input_group.remove();
            });
        });
    })();
</script>