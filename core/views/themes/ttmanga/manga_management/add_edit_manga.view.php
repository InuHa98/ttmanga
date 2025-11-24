<div class="section-sub-header">
	<div class="container">
        <span><?=(empty($manga) ? 'Thêm truyện mới' : 'Sửa truyện');?></span>
    </div>
</div>

<div class="container">
	<form id="form-validate" method="POST">
		<div class="row">
			<div class="col-xs-12">
			<?php if (empty($manga)): ?>
				<a href="<?=RouteMap::get('my_team');?>"><i class="fas fa-chevron-left"></i> Danh sách truyện</a>
			<?php else: ?>
				<a href="<?=RouteMap::get('manga_management', ['action' => mangaManagementController::ACTION_DETAIL, 'id' => $manga['id']]);?>"><i class="fas fa-chevron-left"></i> <?=_echo($manga['name']);?></a>
			<?php endif; ?>
			</div>

		<?php if (!empty($_team['rule'])):
			$rules = array_filter(explode("\n", $_team['rule']));
		?>
			<div class="col-xs-12">
				<div class="rule-team">
					<div class="rule-team__title">Quy định upload của nhóm:</div>
				<?php foreach($rules as $r): ?>
					<div class="rule-team__text">- <?=_echo($r);?></div>
				<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

			<div class="col-md-12 col-lg-9">
			<?php
				if($error)
				{
					echo '<div class="alert alert--error mt-0">'.$error.'</div>';
				}
				else if($success)
				{
					echo '<div class="alert alert--success mt-0">'.$success.'</div>';
				}
			?>
			<?=Security::insertHiddenToken();?>
				<div class="form-group">
					<label class="control-label--mini">Tên truyện:<span class="text-danger">*</span></label>
					<div class="form-control">
						<input type="text" class="form-input required" placeholder="Tên truyện" name="<?=mangaManagementController::INPUT_NAME;?>" value="<?=(!empty($name) ? _echo($name) : null);?>"/>
					</div>
				</div>
				
				<div class="form-group">
					<label class="control-label--mini">Tên khác:</label>
					<div class="form-control">
						<div class="input-group">
							<input class="form-input" name="<?=mangaManagementController::INPUT_NAME_OTHER;?>[]" placeholder="Tên gọi khác" type="text" value="<?=(!empty($name_other[0]) ? _echo($name_other[0]) : null);?>">
							<div class="input-group-append">
								<button type="button" role="btn-add-input" class="btn btn-outline-warning btn-dim"><i class="fa fa-plus"></i></button>
							</div>
						</div>
					<?php if(!empty($name_other[1])): ?>
					<?php
						array_shift($name_other); foreach($name_other as $key): ?>
						<div class="input-group">
							<input class="form-input" name="<?=mangaManagementController::INPUT_NAME_OTHER;?>[]" placeholder="Tên gọi khác" type="text" value="<?=_echo($key);?>">
							<div class="input-group-append">
								<button type="button" role="btn-remove-input" class="btn btn-outline-danger btn-dim"><i class="fa fa-trash"></i></button>
							</div>
						</div>
					<?php endforeach; ?>
					<?php endif; ?>
					</div>
				</div>

				<div class="form-group">
					<label class="control-label--mini">Tác giả:<span class="text-danger">*</span></label>
					<div class="form-control">
						<div class="input-group">
							<input class="form-input required" name="<?=mangaManagementController::INPUT_AUTH;?>[]" placeholder="Tên tác giả" type="text" value="<?=(!empty($author[0]) ? _echo($author[0]) : null);?>">
							<div class="input-group-append">
								<button type="button" role="btn-add-input" class="btn btn-outline-warning btn-dim"><i class="fa fa-plus"></i></button>
							</div>
						</div>
					<?php if(!empty($author[1])): ?>
					<?php
						array_shift($author); foreach($author as $key): ?>
						<div class="input-group">
							<input class="form-input required" name="<?=mangaManagementController::INPUT_AUTH;?>[]" placeholder="Tên tác giả" type="text" value="<?=_echo($key);?>">
							<div class="input-group-append">
								<button type="button" role="btn-remove-input" class="btn btn-outline-danger btn-dim"><i class="fa fa-trash"></i></button>
							</div>
						</div>
					<?php endforeach; ?>
					<?php endif; ?>
					</div>
				</div>

				<div class="form-group">
					<label class="control-label--mini">Nhóm dịch:<span class="text-danger">*</span></label>
					<div class="form-control">
						<div class="input-group">
							<input class="form-input required" name="<?=mangaManagementController::INPUT_TEAM;?>[]" placeholder="Tên nhóm dịch" type="text" value="<?=(!empty($teams[0]) ? _echo($teams[0]) : null);?>">
							<div class="input-group-append">
								<button type="button" role="btn-add-input" class="btn btn-outline-warning btn-dim"><i class="fa fa-plus"></i></button>
							</div>
						</div>
					<?php if(!empty($teams[1])): ?>
					<?php
						array_shift($teams); foreach($teams as $key): ?>
						<div class="input-group">
							<input class="form-input required" name="<?=mangaManagementController::INPUT_TEAM;?>[]" placeholder="Tên nhóm dịch" type="text" value="<?=_echo($key);?>">
							<div class="input-group-append">
								<button type="button" role="btn-remove-input" class="btn btn-outline-danger btn-dim"><i class="fa fa-trash"></i></button>
							</div>
						</div>
					<?php endforeach; ?>
					<?php endif; ?>
					</div>
				</div>

				<div class="form-group">
					<label class="control-label--mini">Thể loại:<span class="text-danger">*</span></label>
					<div class="form-control">
						<div class="genre-list">
						<?php
							foreach($lst_genres as $o):
								$is_include = isset($genres[$o['id']]) && $genres[$o['id']] == 1;
						?>
							<div class="state-btn <?=($is_include ? 'include' : null);?>" title="<?=_echo($o['text']);?>">
								<select class="required" name="<?=mangaManagementController::INPUT_GENRES;?>[<?=$o['id'];?>]">
									<option value="0"></option>
									<option value="1" <?=($is_include ? 'selected' : null);?>></option>
								</select>
								<label><?=_echo($o['name']);?></label>
							</div>
						<?php endforeach; ?>
						</div>
					</div>
				</div>

				<div class="form-group">
					<label class="control-label--mini">Tình trạng:</label>
					<div class="form-control">
						<div class="form-radio">
							<input type="radio" id="status_ongoing" name="<?=mangaManagementController::INPUT_STATUS;?>" value="<?=Manga::STATUS_ONGOING;?>" checked>
							<label for="status_ongoing">Đang tiến hành</label>
						</div>
						<div class="form-radio">
							<input type="radio" id="status_complete" name="<?=mangaManagementController::INPUT_STATUS;?>" value="<?=Manga::STATUS_COMPLETE;?>" <?=($status == Manga::STATUS_COMPLETE ? 'checked' : null);?>>
							<label for="status_complete">Đã hoàn thành</label>
						</div>
						<div class="form-radio">
							<input type="radio" id="status_drop" name="<?=mangaManagementController::INPUT_STATUS;?>" value="<?=Manga::STATUS_DROP;?>" <?=($status == Manga::STATUS_DROP ? 'checked' : null);?>>
							<label for="status_drop">Tạm ngưng</label>
						</div>
					</div>
				</div>

				<div class="form-group">
					<label class="control-label--mini">Cảnh báo:</label>
					<div class="form-control">
						<select class="form-select js-custom-select" name="<?=mangaManagementController::INPUT_TYPE;?>" data-placeholder="Cảnh báo" data-max-width="300px">
							<option value="<?=Manga::TYPE_NOT_WARNING;?>">Không</option>
							<option value="<?=Manga::TYPE_WARNING_16;?>" <?=($type == Manga::TYPE_WARNING_16 ? 'selected' : null);?>>Thể loại 16+</option>
							<option value="<?=Manga::TYPE_WARNING_17;?>" <?=($type == Manga::TYPE_WARNING_17 ? 'selected' : null);?>>Thể loại 17+</option>
							<option value="<?=Manga::TYPE_WARNING_18;?>" <?=($type == Manga::TYPE_WARNING_18 ? 'selected' : null);?>>Thể loại 18+</option>
						</select>
					</div>
				</div>

				<div class="form-group">
					<label class="control-label--mini">Link ảnh:<span class="text-danger">*</span></label>
					<div class="form-control">
						<input type="text" class="form-input link-image" id="link-image" placeholder="https://" name="<?=mangaManagementController::INPUT_IMAGE;?>" value="<?=(!empty($image) ? _echo($image) : null);?>" />
						<span>Hoặc</span>
						<button type="button" class="btn btn--gray btn--small" id="btn-upload-image"><i class="fas fa-file-upload"></i> Upload</button>
					</div>
				</div>

				<div class="form-group">
					<label class="control-label--mini">Link ảnh bìa:</label>
					<div class="form-control">
						<input type="text" class="form-input link" id="link-cover" placeholder="https://" name="<?=mangaManagementController::INPUT_COVER;?>" value="<?=(!empty($cover) ? _echo($cover) : null);?>" />
						<span>Hoặc</span>
						<button type="button" class="btn btn--gray btn--small" id="btn-upload-cover"><i class="fas fa-file-upload"></i> Upload</button>
					</div>
				</div>

				<div class="form-group">
					<label class="control-label--mini">Sơ lược:<span class="text-danger">*</span></label>
					<div class="form-control">
						<textarea rows="15" class="form-textarea required" name="<?=mangaManagementController::INPUT_DESC;?>" placeholder="Giới thiệu sơ qua về nội dung truyện"><?=(!empty($desc) ? _echo($desc) : null);?></textarea>
					</div>
				</div>

				<div class="form-group">
					<label class="control-label--mini">Liên kết:</label>
					<div class="form-control">
						<div class="input-group">
							<input class="form-input link" name="<?=mangaManagementController::INPUT_LINK;?>[]" placeholder="https://example.com|Link 1" type="text" value="<?=(!empty($links[0]) ? _echo($links[0]) : null);?>">
							<div class="input-group-append">
								<button type="button" role="btn-add-input" class="btn btn-outline-warning btn-dim"><i class="fa fa-plus"></i></button>
							</div>
						</div>
					<?php if(!empty($links[1])): ?>
					<?php
						array_shift($links); foreach($links as $key): ?>
						<div class="input-group">
							<input class="form-input link" name="<?=mangaManagementController::INPUT_LINK;?>[]" placeholder="https://example.com|Link 1" type="text" value="<?=_echo($key);?>">
							<div class="input-group-append">
								<button type="button" role="btn-remove-input" class="btn btn-outline-danger btn-dim"><i class="fa fa-trash"></i></button>
							</div>
						</div>
					<?php endforeach; ?>
					<?php endif; ?>
					</div>
				</div>

				<div class="d-flex align-items-center justify-content-end row-gap-2 mb-4">
				<?php if (empty($manga)): ?>
					<button type="reset" class="btn btn--gray btn--round pull-right mt-4">Huỷ</button>
				<?php else: ?>
					<a class="btn btn--gray btn--round pull-right mt-4" href="<?=RouteMap::get('manga_management', ['action' => mangaManagementController::ACTION_DETAIL, 'id' => $manga['id']]);?>">Huỷ</a>
				<?php endif; ?>
					<button type="submit" class="btn btn--round btn--info pull-right mt-4">Lưu lại</button>
				</div>
			</div>
			<div class="col-md-12 col-lg-3">
				<div class="_title border-left mt-0">Hình ảnh</div>
				<div class="d-flex align-items-center justify-content-center mx-2">
					<img class="manga-preview-image" id="preview-image" src="<?=($image ? _echo($image) : null);?>"/>
				</div>
				<div class="_title border-left mt-0">Ảnh bìa</div>
				<div class="d-flex align-items-center justify-content-center mx-2">
					<img class="manga-preview-image" id="preview-cover" src="<?=($cover ? _echo($cover) : null);?>"/>
				</div>
			</div>
		</div>
	</form>
