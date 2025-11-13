    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <form method="GET">
            <select class="js-custom-select" name="<?=adminPanelController::INPUT_STATUS;?>" onchange="this.form.submit()">
                <option value="<?=RegisterKey::STATUS_LIVE;?>">Có hiệu lực</option>
                <option <?=($status == RegisterKey::STATUS_DIE ? 'selected' : null);?> value="<?=RegisterKey::STATUS_DIE;?>">Không có hiệu lực</option>
            </select>
        </form>
        <?php if($is_access_create): ?>
        <div class="action">
            <a class="btn btn--small btn--round" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_REGISTER_KEY, 'action' => adminPanelController::ACTION_ADD]);?>"><i class="fas fa-plus"></i> Tạo mã</a>
        </div>
        <?php endif; ?>
    </div>

    <?php if($register_key_list):?>
        <div class="table-scroll mb-4">
            <table>
                <thead>
                    <tr>
                        <th></th>
                        <th>Mã</th>
                        <th>Số lượng</th>
                        <th>Trạng thái</th>
                        <th>Tạo bởi</th>
                        <th>Tài khoản đăng ký</th>
                        <th>Ghi chú</th>
                        <th>Ngày tạo</th>
                    </tr>
                </thead>
                <tbody>
        <?php foreach($register_key_list as $register_key):
            $user_creator = [
                'id' => $register_key['creator_id'],
                'username' => $register_key['creator_username'],
                'avatar' => $register_key['creator_avatar'],
                'user_ban' => $register_key['creator_user_ban'],
                'role_color' => $register_key['creator_role_color']
            ];
            $lst_register = RegisterKeyUserRegister::list(['register_key_id' => $register_key['id']]);
        ?>
            <tr>
                <td>
                <?php if($is_access_delete && $register_key['status'] == RegisterKey::STATUS_LIVE): ?>
                    <div class="drop-menu">
                        <div class="drop-menu__button">
                            <i class="fa fa-ellipsis-v"></i>
                        </div>
                        <ul class="drop-menu__content">
                            <li class="border-top">
                                <a role="delete-key" href="<?=RouteMap::build_query([InterFaceRequest::ID => $register_key['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_REGISTER_KEY, 'action' => adminPanelController::ACTION_DELETE]);?>"><i class="fas fa-trash"></i> Delete</a>
                            </li>
                        </ul>
                    </div>
                <?php endif; ?>
                </td>
                <td>
                    <div class="form-group">
                        <div class="form-control">
                            <div class="input-group">
                                <input class="form-input" value="<?=_echo($register_key['key']);?>">
                                <div class="input-group-append">
                                    <button type="button" role="btn-copy" class="btn btn-outline-gray btn-dim"><i class="fa fa-copy input-copy"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="nowrap align-center"><?=number_format($register_key['quantity'], 0, ',', '.');?></td>
                <td>
                    <?php if($register_key['status'] == RegisterKey::STATUS_LIVE): ?>
                        <span class="dot dot-success">Có hiệu lực</span>
                    <?php else: ?>
                        <span class="dot dot-gray">Không có hiệu lực</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a target="_blank" class="user-infomation" href="<?=RouteMap::get('profile', ['id' => $user_creator['id']]);?>">
                        <?=render_avatar($user_creator, null, true, true);?>
                    </a>
                </td>
                <td>
                <?php if($lst_register): ?>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                    <?php foreach($lst_register as $user): ?>
                    <a target="_blank" class="user-infomation" href="<?=RouteMap::get('profile', ['id' => $user['id']]);?>">
                        <?=render_avatar($user, null, true, true);?>
                    </a>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                </td>
                <td><?=_echo($register_key['note']);?></td>
                <td class="nowrap"><?=_time($register_key['created_at']);?></td>
            </tr>
        <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <?=html_pagination($pagination);?>
        </div>

    <?php else: ?>
        <div class="alert alert--warning">Chưa có mã đăng ký nào.</div>
    <?php endif; ?>

<script type="text/javascript">
    const  copyText  = (text) => {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text);
    } else {
        const el = document.createElement("textarea");
        el.value = text;
        el.style.position = "fixed"; // tránh cuộn
        el.style.opacity = "0";
        document.body.appendChild(el);
        el.focus();
        el.select();
        document.execCommand("copy");
        el.remove();
    }
    }
    $(document).ready(function() {
        $('[role="delete-key"]').on('click', async function(e) {
            e.preventDefault();

            if(await comfirm_dialog('Xoá bỏ nhãn dán', 'Bạn thực sự muốn xoá bỏ mục này?') == true) {
                window.location.href = $(this).attr('href');
            }
        });

        $('[role="btn-copy"]').on('click', function(e) {
            e.preventDefault();
            const key = $(this).parents('.input-group').find('input').val();
            copyText('<?=RouteMap::get('register');?>?<?=Auth::INPUT_REGISTER_KEY;?>=' + key);
            $.toastShow('Sao chép thành công', {
                type: 'success',
                timeout: 3000
            });
        });

    });
</script>