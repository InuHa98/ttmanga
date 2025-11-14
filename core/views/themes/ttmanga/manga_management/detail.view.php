<?=themeController::load_css('css/manga.css'); ?>
<div class="section-sub-header">
	<div class="container">
        <span>Quản lý truyện</span>
    </div>
</div>

<div class="container">
	<div class="tabmenu-horizontal m-0">
		<div class="tabmenu-horizontal__item active" data-content="list">
			<div>Danh sách chương</div>
		</div>
		<div class="tabmenu-horizontal__item" data-content="info">
			<div>Thông tin truyện</div>
		</div>
	</div>

	<div class="tabmenu-horizontal__content mb-4" id="info">
		<div class="row">
			<div class="col-xs-12 col-sm-9">
				<div class="manga-infomation p-0 mt-3">
					<div class="_title"><?=_echo($manga['name']);?></div>
					<?php if($name_other): ?>
					<div class="manga-infomation__item">
						<div class="label"><i class="fas fa-pen"></i> Tên khác:</div>
						<div class="text">
						<?php foreach($name_other as $name): ?>
							<span class="other-name"><?=_echo($name);?></span>
						<?php endforeach; ?>
						</div>
					</div>
					<?php endif; ?>
					<div class="manga-infomation__item">
						<div class="label"><i class="fas fa-tags"></i> Thể loại:</div>
						<div class="text">
						<?php if($genres): foreach($genres as $val): ?>
							<a class="genres" href="<?=RouteMap::build_query([mangaController::PARAM_GENRES => $val['id']], 'manga');?>"><?=_echo($val['name']);?></a>
						<?php endforeach; else: ?>
							<span class="unknown">Không rõ</span>
						<?php endif; ?>
						</div>
					</div>
					<div class="manga-infomation__item">
						<div class="label"><i class="fa fa-user"></i> Tác giả:</div>
						<div class="text">
						<?php if($auths): foreach($auths as $val): ?>
							<a class="auth" href="<?=RouteMap::build_query([mangaController::INPUT_AUTHOR => $val], 'search_manga');?>"><?=_echo($val);?></a>
						<?php endforeach; else: ?>
							<span class="unknown">Không rõ</span>
						<?php endif; ?>
						</div>
					</div>
					<div class="manga-infomation__item">
						<div class="label"><i class="fas fa-layer-group"></i> Nhóm dịch:</div>
						<div class="text">
						<?php if($teams): foreach($teams as $val): ?>
							<a class="team" href="<?=RouteMap::get('team', ['name' => $val['name']]);?>"><?=_echo($val['name']);?></a>
						<?php endforeach; else: ?>
							<span class="unknown">Không rõ</span>
						<?php endif; ?>
						</div>
					</div>
					<div class="manga-infomation__item">
						<div class="label"><i class="fa fa-rss"></i> Tình trạng:</div>
						<div class="text"><?=$status;?></div>
					</div>

					<div class="manga-infomation__item">
						<div class="label"><i class="fas fa-upload"></i> Đăng bởi:</div>
						<div class="text">
							<a target="_blank" class="user-infomation" href="<?=RouteMap::get('profile', ['id' => $own['id']]);?>">
								<?=render_avatar($own, null, true, true);?>
							</a>
						</div>
					</div>

					<div class="manga-infomation__item column">
						<div class="label">Sơ lược:</div>
						<div class="text"><?=_echo($manga['text'], true);?></div>
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-sm-3">
				<div class="_title border-left">Hình ảnh</div>
				<img class="preview-image-manga" src="<?=_echo($manga['image']);?>" />
				<div class="_title mt-2">Liên kết</div>
				<div class="links-box">
				<?php if($links): foreach($links as $link): ?>
					<a class="link" href="<?=_echo($link['url']);?>"><?=_echo($link['text']);?></a>
				<?php endforeach; else: ?>
					<div class="empty-link">Không có liên kết nào</div>
				<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<div class="tabmenu-horizontal__content mb-4 active" id="list">
		<div class="my-2">
			<a href="<?=RouteMap::get('my_team');?>"><i class="fas fa-chevron-left"></i> Danh sách truyện</a>
		</div>
		<div class="row">
			<div class="col-12">
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
			</div>
			<div class="col-lg-3">
				<div class="box box--list">
					<div class="box__body d-flex flex-wrap">
						<a class="box__body-item <?=(!mangaManagementController::is_own_manga($manga) ? 'disabled' : null);?>" href="<?=RouteMap::get('manga_management', ['action' => mangaManagementController::ACTION_EDIT_MANGA, 'id' => $manga['id']]);?>">
							<span class="item-icon">
								<i class="fad fa-edit"></i>
							</span>
							<div>
								<span class="item-title">Sửa thông tin truyện</span>
							</div>
						</a>

						<a class="box__body-item <?=(!mangaManagementController::is_own_manga($manga) ? 'disabled' : null);?>" href="<?=RouteMap::get('manga_management', ['action' => mangaManagementController::ACTION_SORT_CHAPTER, 'id' => $manga['id']]);?>">
							<span class="item-icon">
								<i class="fas fa-sort"></i>
							</span>
							<div>
								<span class="item-title">Sắp xếp thứ tự chương</span>
							</div>
						</a>

						<a class="box__body-item <?=(!mangaManagementController::is_own_manga($manga) ? 'disabled' : null);?>" href="<?=RouteMap::get('manga_management', ['action' => mangaManagementController::ACTION_TEAM_PARTNER, 'id' => $manga['id']]);?>">
							<span class="item-icon">
								<i class="fas fa-layer-group"></i>
							</span>
							<div>
								<span class="item-title">Nhóm cộng sự</span>
							</div>
						</a>
						<a class="box__body-item <?=(!mangaManagementController::is_own_manga($manga) ? 'disabled' : null);?>" id="change-own">
							<span class="item-icon">
								<i class="far fa-exchange-alt"></i>
							</span>
							<div>
								<span class="item-title">Thay đổi quyền sở hữu</span>
							</div>
						</a>

					<?php if(UserPermission::has('tool_leech')): ?>
						<a class="box__body-item" href="<?=RouteMap::get('manga_management', ['action' => mangaManagementController::ACTION_TOOL_LEECH, 'id' => $manga['id']]);?>">
							<span class="item-icon">
								<i class="fas fa-tools"></i>
							</span>
							<div>
								<span class="item-title">Tool Leech</span>
							</div>
						</a>
					<?php endif; ?>

						<a class="box__body-item <?=(!mangaManagementController::is_own_manga($manga) ? 'disabled' : null);?> text-danger" id="delete-manga">
							<span class="item-icon">
								<i class="fas fa-trash"></i>
							</span>
							<div>
								<span class="item-title">Xoá truyện</span>
							</div>
						</a>
					</div>
				</div>
			</div>
			<div class="col-lg-9">
				<div class="d-flex justify-content-between align-items-center gap-2 flex-wrap my-2">
					<span>Có tất cả <b><?=number_format(count($chapters), 0, ',', '.');?></b> chương truyện.</span>
					<div class="d-flex gap-2 flex-wrap">
						<div class="drop-menu" id="check-status">
							<span class="btn btn--small btn--gray disabled">
								Kiểm tra ảnh hỏng <span role="multiple_selected_count">(0)</span> <i class="fas fa-ellipsis-h"></i>
							</span>
							<ul class="drop-menu__content">
								<li id="btn-check-status">
									Kiểm tra kỹ từng ảnh
								</li>
								<li id="btn-fast-check-status">
									Kiểm tra nhanh (chỉ 1 số lượng ảnh)
								</li>
							</ul>
						</div>
						<button class="btn btn--small btn--danger disabled" role="delete-selected">
							<i class="fas fa-times"></i> Xoá mục đã chọn <span role="multiple_selected_count">(0)</span>
						</button>
						<a class="btn btn--small btn--info" href="<?=RouteMap::get('manga_management', ['action' => mangaManagementController::ACTION_NEW_CHAPTER, 'id' => $manga['id']]);?>"><i class="fas fa-plus"></i> Thêm chương mới</a>
					</div>
				</div>
				<div class="chapter-list mt-0 p-0 mb-2 table-scroll">
				<?php if($chapters): ?>
					<table>
						<tbody>
							<tr>
								<th>
									<span class="form-check">
										<input type="checkbox" id="multiple_selected_all">
										<label for="multiple_selected_all"></label>
									</span>
								</th>
								<th></th>
								<th width="100%">Tên chương</th>
								<th>Trạng thái</th>
								<th>Đăng bởi</th>
								<th class="date">Ngày đăng</th>
								<th></th>
							</tr>
						<?php foreach($chapters as $val):
							    $uploader = [
									'id' => $val['user_upload'],
									'name' => $val['uploader_name'],
									'username' => $val['uploader_username'],
									'avatar' => $val['uploader_avatar'],
									'user_ban' => $val['uploader_ban_id'],
									'role_color' => $val['uploader_role_color']
								];
						?>
							<tr data-id="<?=$val['id'];?>">
								<td>
									<div class="form-check <?=(!mangaManagementController::is_own_chapter($val) ? 'disabled' : null);?>">
										<input type="checkbox" role="multiple_selected" name="<?=mangaManagementController::INPUT_ID;?>[]" value="<?=$val['id'];?>" id="label_<?=$val['id'];?>">
										<label for="label_<?=$val['id'];?>"></label>
									</div>
								</td>
								<td>
									<a target="_blank" href="<?=RouteMap::get('chapter', ['id_manga' => $manga['id'], 'id_chapter' => $val['id']]);?>"><i class="fas fa-eye"></i></a>
								</td>
								<td class="chapter-name">
									<a class="text-primary" href="<?=RouteMap::get('manga_management', ['action' => mangaManagementController::ACTION_EDIT_CHAPTER, 'id' => $val['id']]);?>"><?=_echo($val['name']);?></a>
								</td>
								<td class="nowrap align-center" role="status">--</td>
								<td>
									<a target="_blank" class="user-infomation" href="<?=RouteMap::get('profile', ['id' => $uploader['id']]);?>">
										<?=render_avatar($uploader, null, true, true);?>
									</a>
								</td>
								<td class="date"><?=date('d/m/Y', $val['created_at']);?></td>
								<td>
									<button class="btn btn--small btn--round btn--transparent text-danger <?=(!mangaManagementController::is_own_chapter($val) ? 'disabled' : null);?>" role="delete-chapter">
										<i class="fas fa-times"></i> Xoá
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				<?php else: ?>
					<div class="alert alert--warning">Chưa có chương truyện nào!!!</div>
				<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(() => {
		
		const DATA_CHAPTERS = <?=json_encode($chapters);?>;
		const role_multiple_selected = '[role=multiple_selected]';
		const btnCheckStatus = $('#check-status')
		let ids_chapter = []
		let is_checking = false
		let is_fast_check = false
		let chapter_checking = null

		$('.tabmenu-horizontal__item').on('click', function(e) {
			const contentData = $(this).data('content')

			$('.tabmenu-horizontal__item').parent().find('.active').removeClass('active')
			$('.tabmenu-horizontal__content.active').removeClass('active')

			$(this).addClass('active')
			$(`#${contentData}`).addClass('active')
		})


		multiple_selected({
			role_select_all: "#multiple_selected_all",
			role_select: role_multiple_selected,
			onSelected: function(total_selected, config){
				$("[role=multiple_selected_count]").html('('+total_selected+')');
				$("[role=multiple_selected_count]").parents('.btn').removeClass("disabled");
			},
			onNoSelected: function(total_selected, config){
				$("[role=multiple_selected_count]").html('(0)');
				$("[role=multiple_selected_count]").parents('.btn').removeClass("disabled").addClass("disabled");
			}
		});


		const check_status = function(){
			if (is_checking) {
				return
			}

			chapter_checking = ids_chapter.shift();
			if(!chapter_checking)
			{
				chapter_checking = null
				is_fast_check = false
				return btnCheckStatus.removeClass("disabled");
			}

			const parent_tr = chapter_checking.target

			let status = parent_tr.find('[role="status"]');

            status.removeClass('text-success text-danger').addClass('text-warning');
            status.html('<i class="fas fa-sync-alt"></i> Checking...');
			btnCheckStatus.addClass("disabled");

			is_checking = true
			$.ajax({
                type: "POST",
                url: "<?=appendUrlApi(RouteMap::get('ajax', ['name' => ajaxController::CHECK_IMAGE_CHAPTER]));?>",
                data: {
                    <?=InterFaceRequest::ID;?>: chapter_checking.id,
					fast: is_fast_check
                },
                dataType: 'json',
				cache: false,
				success: function(response)
				{
					if(response.code == 200)
					{
                        status.addClass('text-success');
						return status.html('<i class="fas fa-check-circle"></i> ' + response.message);
					}
                    status.addClass('text-danger');
					status.html('<i class="fas fa-exclamation-circle"></i> ' + response.message + '(' + response.data + ')');
				},
				error: function(response)
				{
                    status.addClass('text-danger');
					status.html('<i class="fas fa-exclamation-circle"></i> Không thể check');
				},
				complete: function(response){
                    status.removeClass('text-warning');
					is_checking = false
					check_status(parent_tr)
				}
			});
		};

		const start_check = function() {
			$(role_multiple_selected+":checked").each(function(){
				const chapter = DATA_CHAPTERS.find(o => o.id == $(this).val());
				const target = $(`tr[data-id="${chapter?.id}"]`);
				if (target && !ids_chapter.some(o => o.id == chapter.id)) {
					if (!chapter_checking || chapter_checking.id != chapter.id) {
						ids_chapter.push({id: chapter.id, target})
						target.find('[role="status"]').addClass('text-warning').html('<i class="fas fa-sync-alt"></i> Pending');
					}
				}
			});
			if (!chapter_checking) {
				check_status();
			}
		}

		$('#btn-check-status').on('click', function() {
			start_check()
		})

		$('#btn-fast-check-status').on('click', function() {
			is_fast_check = true
			start_check()
		})

        $('[role=delete-chapter]').on('click', function(e) {
            e.preventDefault();

            const chapter = DATA_CHAPTERS.find(o => o.id == $(this).parents('tr').attr('data-id'));
            if(!chapter) {
                return $.toastShow('Không tìm thấy chương truyện', {
					type: 'error',
					timeout: 3000
				});	;
            }

            const form = $(`
            <form method="post" class="margin-b-4">
                <?=$insertHiddenToken;?>
                <input type="hidden" name="<?=mangaManagementController::INPUT_ACTION;?>" value="<?=mangaManagementController::ACTION_DELETE;?>">
                <input type="hidden" name="<?=mangaManagementController::INPUT_ID;?>" value="${chapter.id}">
                <div class="dialog-message text-danger">Mọi bình luận và ảnh sẽ bị xoá vĩnh viễn. Bạn vẫn muốn tiếp tục?</div>
				${ chapter.user_upload != <?=Auth::$data['id'];?> ?
					`<div class="dialog-label">Nhập lý do:</div>
					<div class="form-group">
						<div class="form-control">
							<textarea class="form-textarea" name="<?=mangaManagementController::INPUT_REASON;?>" placeholder="Có thể bỏ trống"></textarea>
						</div>
					</div>` : '' }
            </form>`);


			$.dialogShow({
				title: 'Xoá: ' + chapter.name,
				content: form,
				button: {
					confirm: 'Tiếp tục',
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

		$('[role=delete-selected]').on('click', function(e) {
            e.preventDefault();

            const form = $(`
            <form method="post" class="margin-b-4">
                <?=$insertHiddenToken;?>
                <input type="hidden" name="<?=mangaManagementController::INPUT_ACTION;?>" value="<?=mangaManagementController::ACTION_DELETE_MULTIPLE;?>">
                <div class="dialog-message text-danger">Mọi bình luận và ảnh sẽ bị xoá vĩnh viễn. Bạn vẫn muốn tiếp tục?</div>
            </form>`);


			let is_show_reason = false;
			$(role_multiple_selected+":checked").each(function(){
				if (is_show_reason == false) {
					const chapter = DATA_CHAPTERS.find(o => o.id == $(this).val());
					if (chapter.user_upload != <?=Auth::$data['id'];?>) {
						is_show_reason = true;
						form.append(`
						<div class="dialog-label">Nhập lý do:</div>
						<div class="form-group">
							<div class="form-control">
								<textarea class="form-textarea" name="<?=mangaManagementController::INPUT_REASON;?>" placeholder="Có thể bỏ trống"></textarea>
							</div>
						</div>`)
					}
				}
				form.append('<input type="hidden" name="<?=mangaManagementController::INPUT_ID;?>[]" value="'+$(this).val()+'">');
			});

			$.dialogShow({
				title: 'Xoá ' + $(role_multiple_selected+":checked").length + ' chương đã chọn',
				content: form,
				button: {
					confirm: 'Tiếp tục',
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

	<?php if (mangaManagementController::is_own_manga($manga)): ?>
        $('#delete-manga').on('click', function(e) {
            e.preventDefault();

            const form = $(`
            <form method="post" class="margin-b-4">
                <?=$insertHiddenToken;?>
                <input type="hidden" name="<?=mangaManagementController::INPUT_ACTION;?>" value="<?=mangaManagementController::ACTION_DELETE_MANGA;?>">
                <div class="dialog-message text-danger">Mọi chương truyện sẽ đều bị xoá. Bạn vẫn muốn tiếp tục?</div>
			<?php if ($manga['user_upload'] != Auth::$data['id']): ?>
				<div class="dialog-label">Nhập lý do:</div>
                <div class="form-group">
                    <div class="form-control">
                        <textarea class="form-textarea" name="<?=mangaManagementController::INPUT_REASON;?>" placeholder="Có thể bỏ trống"></textarea>
                    </div>
                </div>
			<?php endif; ?>
            </form>`);


			$.dialogShow({
				title: 'Xoá truyện',
				content: form,
				button: {
					confirm: 'Tiếp tục',
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

        $('#change-own').on('click', function() {

            const form = $(`
            <form method="post" class="margin-b-4">
                <?=$insertHiddenToken;?>
                <input type="hidden" name="<?=mangaManagementController::INPUT_ACTION;?>" value="<?=mangaManagementController::ACTION_CHANGE;?>">
                <div class="dialog-message info-box">Bạn sẽ mất quyền quản lý truyện này. Người nhận quyền phải nằm trong nhóm hiện tại hoặc cộng sự.</div>
                <div class="dialog-label">Nhóm dịch:</div>
                <div class="form-group">
                    <div class="form-control">
                        <select class="js-custom-select" id="team-partner" onchange="$('[role=search-team]').trigger('keyup.search')">
                    <?php foreach($lst_teams as $o): ?>
                        <option value="<?=$o['id'];?>"><?=_echo($o['name']);?></option>
                    <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="dialog-label mt-2">Chọn một thành viên:</div>
                <div class="form-group">
                    <div class="form-control">
                        <div class="search-user">
                            <input type="hidden" name="<?=mangaManagementController::INPUT_ID;?>" role="result-data">
                            <span class="form-control-feedback"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-input" placeholder="Tìm kiếm thành viên nhóm đã chọn" role="search-team">
                            <div class="search-user__selected"></div>
                            <ul class="search-user__results"></ul>
                        </div>
                    </div>
                </div>
            </form>`);


			$.dialogShow({
				title: 'Thay đổi quyền sở hữu',
				content: form,
				button: {
					confirm: 'Tiếp tục',
					cancel: 'Huỷ'
				},
				bgHide: false,
                isCenter: true,
                onInit: () => {
					JSCustomSelect();
                    const delay_search = (function () {
                        let timer = 0;
                        return function (callback, ms) {
                            clearTimeout(timer);
                            timer = setTimeout(callback, ms);
                        };
                    })();

                    const result_search = $('.search-user__results');
                    const selected_search = $('.search-user__selected');

                    $(document).off('keyup.search').on('keyup.search', '[role="search-team"]', function() {

                        delay_search(() => {

                            if($(this).val().trim() == '') {
                                return result_search.hide();
                            }

                            result_search.show();
                            selected_search.hide();
                            
                            const offset_search_user = result_search.parent()[0].getBoundingClientRect();
                            result_search.css({
                                top: offset_search_user.y + offset_search_user.height + 5 + 'px',
                                left: offset_search_user.x + 'px',
                                width: offset_search_user.width + 'px'
                            });


                            $.ajax({
                                type: "POST",
                                url: "<?=appendUrlApi(RouteMap::get('ajax', ['name' => ajaxController::SEARCH_TEAM_MEMBER]));?>",
                                data: {
                                    <?=InterFaceRequest::KEYWORD;?>: $(this).val(),
                                    <?=InterFaceRequest::TEAM;?>: $('#team-partner').val(),
                                    <?=InterFaceRequest::MANGA;?>: <?=$manga['id'];?>
                                },
                                dataType: 'json',
                                cache: false,
                                success: function(response)
                                {
                                    if(response.code == 200)
                                    {
                                        result_search.html('');
                                        response.data.forEach(o => {
                                            result_search.append(`
                                            <li class="search-user__results-item" data-id="${o.id}">
                                                <div class="user-infomation bg--none">
                                           			<div class="user-avatar" data-text="${o.first_name}" style="--bg-avatar: ${o.bg_avatar}">
                                                        <img src="${o.avatar}">
                                                    </div>
                                                    <div class="user-display-name">${o.display_name}</siv>
                                                    <div class="user-username">@${o.username}</siv>
                                                </div>
                                            </li>`);
                                        });
                                        return;
                                    }
                                    result_search.html('<span class="empty">' + response.message + '</span>');
                                },
                                error: function(response)
                                {
                                    result_search.html('<span class="error">Có lỗi xảy ra. Vui lòng thử lại</span>');
                                }
                            });
                        }, 500);
                    });

                    $(document).off('click.search').on('click.search', '.search-user__results-item', function(e) {
                        e.stopPropagation();
                        selected_search.html($(this).html());
                        selected_search.show();
                        result_search.hide();
                        form.find('input[role="search-team"]').val('');
                        form.find('input[role="result-data"]').val($(this).attr('data-id'));
                    });

                    Validator({
                        form: form,
                        selector: '.form-control',
                        class_error: 'error',
                        rules: {
                            'input[role="result-data"]': [
                                Validator.isRequired()
                            ]
                        }
                    });
                },
				onBeforeConfirm: function(){
                    form.submit();
                    return false;
				}
			});
		});
	<?php endif; ?>

	})
</script>