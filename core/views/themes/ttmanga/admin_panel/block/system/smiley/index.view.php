<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-2">
    <span>Danh sách nhãn dán</span>
<?php if($is_access_create): ?>
    <a class="btn btn--small btn--round" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_SMILEY, 'action' => adminPanelController::ACTION_ADD]);?>"><i class="fas fa-plus"></i> Add</a>
<?php endif; ?>
</div>

<?php if($smiley_list): ?>
    <?php foreach($smiley_list as $smiley):
        $user_create = [
            'id' => $smiley['user_id'],
            'username' => $smiley['user_username'],
            'avatar' => $smiley['user_avatar'],
            'user_ban' => $smiley['user_ban_id'],
            'role_color' => $smiley['user_role_color'],
        ];
        $images = json_decode($smiley['images'], true);
    ?>
    <div class="smiley-list-box">
        <div class="smiley-list-box__header">
            <span class="smiley-name"><?=_echo($smiley['name']);?></span>
            <span class="smiley-user">
                <div class="drop-menu">
                    <div class="drop-menu__button">
                        <i class="fa fa-ellipsis-v"></i>
                    </div>
                    <ul class="drop-menu__content">
                    <?php if($is_access_edit): ?>
                        <li>
                            <a href="<?=RouteMap::build_query([InterFaceRequest::ID => $smiley['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_SMILEY, 'action' => adminPanelController::ACTION_EDIT]);?>"><i class="fas fa-edit"></i> Edit</a>
                        </li>
                    <?php endif; ?>
                    <?php if($is_access_delete): ?>
                        <li class="border-top">
                            <a role="delete-smiley" href="<?=RouteMap::build_query([InterFaceRequest::ID => $smiley['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_SMILEY, 'action' => adminPanelController::ACTION_DELETE]);?>"><i class="fas fa-trash"></i> Delete</a>
                        </li>
                    <?php endif; ?>
                    </ul>
                </div>
                <a class="user-infomation bg--white" target="_blank" href="<?=RouteMap::get('profile', ['id' => $user_create['id']]);?>">
                    <?=render_avatar($user_create, null, true, true);?>
                </a>
            </span>
        </div>
    <?php if($images): ?>
        <div class="smiley-list-box__body">
    <?php foreach($images as $image): ?>
        <img src="<?=_echo($image);?>" />
    <?php endforeach; ?>
        </div>
    <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <div class="pagination">
        <?=html_pagination($pagination);?>
    </div>
    
<?php else: ?>
	<div class="alert alert--warning">Chưa có nhãn dán nào.</div>
<?php endif; ?>

<script type="text/javascript">
    $(document).ready(function() {
        $('[role="delete-smiley"]').on('click', async function(e) {
            e.preventDefault();

            if(await comfirm_dialog('Xoá bỏ nhãn dán', 'Bạn thực sự muốn xoá bỏ mục này?') == true) {
                window.location.href = $(this).attr('href');
            }
        });
    });
</script>