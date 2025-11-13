<div class="section-sub-header">
	<div class="container">
        <span><?=(empty($chapter) ? 'Thêm chương mới' : 'Sửa chương truyện');?></span>
    </div>
</div>

<div class="container">
	<div class="row">
		<div class="col-xs-12">
			<a href="<?=RouteMap::get('manga_management', ['action' => mangaManagementController::ACTION_DETAIL, 'id' => $manga['id']]);?>"><i class="fas fa-chevron-left"></i> <?=_echo($manga['name']);?></a>
		</div>

		<div class="col-xs-12 col-md-12">
			<?php
				if($error)
				{
					echo '<div class="alert alert--error mb-0">'.$error.'</div>';
				}
				else if($success)
				{
					echo '<div class="alert alert--success mb-0">'.$success.'</div>';
				}
			?>

			<form id="form-validate" method="POST" class="pt-3">
				<?=Security::insertHiddenToken();?>
				<div class="form-group">
					<label class="control-label--mini">Vị trí hiển thị:</label>
					<div class="form-control">
					<?php if (!in_array($index, [Chapter::POSITION_TOP, Chapter::POSITION_BOTTOM])): ?>
						<div class="form-radio">
							<input type="radio" id="current" name="<?=mangaManagementController::INPUT_INDEX;?>" value="<?=$index;?>" checked>
							<label for="current">Vị trí hiện tại</label>
						</div>
					<?php endif; ?>
						<div class="form-radio">
							<input type="radio" id="top" name="<?=mangaManagementController::INPUT_INDEX;?>" value="<?=Chapter::POSITION_TOP;?>" <?=($index == Chapter::POSITION_TOP ? 'checked' : null);?>>
							<label for="top">Trên cùng</label>
						</div>
						<div class="form-radio">
							<input type="radio" id="bottom" name="<?=mangaManagementController::INPUT_INDEX;?>" value="<?=Chapter::POSITION_BOTTOM;?>" <?=($index == Chapter::POSITION_BOTTOM ? 'checked' : null);?>>
							<label for="bottom">Dưới cùng</label>
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label--mini">Tên chương:<span class="text-danger">*</span></label>
					<div class="form-control">
						<input type="text" class="form-input required" placeholder="Chapter xxx" name="<?=mangaManagementController::INPUT_NAME;?>" value="<?=(!empty($name) ? _echo($name) : null);?>"/>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label--mini">Link tải:</label>
					<div class="form-control">
						<div class="input-group">
							<input class="form-input link" name="<?=mangaManagementController::INPUT_LINK;?>[]" placeholder="https://" type="text" value="<?=(!empty($links[0]) ? _echo($links[0]) : null);?>">
						</div>
					</div>
				</div>

				<ul class="upload-image-box">
					<li class="image-item btn-select-image" id="choose-image">
						<i class="fas fa-plus"></i>
					</li>
				</ul>
				<div class="d-flex justify-content-between align-items-center gap-2 flex-wrap my-3">
					<span>Có tất cả <b id="total-image"><?=count($items);?></b> hình ảnh.</span>
					<div class="drop-menu">
						<span class="btn btn--gray">
							Tuỳ chọn <i class="fas fa-ellipsis-h"></i>
						</span>
						<ul class="drop-menu__content">
							<li id="import-image">
								<i class="far fa-link"></i> Nhập link ảnh
							</li>
						<?php if(UserPermission::has('tool_leech')): ?>
							<li id="tool-leech">
								<i class="fas fa-tools"></i> Tool Reupload
							</li>
						<?php endif; ?>
							<li id="change-status-confirm-delete">
								<i class="fas fa-toggle-off"></i> Tắt hỏi khi xoá
							</li>
							<li id="remove-all-image">
								<i class="far fa-times"></i> Xoá nhanh tất cả ảnh
							</li>
						</ul>
					</div>
				</div>

				<div class="d-flex justify-content-end my-4">
					<button type="button" id="reset-sort" type="button" class="btn btn--round btn--gray">Huỷ</button>
					<button type="submit" class="btn btn--round btn--info" id="submit-save">Lưu lại</button>
				</div>
			</form>
		</div>
	</div>
</div>

