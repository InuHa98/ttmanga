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
        <span>Chờ xét duyệt (<b><?=number_format($count, 0, ',', '.');?></b>)</span>
    </div>

    <?php if($team_list): ?>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th width="30%"></th>
                    <th>Team Name</th>
                    <th>Own</th>
                    <th width="70%">Note</th>
                    <th>Date</th>
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
            <td class="nowrap" data-id="<?=$team['id'];?>">
                <span class="btn btn--small btn--success" role="accept-team">
                    <i class="fas fa-check"></i> Đồng ý
                </span>
                <span class="btn btn--small btn--danger" role="reject-team">
                    <i class="fas fa-times"></i> Từ chối
                </span>
            </td>
            <td class="nowrap">
                <strong class="btn btn--small btn--round btn-outline-info"><?=_echo($team['name']);?></strong>
            </td>
            <td>
                <a target="_blank" class="user-infomation" href="<?=RouteMap::get('profile', ['id' => $user_own['id']]);?>">
                    <?=render_avatar($user_own, null, true, true);?>
                </a>
            </td>
            <td><?=_echo($team['note'], true);?></td>
            <td class="nowrap">
                <?=_time($team['created_at']);?>
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
	<div class="alert alert--warning">Không có nhóm dịch mới nào cần xét duyệt.</div>
<?php endif; ?>



<script type="text/javascript">
    $(document).ready(function() {
        
        const DATA_TEAMS = <?=json_encode($team_list);?>;

    <?php if(UserPermission::has('admin_team_approval')): ?>
        $(document).on('click', '[role="accept-team"]', function() {
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
                <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_ACCEPT;?>">
                <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="${team.id}">
                <div class="dialog-label">Vui lòng chọn một cấu hình upload:</div>
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
				title: 'Chấp thuận nhóm dịch mới',
				content: form,
				button: {
					confirm: 'Accept',
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

        $(document).on('click', '[role="reject-team"]', function() {
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
                <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_REJECT;?>">
                <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="${team.id}">
                <div class="dialog-label">Nhập lý do từ chối:</div>
                <div class="form-group">
                    <div class="form-control">
                        <textarea class="form-textarea" name="<?=adminPanelController::INPUT_REASON;?>" placeholder="Có thể bỏ trống"></textarea>
                    </div>
                </div>
            </form>`);


			$.dialogShow({
				title: 'Từ chối nhóm dịch mới',
				content: form,
				button: {
					confirm: 'Reject',
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