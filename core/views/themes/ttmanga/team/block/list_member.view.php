
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
                            <option value="<?=teamController::INPUT_USERNAME;?>">Tên thành viên</option>
                            <option <?=($type == teamController::INPUT_EMAIL ? 'selected' : null);?> value="<?=teamController::INPUT_EMAIL;?>">Email</option>
                        </select>               
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php if($user_list): ?>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th>ID</th>
                    <th></th>
                    <th width="60%">Thành viên</th>
                    <th></th>
                    <th class="align-center">Số truyện</th>
                    <th class="align-center">Số chương</th>
                </tr>
            </thead>
            <tbody>
    <?php foreach($user_list as $user):
        $total_manga = Manga::count(['user_upload' => $user['id']]);
        $total_chapter = Chapter::count(['user_upload' => $user['id']]);
    ?>
        <tr>
            <td>
            <?php if($user['id'] != Auth::$data['id'] && (UserPermission::isAdmin() || $user['role_level'] > Auth::$data['role_level'])): ?>
                <div class="drop-menu">
                    <div class="drop-menu__button">
                        <i class="fa fa-ellipsis-v"></i>
                    </div>
                    <ul class="drop-menu__content" data-id="<?=$user['id'];?>">
                    <?php if (!$user['team_id']): ?>
                        <li role="approval-user" class="text-success">
                            <i class="fas fa-user-check"></i> Chấp nhận
                        </li>
                        <li role="reject-user" class="text-danger">
                            <i class="fas fa-user-times"></i> Từ chối
                        </li>
                    <?php else: ?>
                        <li role="remove-user" class="text-danger">
                            <i class="fas fa-user-slash"></i> Xoá khỏi nhóm
                        </li>
                    <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
            </td>
            <td><?=$user['id'];?></td>
            <td>
                <?=render_avatar($user);?>
            </td>
            <td>
                <div class="d-flex flex-column">
                    <a target="_blank" href="<?=RouteMap::get('profile', ['id' => $user['id']]);?>">
                        <strong><?=User::get_username($user);?></strong>
                    </a>
                    <div class="email">
                        <i class="fas fa-envelope"></i> <span><?=_echo($user['email']);?></span>
                    </div>
                </div>
            </td>
            <td>
                <?php if (!$user['team_id']): ?>
                    <span class="btn btn-outline-warning btn--round btn--small py-0">Đang chờ xét duyệt</span>
                <?php endif; ?>
            </td>
            <td class="align-center"><?=number_format($total_manga, 0, ',', '.');?></td>
            <td class="align-center"><?=number_format($total_chapter, 0, ',', '.');?></td>
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

    <?php if($is_own): ?>
        $(document).on('click', '[role="approval-user"]', function(e) {
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
                <input type="hidden" name="<?=teamController::INPUT_ACTION;?>" value="<?=teamController::ACTION_APPROVAL_MEMBER;?>">
                <input type="hidden" name="<?=teamController::INPUT_ID;?>" value="${user.id}">
                <div class="dialog-message">Thành viên sau khi xác nhận có thể đăng tải truyện bình thường</div>
            </form>`);


			$.dialogShow({
				title: 'Xác nhận gia nhập nhóm',
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

        $(document).on('click', '[role="reject-user"]', function(e) {
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
                <input type="hidden" name="<?=teamController::INPUT_ACTION;?>" value="<?=teamController::ACTION_REJECT_MEMBER;?>">
                <input type="hidden" name="<?=teamController::INPUT_ID;?>" value="${user.id}">
                <div class="dialog-label">Nhập lý do từ chối:</div>
                <div class="form-group">
                    <div class="form-control">
                        <textarea class="form-textarea" name="<?=teamController::INPUT_REASON;?>" placeholder="Có thể bỏ trống"></textarea>
                    </div>
                </div>
            </form>`);


			$.dialogShow({
				title: 'Từ chối gia nhập nhóm',
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

        $(document).on('click', '[role="remove-user"]', function(e) {
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
                <input type="hidden" name="<?=teamController::INPUT_ACTION;?>" value="<?=teamController::ACTION_REMOVE_MEMBER;?>">
                <input type="hidden" name="<?=teamController::INPUT_ID;?>" value="${user.id}">
                <div class="dialog-label">Nhập lý do xoá khỏi nhóm:</div>
                <div class="form-group">
                    <div class="form-control">
                        <textarea class="form-textarea" name="<?=teamController::INPUT_REASON;?>" placeholder="Có thể bỏ trống"></textarea>
                    </div>
                </div>
            </form>`);


			$.dialogShow({
				title: 'Xoá thành viên khỏi nhóm',
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


    });
</script>