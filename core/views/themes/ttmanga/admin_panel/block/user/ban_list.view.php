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
        <div>Có <b><?=number_format($count, 0, ',', '.');?></b> thành viên</div>
        <form method="GET" class="d-flex justify-content-start align-items-center gap-2 flex-wrap">
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
                    <th>User Ban</th>
                </tr>
            </thead>
            <tbody>
    <?php foreach($user_list as $user):
        $user_ban = [
            'id' => $user['user_ban_id'],
            'username' => $user['user_ban_username'],
            'avatar' => $user['user_ban_avatar'],
            'user_ban' => $user['user_ban_ban_id'],
            'role_color' => $user['user_ban_role_color']
        ];
    ?>
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
                    <?php if(UserPermission::has('admin_user_unban')): ?>
                        <li role="unban-user">
                            <i class="far fa-unlock"></i> UnBan user
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
            <td>
                <a target="_blank" class="user-infomation" href="<?=RouteMap::get('profile', ['id' => $user_ban['id']]);?>">
                    <?=render_avatar($user_ban, null, true, true);?>
                </a>
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
            return o;
        });

    <?php if(UserPermission::has('admin_user_unban')): ?>
        $(document).on('click', '[role="unban-user"]', async function(e) {
            e.preventDefault();

            const user = DATA_USERS.find(o => o.id == $(this).parent().attr('data-id'));

            if(!user) {
                return $.toastShow('Không tìm thấy user', {
					type: 'error',
					timeout: 3000
				});	;
            }

            if(await comfirm_dialog('Bỏ cấm thành viên', 'Bỏ cấm thành viên: <b>' + user.username + '</b>?') == true) {
                const form = $(`
                <form method="post" style="display: none">
                    <?=$insertHiddenToken;?>
                    <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_UNBAN;?>">
                    <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="${user.id}">
                </form>`);
                $('body').append(form);
                form.submit();
            }
        });
    <?php endif; ?>


    });
</script>