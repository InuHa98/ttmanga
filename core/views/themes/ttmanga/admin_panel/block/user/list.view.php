
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
        <?php if (!$team): ?>
            <div>Có <b><?=number_format($count, 0, ',', '.');?></b> thành viên</div>
        <?php else: ?>
            <div>Thành viên nhóm: <b><?=_echo($team['name']);?></b> (<b><?=number_format($count, 0, ',', '.');?></b>)</div>
        <?php endif; ?>

        <form method="GET" class="d-flex justify-content-start align-items-center gap-2 flex-wrap">
            <?php if ($team): ?>
                <input type="hidden" name="<?=adminPanelController::INPUT_TEAM;?>" value="<?=$team['id'];?>" />
            <?php endif; ?>
            <div>
                <div class="input-group">
                    <span class="form-control-feedback"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-input border-radius-left" name="<?=InterFaceRequest::KEYWORD;?>" placeholder="Tìm kiếm" value="<?=_echo($keyword);?>"/>
                    <div class="input-group-append">
                        <select class="js-custom-select" name="<?=InterFaceRequest::TYPE;?>" onchange="this.form.submit()">
                            <option value="<?=adminPanelController::INPUT_USERNAME;?>">Username</option>
                            <option <?=($type == adminPanelController::INPUT_EMAIL ? 'selected' : null);?> value="<?=adminPanelController::INPUT_EMAIL;?>">Email</option>
                        </select>               
                    </div>
                </div>
            </div>
            <div>
                <select class="js-custom-select" name="<?=adminPanelController::INPUT_ROLE;?>" onchange="this.form.submit()">
                    <option value="<?=adminPanelController::INPUT_ALL;?>">Tất cả chức vụ</option>
                <?php foreach($list_role as $rl): ?>
                    <option <?=($role == $rl['id'] ? 'selected' : null);?> value="<?=$rl['id'];?>"><?=_echo($rl['name']);?></option>
                <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

        <?php if($user_list): ?>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th></th>
                    <th>User</th>
                    <th></th>
                    <th>Role</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
    <?php foreach($user_list as $user): ?>
        <tr>
            <td><?=$user['id'];?></td>
            <td>
                <?=render_avatar($user);?>
            </td>
            <td>
                <a target="_blank" href="<?=RouteMap::get('profile', ['id' => $user['id']]);?>">
                    <strong><?=User::get_username($user);?></strong>
                </a>
            </td>
            <td>
            <?php if($user['id'] != Auth::$data['id'] && (UserPermission::isAdmin() || $user['role_level'] > Auth::$data['role_level'])): ?>
                <div class="drop-menu">
                    <div class="drop-menu__button">
                        <i class="fa fa-ellipsis-v"></i>
                    </div>
                    <ul class="drop-menu__content" data-id="<?=$user['id'];?>">
                    <?php if(UserPermission::has('admin_user_edit')): ?>
                        <li role="change-username">
                            <i class="fa fa-user"></i> Change Username
                        </li>
                        <li role="change-password">
                            <i class="fa fa-lock"></i> Change password
                        </li>
                        <li role="change-email">
                            <i class="fas fa-envelope"></i> Change email
                        </li>
                    <?php if(UserPermission::isAdmin()): ?>
                        <li role="change-role">
                            <i class="fab fa-joomla"></i> Change role
                        </li>
                        <li class="border-bottom" role="change-permission">
                            <i class="fas fa-user-cog"></i> Change permission
                        </li>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php if(UserPermission::has('admin_user_ban')): ?>
                        <li role="ban-user" class="text-danger">
                            <i class="fas fa-ban"></i> Ban user
                        </li>
                    <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
            </td>
            <td>
                <span class="user-role" style="background: <?=_echo($user['role_color']);?>"><?=_echo($user['role_name']);?></span>
            </td>
            <td>
                <i class="fas fa-envelope"></i>
                <span><?=_echo($user['email']);?></span>
            </td>
        </tr>
    <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <?=html_pagination($pagination);?>
    </div>

<?php else: ?>
	<div class="alert alert--warning">Chưa có thành viên nào.</div>
<?php endif; ?>