</div>
<input id="input-upload-image" accept="image/*" type="file" style="display: none;">
<input id="input-upload-cover" accept="image/*" type="file" style="display: none;">

<script type="text/javascript">
    $(document).ready(function() {

		Validator({
            form: '#form-validate',
            selector: '.form-control',
            class_error: 'error',
            class_message: null,
            rules: {
				'.required': [
					Validator.isRequired('Vui lòng nhập trường này'),
				],
				'.link-image': [
					Validator.isRequired('Vui lòng nhập trường này'),
					function(value) {
						return !value || /^https?:\/\/[^\s]+$/u.test(value) ? undefined : 'Link ảnh không hợp lệ';
					}
				],
                '.link': function(value) {
                    return !value || /^https?:\/\/[^\s|]+(\|[^\s].*)?$/u.test(value) ? undefined : 'Liên kết không hợp lệ';
                }
            }
        });

		$('.state-btn').on('click', function() {
			var selectedGenre = $(this).children('select');
			if ($(this).hasClass('include')) {
				$(this).removeClass('include');
				selectedGenre.val(0).change();
			} else {
				$(this).addClass('include');
				selectedGenre.val(1).change();
			}
		});

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

		const link_image = $('#link-image')
		const link_cover = $('#link-cover')
		link_image.on('change', function() {
			if (!$(this).val().trim()) {
				return $('#preview-image').removeAttr('src')
			}
			$('#preview-image').attr('src', $(this).val().trim())
		});

		link_cover.on('change', function() {
			if (!$(this).val().trim()) {
				return $('#preview-cover').removeAttr('src')
			}
			$('#preview-cover').attr('src', $(this).val().trim())
		});

		const btn_image = $('#btn-upload-image');
		const btn_cover = $('#btn-upload-cover');

		btn_image.on('click', function() {
			$('#input-upload-image').click();
		});

		btn_cover.on('click', function() {
			$('#input-upload-cover').click();
		});

		const processUpload = function(_this, type) {
			const file = _this.files[0];
			if (!file) return;

			const validTypes = <?=json_encode(ajaxController::TYPE_ALLOW_UPLOAD_IMAGE);?>;
			if ($.inArray(file.type, validTypes) === -1) {
				$(_this).val(null);
				return $.toastShow('Không hỗ trợ định dạng ảnh: ' + file.type, { type: 'error',timeout: 3000 });
			}

			if (file.size > <?=ajaxController::MAX_SIZE_UPLOAD_IMAGE;?>) {
				$(_this).val(null);
				return $.toastShow('Dung lượng ảnh tối đa cho phép là: ' + (file.size / 1048576).toFixed(6) + 'MB', { type: 'error',timeout: 3000 });
			}

			const reader = new FileReader();
			reader.onload = function (e) {
				let tmp_html;
				let btn;
				let input;
				if (type === 'image') {
					btn = btn_image
					input = link_image
				}
				else if (type === 'cover') {
					btn = btn_cover
					input = link_cover
				}
				tmp_html = btn.html()
				btn.addClass('disabled').html('<span class="loader"></span> Uploading...')

				$.ajax({
					url: '<?=appendUrlApi(RouteMap::get('ajax', ['name' => ajaxController::UPLOAD_IMAGE]));?>',
					type: 'POST',
					headers: {
						'<?=InterFaceRequest::X_NAME;?>': file.name.replace(/\.[^/.]+$/, ''),
					},
					data: e.target.result,
					processData: false,
					dataType: 'json',
					contentType: 'application/octet-stream',
					success: function(res) {
						if (res?.code === 200 && res?.data) {
							input.val(res.data).change()
						} else {
							$.toastShow(res?.message || 'Không thể tải lên hình ảnh', { type: 'error',timeout: 3000 });
						}
					},
					error: function(err) {
						$.toastShow('Có lỗi xảy ra. Vui lòng thử lại sau ít phút', { type: 'error',timeout: 3000 });
					},
					complete: function() {
						btn.removeClass('disabled').html(tmp_html)
						$(_this).val(null);
					}
				});
			};
			reader.readAsArrayBuffer(file);
		};

		$('#input-upload-image').on('change', function() {
			processUpload(this, 'image');
		});

		$('#input-upload-cover').on('change', function() {
			processUpload(this, 'cover');
		});

    });  
</script>