<input id="input-upload-image" accept="image/*" type="file" style="display: none;">
<input id="input-upload-multiple-image" accept="image/*" type="file" multiple style="display: none;">

<script type="text/javascript" src="<?=assets('js/jquery-sortable.js');?>"></script>

<script type="text/javascript">
	const URL_GET_CHAPTER = '<?=appendUrlApi(RouteMap::get('tool_leech', ['block' => toolLeechController::BLOCK_GET_CHAPTER]));?>';
	const URL_REUPLOAD_IMAGE = '<?=appendUrlApi(RouteMap::get('tool_leech', ['block' => toolLeechController::BLOCK_REUPLOAD_IMAGE]));?>';

	const validTypes = <?=json_encode(ajaxController::TYPE_ALLOW_UPLOAD_IMAGE);?>;
	const validSize = <?=ajaxController::MAX_SIZE_UPLOAD_IMAGE;?>;

	const ORIGIN_ITEMS = <?=json_encode($items);?>;
	let ITEMS = [];
	let SHOW_CONFIRM_DELETE = true

	$(document).ready(() => {

		Validator({
            form: '#form-validate',
            selector: '.form-control',
            class_error: 'error',
            class_message: null,
            rules: {
				'.required': [
					Validator.isRequired('Vui lòng nhập trường này'),
				],
                '.link': function(value) {
                    return !value || /^https?:\/\/[^\s|]+(\|[^\s].*)?$/u.test(value) ? undefined : 'Liên kết không hợp lệ';
                }
            }
        });

		$('#change-status-confirm-delete').on('click', function() {
			SHOW_CONFIRM_DELETE = !SHOW_CONFIRM_DELETE
			if (SHOW_CONFIRM_DELETE) {
				$(this).html('<i class="fas fa-toggle-off"></i> Tắt hỏi khi xoá');
			} else {
				$(this).html('<i class="fal fa-toggle-on"></i> Bật hỏi khi xoá');
			}
		})
	})
	

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

        const sortContainer = $('.upload-image-box');
		const btnSelectFile = $('#choose-image')
		const inputUploadImage = $('#input-upload-image');
		const inputMultipleUploadImage = $('#input-upload-multiple-image');

		const getName = (item) => {
			return item?.data?.name || item?.link?.split('/')?.pop() || 'Không rõ'
		}
		const render_item = (item, index) => {
			const image = item.image;
			const link = item.link;
			const name = getName(item)
			const element = $(`
			<li class="image-item ${item?.status || ''}" data-index="${index}">
				<input type="hidden" name="<?=mangaManagementController::INPUT_IMAGE;?>[]" value="${link}" />
				<img src="${image}" />
				<div class="action">
					<div class="index"></div>
					<div class="button" role="change-image"><i class="fas fa-pencil"></i></div>
					<div class="button" role="remove-image"><i class="fas fa-times"></i></div>
					<div class="handle-drag">
						<i class="fas fa-grip-vertical"></i>
					</div>
				</div>
				<div class="detail">
					<div class="loader"></div>
					<div class="retry" role="retry-upload">
						<i class="fas fa-redo-alt"></i>
					</div>
					<div class="name" title="${name}">${name}</div>
				</div>
			</li>`)
			element.insertBefore(btnSelectFile)
			$('#total-image').html(sortContainer.find('li').not(btnSelectFile).length)
			item.target = element
			if (ITEMS.some(o => o?.status == 'uploading')) {
				$('#submit-save').addClass('disabled');
			} else {
				$('#submit-save').removeClass('disabled');
			}
		}

		const loading = $('<div class="loading"><div class="loader"></div></div>');

		const init_items = () => {
			ITEMS = [...ORIGIN_ITEMS];
			sortContainer.find('li').not(btnSelectFile).remove()
			ITEMS.forEach((item, index) => {
				render_item(item, index + 1)
			})
		}
		init_items()

		const indexItem = () => {
			const $items = sortContainer.find('li').not(btnSelectFile);
            $items.each(function(i) {
                    $(this).attr('data-index', i + 1);
            });
		}

        sortContainer.sortable({
            scroll: true,
            scrollSensitivity: 60,
            scrollSpeed: 10,
            forceFallback: true,
            swap: false,
            invertSwap: true,
            multiDrag: true,
            swapThreshold: 0.65,
            animation: 150,
			filter: '.btn-select-image',
            swapClass: 'swap-highlight',
            ghostClass: 'dragging-item',
            selectedClass: 'selected-drag',
            fallbackTolerance: 3,
            handle: '.handle-drag',
            onEnd: (evt) => {
				indexItem();
				sortContainer.append(btnSelectFile);
            }
        });

        $('#reset-sort').on('click', function() {
            init_items()
        });

        $('#import-image').on('click', function(e) {
            e.preventDefault();
            const form = $(`
			<div class="dialog-label">Nhập 1 hoặc nhiều link ảnh:</div>
			<div class="form-group">
				<div class="form-control">
					<textarea class="form-textarea" rows="10" wrap="off" placeholder="Mỗi ảnh cách nhau bởi dấu xuống dòng" id="links"></textarea>
				</div>
			</div>`);

			$.dialogShow({
				title: 'Nhập link ảnh',
				content: form,
				button: {
					confirm: 'Tiếp tục',
					cancel: 'Huỷ'
				},
				bgHide: false,
				minWidth: 1000,
                isCenter: true,
				onBeforeConfirm: function(){
					const links = $.trim($('textarea').val())
					.split('\n')
					.map(l => $.trim(l))
					.filter(l => l !== '' && /^https?:\/\/[^\s]+$/i.test(l));
					
					links.forEach(link => {
						const item = {type: 'image', image: link, link}
						render_item(item, ITEMS.length + 1)
						ITEMS.push(item)
					});
                    return true;
				}
			});
        });

		$(document).on('click', '[role=remove-image]', function(e) {
            e.preventDefault();

            const item = ITEMS.find(o => o.target[0] == $(this).parents('li')[0]);
            if(!item) {
                return $.toastShow('Không tìm hình ảnh', {
					type: 'error',
					timeout: 3000
				});	;
            }

			const removeImage = () => {
				item?.xhr && item.xhr.abort === 'function' && item.xhr.abort()
				item.target.remove()
				ITEMS.splice(ITEMS.indexOf(item), 1);
				$('#total-image').html(sortContainer.find('li').not(btnSelectFile).length)
				if (ITEMS.some(o => o?.status == 'uploading')) {
					$('#submit-save').addClass('disabled');
				} else {
					$('#submit-save').removeClass('disabled');
				}
				indexItem();
			}

			if (!SHOW_CONFIRM_DELETE) {
				return removeImage()
			}

			const content = $(`<div class="dialog-message d-flex justify-content-center"><img class="img-responsive" src="${item.image}" style="max-height: 350px" /></div>`)
			$.dialogShow({
				title: 'Xoá hình ảnh',
				content: content,
				button: {
					confirm: 'Tiếp tục',
					cancel: 'Huỷ'
				},
				bgHide: false,
                isCenter: false,
                onInit: () => {
					$.dialogResize()
				},
				onBeforeConfirm: function(){
					removeImage()
                    return true;
				}
			});
        });

		$('#remove-all-image').on('click', function(e) {
            e.preventDefault();
			sortContainer.html(btnSelectFile)
			ITEMS.forEach(item => {
				item?.xhr && item.xhr.abort === 'function' && item.xhr.abort();
			})
			ITEMS = []
        });

		$(document).on('click', '[role=change-image]', function(e) {
            e.preventDefault();

            const item = ITEMS.find(o => o.target[0] == $(this).parents('li')[0]);
            if(!item) {
                return $.toastShow('Không tìm hình ảnh', {
					type: 'error',
					timeout: 3000
				});	;
            }

			const form = $(`
			<form class="row m-0">
				<div class="col-md-4">
					<div class="preview-edit-link">
						<img src="${item.image}" />
					</div>
				</div>
				<div class="col-md-8">
					<div class="dialog-label">Link ảnh:</div>
					<div class="form-group">
						<div class="form-control">
							<input type="text" class="form-input" placeholder="https://" value="${item.image}" id="input-edit-link">
						</div>
					</div>
					<div class="dialog-label mt-2">Hoặc Upload:</div>
					<div class="form-group">
						<div class="form-control">
							<div id="container-upload-edit" class="image-upload-box">
								<i class="fad fa-images"></i>
								<span class="text-center">Chọn hoặc kéo thả ảnh vào đây</span>
							</div>
						</div>
					</div>
				</div>
			</form>`);
			loading.hide();
			form.append(loading);

			$(document).on('change', '#input-edit-link', function() {
				form.find('img').attr('src', $(this).val())
			});

			inputUploadImage.attr('data-index', item.target.attr('data-index'))

			$(document).off('click.containerUploadEdit').on('click.containerUploadEdit', '#container-upload-edit', () => inputUploadImage.click());
			$(document).off('dragover.containerUploadEdit').on('dragover.containerUploadEdit', '#container-upload-edit', e => { e.preventDefault(); });
			$(document).off('dragleave.containerUploadEdit').on('dragleave.containerUploadEdit', '#container-upload-edit', e => { e.preventDefault(); });
			$(document).off('drop.containerUploadEdit').on('drop.containerUploadEdit', '#container-upload-edit', e => {
				e.preventDefault();
				const files = e.originalEvent.dataTransfer.files;
				inputUploadImage[0].files = files;
				inputUploadImage.trigger('change');
			});

			$.dialogShow({
				title: 'Chỉnh sửa hình ảnh',
				content: form,
				button: {
					confirm: 'Tiếp tục',
					cancel: 'Huỷ'
				},
				minWidth: 800,
				bgHide: false,
                isCenter: false,
                onInit: () => {
					$.dialogResize()
                    Validator({
                        form: form,
                        selector: '.form-control',
                        class_error: 'error',
                        rules: {
                            'input': [
								Validator.isRequired('Vui lòng nhập trường này'),
								function(value) {
									return !value || /^https?:\/\/[^\s]+$/u.test(value) ? undefined : 'Link ảnh không hợp lệ';
								}
                            ]
                        }
                    });
                },
				onBeforeConfirm: function(){
					item.type = 'image'
					item.data = null

					item.link = $('#input-edit-link').val()
					item.image = item.link
					item.name = getName(item)
					item.status = 'complete'

					item?.xhr && item.xhr.abort === 'function' && item.xhr.abort();

					item.target.find('img').attr('src', item.link)
					item.target.find('input').val(item.link)
					item.target.find('.name').attr('title', item.name).html(item.name)
                    return true;
				}
			});
        });

		const processUpload = function(_this, callback) {
			const file = _this.files[0];
			if (!file) return;

			if ($.inArray(file.type, validTypes) === -1) {
				$(_this).val(null);
				return $.toastShow('Không hỗ trợ định dạng ảnh: ' + file.type, { type: 'error' });
			}

			if (file.size > validSize) {
				$(_this).val(null);
				return $.toastShow('Dung lượng ảnh tối đa cho phép là: ' + (file.size / 1048576).toFixed(6) + 'MB', { type: 'error' });
			}

			const reader = new FileReader();
			reader.onload = function (e) {
				$.ajax({
					url: '<?=appendUrlApi(RouteMap::get('ajax', ['name' => ajaxController::UPLOAD_IMAGE]));?>',
					type: 'POST',
					headers: {
						'<?=InterFaceRequest::X_NAME;?>': $(_this).attr('data-index'),
					},
					data: e.target.result,
					processData: false,
					dataType: 'json',
					contentType: 'application/octet-stream',
					success: function(res) {
						if (res?.code === 200 && res?.data) {
							typeof callback == 'function' && callback(res.data)
						} else {
							$.toastShow(res?.message || 'Không thể tải lên hình ảnh', { type: 'error' });
						}
					},
					error: function(err) {
						$.toastShow('Có lỗi xảy ra. Vui lòng thử lại sau ít phút', { type: 'error' });
					},
					complete: function() {
						$(_this).val(null);
						loading.hide()
					}
				});
			};
			reader.readAsArrayBuffer(file);
		};

		inputUploadImage.on('change', function() {
			const file = this.files[0];
			if ($.inArray(file.type, validTypes) === -1) {
				$(this).val(null);
				return $.toastShow('Không hỗ trợ định dạng ảnh: ' + file.type, { type: 'error' });
			}

			if (file.size > validSize) {
				$(this).val(null);
				return $.toastShow('Dung lượng ảnh tối đa cho phép là: ' + (file.size / 1048576).toFixed(6) + 'MB', { type: 'error' });
			}

			loading.show();
			processUpload(this, (link) => $('#input-edit-link').val(link).change())
		})

		btnSelectFile.on('click', () => inputMultipleUploadImage.click());
		sortContainer.on('dragover', e => { e.preventDefault(); });
		sortContainer.on('dragleave', e => { e.preventDefault(); });
		sortContainer.on('drop', e => {
			e.preventDefault();
			const files = e.originalEvent.dataTransfer.files;
			inputMultipleUploadImage[0].files = files;
			inputMultipleUploadImage.trigger('change');
		});

		$(document).on('paste', function (e) {
			const items = e.originalEvent.clipboardData.items;
			for (const item of items) {
				if (item.type.indexOf('image') !== -1) {
					const file = item.getAsFile();
					const dataTransfer = new DataTransfer();
					dataTransfer.items.add(file);
					inputMultipleUploadImage[0].files = dataTransfer.files;
					inputMultipleUploadImage.trigger('change');
					break;
				}
			}
		});

		const render_status = (item) => {
			item.target.removeClass('pending complete uploading error').addClass(item.status)
		}

		const start_upload = () => {
			const lst_files = ITEMS.filter(o => o.type == 'file' && o.status == 'pending');
			if (lst_files.length < 1) {
				return $('#submit-save').removeClass('disabled');
			}

			if (ITEMS.some(o => o?.status == 'uploading')) {
				return $('#submit-save').addClass('disabled');
			}

			const file = lst_files.shift();

			if (file.status == 'pending') {
				const data = file?.data;
				if (!data || $.inArray(data.type, validTypes) === -1 || file.size > validSize) {
					return start_upload();
				}

				$('#submit-save').addClass('disabled');

				const reader = new FileReader();
				reader.onload = function (e) {
					file.status = 'uploading';
					file.xhr = $.ajax({
						url: '<?=appendUrlApi(RouteMap::get('ajax', ['name' => ajaxController::UPLOAD_IMAGE]));?>',
						type: 'POST',
						headers: {
							'<?=InterFaceRequest::X_NAME;?>': file.target.attr('data-index'),
						},
						data: e.target.result,
						processData: false,
						dataType: 'json',
						contentType: 'application/octet-stream',
						success: function(res) {
							if (res?.code === 200 && res?.data) {
								file.status = 'complete';
								file.type = 'image'
								file.link = res.data
								file.image = file.link
								file.data = null
								file.name = getName(file)

								file.target.find('input').val(file.link)
								file.target.find('.name').html(file.name).attr('title', file.name)
							} else {
								file.status = 'error';
							}
						},
						error: function(err) {
							file.status = 'error';
						},
						complete: function() {
							render_status(file)
							start_upload()
						}
					});
				};
				reader.readAsArrayBuffer(data);
			}
		};

		inputMultipleUploadImage.on('change', function() {
			const validFiles = [];
			for (const file of this.files) {
				if (!validTypes.includes(file.type)) {
					$.toastShow(`"${file.name}" không phải ảnh hợp lệ (chỉ chấp nhận ${validTypes.map(o => o.split('/')[1]).join(', ')}).`, { type: 'error' });
					continue;
				}
				if (file.size > validSize) {
					$.toastShow(`"${file.name}" vượt quá dung lượng cho phép (tối đa ${(validSize / 1048576).toFixed(6)}MB).`, { type: 'error' });
					continue;
				}
				validFiles.push(file);
			}

			let index = ITEMS.length + 1
			for (const file of validFiles) {
				const item = {
					type: 'file',
					image: URL.createObjectURL(file),
					data: file,
					status: 'pending'
				}	
				render_item(item, index++)
				ITEMS.push(item)
			}
			inputMultipleUploadImage.val(null)
			start_upload()
		})


		$(document).on('click', '[role=retry-upload]', function(e) {
            e.preventDefault();

            const item = ITEMS.find(o => o.target[0] == $(this).parents('li')[0]);
            if(!item) {
                return $.toastShow('Không tìm hình ảnh', {
					type: 'error',
					timeout: 3000
				});	;
            }

			item.status = 'pending'
			render_status(item)
			start_upload()
        });


</script>

<?=themeController::load_js('js/tool-reupload.js');?>