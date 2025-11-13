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
        <span>Bị cấm (<b><?=number_format($count, 0, ',', '.');?></b>)</span>
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
                    <th>Name</th>
                    <th>Own</th>
                    <th>Config</th>
                    <th width="30%" class="align-center">Member</th>
                    <th width="30%" class="align-center">Manga</th>
                    <th width="30%" class="align-center">Chapter</th>
                    <th>User Ban</th>
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

        $user_ban = [
            'id' => $team['user_ban'],
            'username' => $team['user_ban_username'],
            'avatar' => $team['user_ban_avatar'],
            'user_ban' => $team['user_ban_id'],
            'role_color' => $team['user_ban_role_color']
        ];
    ?>
        <tr>
            <td>
                <div class="drop-menu">
                    <div class="drop-menu__button">
                        <i class="fa fa-ellipsis-v"></i>
                    </div>
                    <ul class="drop-menu__content" data-id="<?=$team['id'];?>">
                    <?php if(UserPermission::has('admin_team_unban')): ?>
                        <li role="unban-team">
                            <i class="far fa-unlock"></i> UnBan team
                        </li>
                    <?php endif; ?>
                    </ul>
                </div>
            </td>
            <td>
                <?=render_avatar($team, Team::get_avatar($team)); ?>
            </td>
            <td class="nowrap">
                <a href="<?=RouteMap::build_query([adminPanelController::INPUT_TEAM => $team['id']], 'admin_panel', ['group' => adminPanelController::GROUP_USER, 'block' => adminPanelController::BLOCK_USER_LIST]);?>">
                    <strong strong class="btn btn--small btn-outline-danger"><?=_echo($team['name']);?></strong>
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
            <td class="nowrap align-center"><i class="fad fa-users"></i> <?=$team['total_members'];?></td>
            <td class="nowrap align-center"><?=$team['total_mangas'];?></td>
            <td class="nowrap align-center"><?=$team['total_chapters'];?></td>
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
	<div class="alert alert--warning">Chưa có nhóm dịch bị cấm nào.</div>
<?php endif; ?>




<script type="text/javascript">
    $(document).ready(function() {
        
        const DATA_TEAMS = <?=json_encode($team_list);?>;


    <?php if(UserPermission::has('admin_team_unban')): ?>
        $(document).on('click', '[role="unban-team"]', async function(e) {
            e.preventDefault();

            const team = DATA_TEAMS.find(o => o.id == $(this).parent().attr('data-id'));

            if(!team) {
                return $.toastShow('Không tìm thấy nhóm dịch', {
					type: 'error',
					timeout: 3000
				});	;
            }

            if(await comfirm_dialog('Bỏ cấm nhóm dịch', 'Bạn thực sự muốn bỏ cấm nhóm dịch: <b>' + team.name + '</b>?') == true) {
                const form = $(`
                <form method="post" style="display: none">
                    <?=$insertHiddenToken;?>
                    <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_UNBAN;?>">
                    <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="${team.id}">
                </form>`);
                $('body').append(form);
                form.submit();
            }
        });
    <?php endif; ?>


    });
</script>