<script type="text/javascript">
    $(document).ready(function() {
        
        const DATA_USERS = <?=json_encode($user_list);?>;
        DATA_USERS.map(o => {
            o.perms = JSON.parse(o.perms);
            o.role_perms = JSON.parse(o.role_perms);
            return o;
        });

    <?php if(UserPermission::has('admin_user_edit')): ?>
		$(document).on('click', '[role=change-username]', function() {
            const user = DATA_USERS.find(o => o.id == $(this).parent().attr('data-id'));

            if(!user) {
                return $.toastShow('Không tìm thấy user', {
					type: 'error',
					timeout: 3000
				});	;
            }

            const form = $(`
            <form method="post" class="margin-b-4">
                <?=$insertHiddenToken;?>
                <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_CHANGE_USERNAME;?>">
                <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="${user.id}">
                <div class="dialog-label">Username mới:</div>
                <div class="form-group">
                    <div class="form-control">
                        <input type="text" class="form-input" name="<?=adminPanelController::INPUT_USERNAME;?>" placeholder="Enter new username" value="${user.username}">
                    </div>
                </div>
            </form>`);


			$.dialogShow({
				title: 'Thay đổi Username',
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
                                Validator.isRequired(),
                                Validator.isUsername('Username không hợp lệ'),
                                Validator.minLength(<?=Auth::USERNAME_MIN_LENGTH;?>),
                                Validator.maxLength(<?=Auth::USERNAME_MAX_LENGTH;?>)
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

        $(document).on('click', '[role=change-password]', function() {
            const user = DATA_USERS.find(o => o.id == $(this).parent().attr('data-id'));
            const min = <?=Auth::PASSWORD_MIN_LENGTH;?>;
            const max = <?=Auth::PASSWORD_MAX_LENGTH;?>;
            if(!user) {
                return $.toastShow('Không tìm thấy user', {
					type: 'error',
					timeout: 3000
				});	;
            }

            const form = $(`
            <form method="post" class="margin-b-4">
                <?=$insertHiddenToken;?>
                <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_CHANGE_PASSWORD;?>">
                <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="${user.id}">
                <div class="dialog-label">Mật khẩu mới:</div>
                <div class="form-group">
                    <div class="form-control">
                        <input type="password" class="form-input" id="new-password" name="<?=adminPanelController::INPUT_PASSWORD;?>" placeholder="Enter new password">
                    </div>
                </div>
                <div class="dialog-label margin-t-4">Nhập lại mật khẩu mới:</div>
                <div class="form-group">
                    <div class="form-control">
                        <input type="password" class="form-input" id="new-password-confirm" name="<?=adminPanelController::INPUT_PASSWORD_CONFIRM;?>" placeholder="Confirm new password">
                    </div>
                </div>
            </form>`);


			$.dialogShow({
				title: 'Thay đổi Password',
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
                            '#new-password': [
                                Validator.isRequired(),
                                Validator.minLength(min, 'Mật khẩu phải từ '+min+' kí tự trở lên'),
                                Validator.maxLength(max, 'Mật khẩu phải ít hơn '+max+' kí tự')
                            ],
                            '#new-password-confirm': [
                                Validator.isRequired(),
                                Validator.isConfirmed(document.querySelector('#new-password'), 'Mật khẩu nhập lại không chính xác')
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

        $(document).on('click', '[role=change-email]', function() {
            const user = DATA_USERS.find(o => o.id == $(this).parent().attr('data-id'));

            if(!user) {
                return $.toastShow('Không tìm thấy user', {
					type: 'error',
					timeout: 3000
				});	;
            }

            const form = $(`
            <form method="post" class="margin-b-4">
                <?=$insertHiddenToken;?>
                <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_CHANGE_EMAIL;?>">
                <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="${user.id}">
                <div class="dialog-label">Email mới:</div>
                <div class="form-group">
                    <div class="form-control">
                        <input type="text" class="form-input" name="<?=adminPanelController::INPUT_EMAIL;?>" placeholder="Enter new email" value="${user.email}">
                    </div>
                </div>
            </form>`);


			$.dialogShow({
				title: 'Thay đổi Email',
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
                                Validator.isRequired(),
                                Validator.isEmail('Email không hợp lệ')
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

    <?php if(UserPermission::isAdmin()): ?>
        $(document).on('click', '[role=change-role]', function() {
            const user = DATA_USERS.find(o => o.id == $(this).parent().attr('data-id'));

            if(!user) {
                return $.toastShow('Không tìm thấy user', {
					type: 'error',
					timeout: 3000
				});	;
            }

            const form = $(`
            <form method="post" class="margin-b-4">
                <?=$insertHiddenToken;?>
                <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_CHANGE_ROLE;?>">
                <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="${user.id}">
                <div class="dialog-label">Chọn một chức vụ:</div>
                <div class="form-group">
                    <div class="form-control">
                        <select class="js-custom-select" name="<?=adminPanelController::INPUT_ROLE;?>">
                    <?php foreach($list_role as $role): ?>
                        <option value="<?=$role['id'];?>"><?=_echo($role['name']);?></option>
                    <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </form>`);


			$.dialogShow({
				title: 'Thay đổi chức vụ',
				content: form,
				button: {
					confirm: 'Change',
					cancel: 'Cancel'
				},
				bgHide: false,
                isCenter: true,
                onInit: () => {
                    form.find('option[value="' + user.role_id + '"]').attr('selected', true);
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

        $(document).on('click', '[role=change-permission]', function() {
            const user = DATA_USERS.find(o => o.id == $(this).parent().attr('data-id'));

            if(!user) {
                return $.toastShow('Không tìm thấy user', {
					type: 'error',
					timeout: 3000
				});	;
            }

            const form = $(`
            <form method="post" class="margin-b-4">
                <?=$insertHiddenToken;?>
                <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_CHANGE_PERMISSION;?>">
                <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="${user.id}">
                <div class="form-group">
                    <div class="form-control">
                        <div class="genre-list">
                        <?php foreach($list_permission as $key => $value): ?>
                            <div class="state-btn" title="<?=_echo($value);?>">
                                <select name="<?=adminPanelController::INPUT_PERMISSION;?>[<?=$key;?>]" data-perm="<?=$key;?>">
                                    <option value="0"></option>
                                    <option value="1"></option>
                                </select>
                                <label><?=$key;?></label>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </form>`);


			$.dialogShow({
				title: 'Thay đổi Permission',
				content: form,
				button: {
					confirm: 'Change',
					cancel: 'Cancel'
				},
				bgHide: false,
                isCenter: true,
                onInit: () => {

                    user.role_perms.forEach(function(perm) {
                        const select = form.find('select[data-perm="' + perm + '"]');
                        select.val(1);
                        select.parents('.state-btn').addClass('include');
                    });

                    for(const key in user.perms) {
                        const value = user.perms[key];
                        const select = form.find('select[data-perm="' + key + '"]');
                        if(value == true || value == 'true' || value == 1 || value == '1') {
                            select.val(1);
                            select.parents('.state-btn').addClass('include');
                        } else {
                            select.val(0);
                            select.parents('.state-btn').removeClass('include');
                        }
                    }


                    $(document).off('click.permission').on('click.permission', '.state-btn', function() {
                        var selectedGenre = $(this).children('select');
                        if ($(this).hasClass('include')) {
                            $(this).removeClass('include');
                            selectedGenre.val(0).change();
                        } else {
                            $(this).addClass('include');
                            selectedGenre.val(1).change();
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
    <?php endif; ?>

    <?php if(UserPermission::has('admin_user_ban')): ?>
        $(document).on('click', '[role="ban-user"]', function(e) {
            e.preventDefault();

            const user = DATA_USERS.find(o => o.id == $(this).parent().attr('data-id'));

            if(!user) {
                return $.toastShow('Không tìm thấy user', {
					type: 'error',
					timeout: 3000
				});	;
            }


            const form = $(`
            <form method="post" class="margin-b-4">
                <?=$insertHiddenToken;?>
                <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_BAN;?>">
                <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="${user.id}">
                <div class="dialog-label">Nhập lý do cấm:</div>
                <div class="form-group">
                    <div class="form-control">
                        <textarea class="form-textarea" name="<?=adminPanelController::INPUT_REASON;?>" placeholder="Có thể bỏ trống"></textarea>
                    </div>
                </div>
            </form>`);


			$.dialogShow({
				title: 'Cấm thành viên',
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