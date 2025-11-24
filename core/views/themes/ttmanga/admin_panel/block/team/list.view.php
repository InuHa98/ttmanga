
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

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <span>Đang hoạt động (<b><?=number_format($count, 0, ',', '.');?></b>)</span>
        <form method="GET" class="action">
            <div class="form-control">
                <span class="form-control-feedback"><i class="fas fa-search"></i></span>
                <input type="text" class="form-input" name="<?=InterFaceRequest::KEYWORD;?>" placeholder="Tìm kiếm" value="<?=_echo($keyword);?>"/>
            </div>
        </form>
    </div>

    <?php if($team_list): ?>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th></th>
                    <th>Tên nhóm</th>
                    <th>Trưởng nhóm</th>
                    <th>Cấu hình upload</th>
                    <th width="30%" class="align-center">Thành viên</th>
                    <th width="30%" class="align-center">Manga</th>
                    <th width="30%" class="align-center">Chapter</th>
                </tr>
            </thead>
            <tbody>
    <?php foreach($team_list as $team):
        $user_own = [
            'id' => $team['own_id'],
            'username' => $team['own_username'],
            'avatar' => $team['own_avatar'],
            'user_ban' => $team['own_user_ban'],
            'role_color' => $team['own_role_color']
        ];
    ?>
        <tr>
            <td>
                <div class="drop-menu">
                    <div class="drop-menu__button">
                        <i class="fa fa-ellipsis-v"></i>
                    </div>
                    <ul class="drop-menu__content" data-id="<?=$team['id'];?>">
                    <?php if(UserPermission::has('admin_team_edit')): ?>
                        <li role="change-name">
                            <i class="fa fa-user"></i> Change Name
                        </li>
                        <li role="change-own">
                            <i class="fa fa-lock"></i> Change Own
                        </li>
                        <li role="change-config">
                            <i class="fa fa-lock"></i> Change Config
                        </li>
                    <?php endif; ?>
                    <?php if(UserPermission::has('admin_team_ban')): ?>
                        <li role="ban-team" class="border-top text-danger">
                            <i class="fas fa-ban"></i> Ban team
                        </li>
                    <?php endif; ?>
                    </ul>
                </div>
            </td>
            <td>
                <?=render_avatar($team, Team::get_avatar($team));?>
            </td>
            <td class="nowrap">
                <a href="<?=RouteMap::build_query([adminPanelController::INPUT_TEAM => $team['id']], 'admin_panel', ['group' => adminPanelController::GROUP_USER, 'block' => adminPanelController::BLOCK_USER_LIST]);?>">
                    <strong class="btn btn--small btn-outline-info btn--round"><?=_echo($team['name']);?></strong>
                </a>
            </td>
            <td>
                <a target="_blank" class="user-infomation" href="<?=RouteMap::get('profile', ['id' => $user_own['id']]);?>">
                    <?=render_avatar($user_own, null, true, true);?>
                </a>
            </td>
            <td class="nowrap">
                <i class="fas fa-hdd"></i> <?=_echo($team['config_name']);?>
            </td>
            <td class="nowrap align-center"><i class="fad fa-users"></i> <?=number_format($team['total_members'], 0, ',', '.');?></td>
            <td class="nowrap align-center"><?=number_format($team['total_mangas'], 0, ',', '.');?></td>
            <td class="nowrap align-center"><?=number_format($team['total_chapters'], 0, ',', '.');?></td>
        </tr>
    <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <?=html_pagination($pagination);?>
    </div>

<?php else: ?>
	<div class="alert alert--warning">Chưa có nhóm dịch nào.</div>
<?php endif; ?>



