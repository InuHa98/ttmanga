    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <span>Phân quyền truy cập thành viên</span>
        <?php if($is_access_create): ?>
            <div class="action">
                <a class="btn btn--small btn--round" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_ROLE, 'action' => adminPanelController::ACTION_ADD]);?>"><i class="fas fa-plus"></i> Add</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if($role_list): ?>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th></th>
                        <th>Role Name</th>
                        <th>Level</th>
                        <th>Permissions</th>
                    </tr>
                </thead>
                <tbody>
        <?php foreach($role_list as $role):
            $role_perms = json_decode($role['perms'], true);
        ?>
            <tr>
                <td>
                    <div class="drop-menu">
                        <div class="drop-menu__button">
                            <i class="fa fa-ellipsis-v"></i>
                        </div>
                        <ul class="drop-menu__content">
                        <?php if($is_access_edit): ?>
                            <li>
                                <a href="<?=RouteMap::build_query([InterFaceRequest::ID => $role['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_ROLE, 'action' => adminPanelController::ACTION_EDIT]);?>"><i class="fas fa-edit"></i> Edit</a>
                            </li>
                        <?php endif; ?>
                        <?php if($role['is_default'] != Role::IS_DEFAULT && $is_access_delete): ?>
                            <li class="border-top">
                                <a role="delete-role" href="<?=RouteMap::build_query([InterFaceRequest::ID => $role['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_ROLE, 'action' => adminPanelController::ACTION_DELETE]);?>"><i class="fas fa-trash"></i> Delete</a>
                            </li>
                        <?php endif; ?>
                        </ul>
                    </div>
                </td>
                <td>
                    <span class="user-role" style="background: <?=_echo($role['color']);?>"><?=_echo($role['name']);?></span>
                </td>
                <td>
                    <?=_echo($role['level']);?>
                </td>
                <td class="nowrap">
                    <?php if($role_perms): ?>
                    <?php foreach($role_perms as $perm): ?>
                        <span class="role-perm"><?=$perm;?></span>
                    <?php endforeach; ?>
                    <?php endif; ?>
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
        <div class="alert alert--warning">Chưa có chức vụ nào.</div>
    <?php endif; ?>

<script type="text/javascript">
    $(document).ready(function() {
        $('[role="delete-role"]').on('click', async function(e) {
            e.preventDefault();

            if(await comfirm_dialog('Xoá bỏ nhãn dán', 'Bạn thực sự muốn xoá bỏ mục này?') == true) {
                window.location.href = $(this).attr('href');
            }
        });
    });
</script>