    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <span>Danh sách thể loại (<b><?=number_format($count, 0, ',', '.');?></b>)</span>
        <?php if($is_access_create): ?>
            <div class="action">
                <a class="btn btn--small btn--round" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_GENRES, 'action' => adminPanelController::ACTION_ADD]);?>"><i class="fas fa-plus"></i> Add</a>
            </div>
        <?php endif; ?>
    </div>


    <?php if($genres_list): ?>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th></th>
                        <th>Name</th>
                        <th></th>
                        <th width="100%">Description</th>
                    </tr>
                </thead>
                <tbody>
        <?php foreach($genres_list as $genres): ?>
            <tr class="valign-top">
                <td>
                    <?=$genres['id'];?>
                </td>
                <td>
                    <strong><?=_echo($genres['name']);?></strong>
                </td>
                <td>
                    <div class="drop-menu">
                        <div class="drop-menu__button">
                            <i class="fa fa-ellipsis-v"></i>
                        </div>
                        <ul class="drop-menu__content">
                        <?php if($is_access_edit): ?>
                            <li>
                                <a href="<?=RouteMap::build_query([InterFaceRequest::ID => $genres['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_GENRES, 'action' => adminPanelController::ACTION_EDIT]);?>"><i class="fas fa-edit"></i> Edit</a>
                            </li>
                        <?php endif; ?>
                        <?php if($is_access_delete): ?>
                            <li class="border-top">
                                <a role="delete-genres" href="<?=RouteMap::build_query([InterFaceRequest::ID => $genres['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_GENRES, 'action' => adminPanelController::ACTION_DELETE]);?>"><i class="fas fa-trash"></i> Delete</a>
                            </li>
                        <?php endif; ?>
                        </ul>
                    </div>
                </td>
                <td>
                    <?=_echo($genres['text'], true);?>
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
        <div class="alert alert--warning">Chưa có thể loại nào.</div>
    <?php endif; ?>


<script type="text/javascript">
    $(document).ready(function() {
        $('[role="delete-genres"]').on('click', async function(e) {
            e.preventDefault();

            if(await comfirm_dialog('Xoá bỏ thể loại', 'Bạn thực sự muốn xoá thể loại này?') == true) {
                window.location.href = $(this).attr('href');
            }
        });
    });
</script>