<script type="text/javascript">
    $(document).ready(function() {
        
        const DATA_TEAMS = <?=json_encode($team_list);?>;

    <?php if(UserPermission::has('admin_team_edit')): ?>
		$(document).on('click', '[role=change-name]', function() {
            const team = DATA_TEAMS.find(o => o.id == $(this).parent().attr('data-id'));

            if(!team) {
                return $.toastShow('Không tìm thấy nhóm dịch', {
					type: 'error',
					timeout: 3000
				});	;
            }

            const form = $(`
            <form method="post" class="margin-b-4">
                <?=$insertHiddenToken;?>
                <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_CHANGE_NAME;?>">
                <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="${team.id}">
                <div class="dialog-label">Tên mới:</div>
                <div class="form-group">
                    <div class="form-control">
                        <input type="text" class="form-input" name="<?=adminPanelController::INPUT_NAME;?>" placeholder="Enter new name" value="${team.name}">
                    </div>
                </div>
            </form>`);


			$.dialogShow({
				title: 'Thay đổi tên nhóm dịch',
				content: form,
				button: {
					confirm: 'Change',
					cancel: 'Cancel'
				},
				bgHide: false,
                isCenter: true,
                onInit: () => {
                    Validator({
                        form: form,
                        selector: '.form-control',
                        class_error: 'error',
                        rules: {
                            '.form-input': [
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

        $(document).on('click', '[role=change-own]', function() {
            const team = DATA_TEAMS.find(o => o.id == $(this).parent().attr('data-id'));

            if(!team) {
                return $.toastShow('Không tìm thấy nhóm dịch', {
					type: 'error',
					timeout: 3000
				});	;
            }

            const form = $(`
            <form method="post" class="margin-b-4">
                <?=$insertHiddenToken;?>
                <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_CHANGE_OWN;?>">
                <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="${team.id}">
                <div class="dialog-label">Chọn một thành viên:</div>
                <div class="form-group">
                    <div class="form-control">
                        <div class="search-user">
                            <input type="hidden" name="<?=adminPanelController::INPUT_OWN;?>" role="result-data">
                            <span class="form-control-feedback"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-input" placeholder="Tìm kiếm thành viên" role="search-user">
                            <div class="search-user__selected"></div>
                            <ul class="search-user__results"></ul>
                        </div>
                    </div>
                </div>
            </form>`);


			$.dialogShow({
				title: 'Thay đổi trưởng nhóm',
				content: form,
				button: {
					confirm: 'Change',
					cancel: 'Cancel'
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

                    $(document).off('keyup.search_user').on('keyup.search_user', '[role="search-user"]', function() {


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
                                type: "GET",
                                url: "<?=appendUrlApi(RouteMap::get('ajax', ['name' => ajaxController::SEARCH_USER]));?>",
                                data: {
                                    <?=InterFaceRequest::KEYWORD;?>: $(this).val()
                                },
                                dataType: 'json',
                                cache: false,
                                success: function(response)
                                {
                                    if(response.code == 200)
                                    {
                                        result_search.html('');
                                        response.data.forEach(user => {
                                            result_search.append(`
                                            <li class="search-user__results-item" data-id="${user.id}">
                                                <div class="user-infomation bg--none">
                                                    <div class="user-avatar" data-text="${user.first_name}" style="--bg-avatar: ${user.bg_avatar}">
                                                        <img src="${user.avatar}">
                                                    </div>
                                                    <div>
                                                        <div class="user-display-name">${user.display_name}</div>
                                                        <div class="user-username">@${user.username}</div>
                                                    </div>
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

                    $(document).off('click.search_user').on('click.search_user', '.search-user__results-item', function(e) {
                        e.stopPropagation();
                        selected_search.html($(this).html());
                        selected_search.show();
                        result_search.hide();
                        form.find('input[role="search-user"]').val('');
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

        $(document).on('click', '[role=change-config]', function() {
            const team = DATA_TEAMS.find(o => o.id == $(this).parent().attr('data-id'));

            if(!team) {
                return $.toastShow('Không tìm thấy nhóm dịch', {
					type: 'error',
					timeout: 3000
				});	;
            }

            const form = $(`
            <form method="post" class="margin-b-4">
                <?=$insertHiddenToken;?>
                <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_CHANGE_CONFIG;?>">
                <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="${team.id}">
                <div class="dialog-label">Chọn một cấu hình:</div>
                <div class="form-group">
                    <div class="form-control">
                        <select class="js-custom-select" name="<?=adminPanelController::INPUT_CONFIG;?>">
                    <?php foreach($list_config as $config): ?>
                        <option value="<?=$config['id'];?>"><?=_echo($config['name']);?></option>
                    <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>`);


			$.dialogShow({
				title: 'Thay đổi cấu hình upload',
				content: form,
				button: {
					confirm: 'Change',
					cancel: 'Cancel'
				},
				bgHide: false,
                isCenter: true,
                onInit: () => {
                    form.find('option[value="' + team.config_id + '"]').attr('selected', true);
                    JSCustomSelect();
                    Validator({
                        form: form,
                        selector: '.form-control',
                        class_error: 'error',
                        rules: {
                            'select': [
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

    <?php if(UserPermission::has('admin_team_ban')): ?>
        $(document).on('click', '[role="ban-team"]', async function(e) {
            e.preventDefault();

            const team = DATA_TEAMS.find(o => o.id == $(this).parent().attr('data-id'));

            if(!team) {
                return $.toastShow('Không tìm thấy nhóm dịch', {
					type: 'error',
					timeout: 3000
				});	;
            }

            const form = $(`
            <form method="post" class="margin-b-4">
                <?=$insertHiddenToken;?>
                <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_BAN;?>">
                    <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="${team.id}">
                <div class="dialog-label">Nhập lý do cấm:</div>
                <div class="form-group">
                    <div class="form-control">
                        <textarea class="form-textarea" name="<?=adminPanelController::INPUT_REASON;?>" placeholder="Có thể bỏ trống"></textarea>
                    </div>
                </div>
            </form>`);


			$.dialogShow({
				title: 'Cấm nhóm dịch',
				content: form,
				button: {
					confirm: 'Continue',
					cancel: 'Cancel'
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


    });
</script>