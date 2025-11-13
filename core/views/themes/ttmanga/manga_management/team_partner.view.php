<?=themeController::load_css('css/manga.css'); ?>
<div class="section-sub-header">
	<div class="container">
        <span>Nhóm cộng sự</span>
    </div>
</div>

<div class="container">

	<div class="my-2">
		<a href="<?=RouteMap::get('manga_management', ['action' => mangaManagementController::ACTION_DETAIL, 'id' => $manga['id']]);?>"><i class="fas fa-chevron-left"></i> <?=_echo($manga['name']);?></a>
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

		<div class="col-lg-9">
			<div class="d-flex justify-content-between align-items-center gap-2 flex-wrap my-2">
				<span>Có tất cả <b><?=number_format($count, 0, ',', '.');?></b> nhóm cộng sự.</span>
				<div class="d-flex gap-2 flex-wrap">
					<button role="add-team" class="btn btn--small btn--round"><i class="fas fa-plus"></i> Thêm mới</button>
				</div>
			</div>
			<div class="chapter-list mt-0 p-0 mb-2 table-scroll">
			<?php if($lst_items): ?>
				<table>
					<tbody>
						<tr>
							<th></th>
							<th></th>
							<th>Tên nhóm</th>
							<th width="100%">Trưởng nhóm</th>
							<th class="align-center">Thành viên</th>
							<th>Số truyện</th>
							<th>Chương</th>
						</tr>
					<?php foreach($lst_items as $team):
						$user_own = [
							'id' => $team['own_id'],
							'username' => $team['own_username'],
							'avatar' => $team['own_avatar'],
							'user_ban' => $team['own_user_ban'],
							'role_color' => $team['own_role_color']
						];
					?>
						<tr data-id="<?=$team['id'];?>">
							<td>
								<?php if (mangaManagementController::is_own_manga($manga)): ?>
									<button role="stop-share" class="btn btn--small btn--round btn--warning">Dừng chia sẻ</button>
								<?php endif; ?>
							</td>
							<td>
								<?=render_avatar($team, Team::get_avatar($team));?>
							</td>
							<td class="nowrap">
								<a href="<?=RouteMap::get('team', ['name' => $team['name']]);?>">
									<strong class="btn btn--small btn-outline-info btn--round"><?=_echo($team['name']);?></strong>
								</a>
							</td>
							<td>
								<a target="_blank" class="user-infomation" href="<?=RouteMap::get('profile', ['id' => $user_own['id']]);?>">
									<?=render_avatar($user_own, null, true, true);?>
								</a>
							</td>
							<td class="nowrap align-center"><i class="fad fa-users"></i> <?=$team['total_members'];?></td>
							<td class="nowrap align-center"><?=$team['total_mangas'];?></td>
							<td class="nowrap align-center"><?=$team['total_chapters'];?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>

				<div class="pagination">
					<?=html_pagination($pagination);?>
				</div>

			<?php else: ?>
				<div class="alert alert--warning">Chưa có nhóm cộng sự nào!!!</div>
			<?php endif; ?>
			</div>
		</div>
		<div class="col-lg-3">
			<div class="info-box mt-4">Thành viên trong nhóm cộng sự có thể tham gia upload chương truyện.</div>
		</div>
	</div>
</div>


<script type="text/javascript">
	$(document).ready(() => {

	<?php if (mangaManagementController::is_own_manga($manga)): ?>
        $(document).on('click', '[role=add-team]', function() {

            const form = $(`
            <form method="post" class="margin-b-4">
                <?=$insertHiddenToken;?>
                <input type="hidden" name="<?=mangaManagementController::INPUT_ACTION;?>" value="<?=mangaManagementController::ACTION_ADD;?>">
                <div class="dialog-label">Chọn một nhóm:</div>
                <div class="form-group">
                    <div class="form-control">
                        <div class="search-user">
                            <input type="hidden" name="<?=mangaManagementController::INPUT_ID;?>" role="result-data">
                            <span class="form-control-feedback"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-input" placeholder="Tìm kiếm nhóm dịch" role="search-team">
                            <div class="search-user__selected"></div>
                            <ul class="search-user__results"></ul>
                        </div>
                    </div>
                </div>
            </form>`);


			$.dialogShow({
				title: 'Thêm nhóm cộng sự',
				content: form,
				button: {
					confirm: 'Tiếp tục',
					cancel: 'Huỷ'
				},
				bgHide: false,
                isCenter: true,
                onInit: () => {

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
                                url: "<?=appendUrlApi(RouteMap::get('ajax', ['name' => ajaxController::SEARCH_TEAM_PARTNER]));?>",
                                data: {
                                    <?=InterFaceRequest::KEYWORD;?>: $(this).val(),
                                    <?=InterFaceRequest::ID;?>: <?=$manga['id'];?>
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
                                                    <div class="user-display-name">${o.name}</siv>
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

        $(document).on('click', '[role=stop-share]', function(e) {
            e.preventDefault();

            const id = $(this).parents('tr').attr('data-id');

            const form = $(`
            <form method="post" class="margin-b-4">
                <?=$insertHiddenToken;?>
                <input type="hidden" name="<?=mangaManagementController::INPUT_ACTION;?>" value="<?=mangaManagementController::ACTION_REMOVE;?>">
                <input type="hidden" name="<?=mangaManagementController::INPUT_ID;?>" value="${id}">
                <div class="dialog-message">Nhóm cộng sự sẽ không thể upload và chỉnh sửa các chương trong truyện.</div>
            </form>`);

			$.dialogShow({
				title: 'Dừng chia sẻ truyện',
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
		<?php endif; ?>

	})
</script>