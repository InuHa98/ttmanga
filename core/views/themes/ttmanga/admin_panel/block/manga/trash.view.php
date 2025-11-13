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
    <span>Truyện đã xoá (<b><?=number_format($count, 0, ',', '.');?></b>)</span>
    <form method="GET" class="action">
        <div class="input-group">
            <span class="form-control-feedback"><i class="fas fa-search"></i></span>
            <input type="text" class="form-input border-radius-left" name="<?=InterFaceRequest::KEYWORD;?>" placeholder="Tìm kiếm theo..." value="<?=_echo($keyword);?>"/>
            <div class="input-group-append">
                <select class="js-custom-select" name="<?=InterFaceRequest::TYPE;?>" onchange="this.form.submit()">
                    <option value="<?=adminPanelController::INPUT_NAME;?>">Tên truyện</option>
                    <option <?=($type == adminPanelController::INPUT_TEAM ? 'selected' : null);?> value="<?=adminPanelController::INPUT_TEAM;?>">Tên nhóm</option>
                </select>               
            </div>
        </div>
    </form>
</div>

<?php if($lst_manga): ?>
<div class="table-scroll">
    <table class="list-manga">
        <thead>
            <tr>
                <th></th>
                <th></th>
                <th width="70%">Tên truyện</th>
                <th width="30%">Mới nhất</th>
                <th>Nhóm sở hữu</th>
            </tr>
        </thead>
        <tbody>
<?php foreach($lst_manga as $manga):
    $teams = array_filter(explode(Manga::SEPARATOR, $manga['team_owns'] ?? ''));
?>
    <tr>
        <td>
        <?php if(UserPermission::has('admin_manga_delete')): ?>
            <button class="btn btn--info btn--round btn--small" data-id="<?=$manga['id'];?>" data-role="restore"><i class="fas fa-history"></i> Khôi phục</button>
            <button class="btn btn--danger btn--round btn--small" data-id="<?=$manga['id'];?>" data-role="delete"><i class="fas fa-trash"></i> Xoá hẳn</button>
        <?php endif; ?>
        </td>
        <td>
            <img class="cover-manga" src="<?=_echo($manga['image']);?>" />
        </td>
        <td class="name-manga">
            <a target="_blank" href="<?=RouteMap::get('manga_management', ['id' => $manga['id'], 'action' => mangaManagementController::ACTION_DETAIL]);?>">
                <strong><?=_echo($manga['name']);?></strong>
            </a>
        </td>
        <td>
        <?php if ($manga['id_last_chapter']): ?>
            <a class="name" target="_blank" href="<?=RouteMap::get('chapter', ['id_manga' => $manga['id'], 'id_chapter' => $manga['id_last_chapter']]);?>">
                <?=_echo($manga['name_last_chapter']);?>
            </a>
        <?php else: ?>
            <span class="empty">Chưa có</span>
        <?php endif; ?>
        </td>
        <td class="nowrap">
            <?php if($teams): ?>
            <i class="fas fa-layer-group"></i>
            <ul class="list-teams">
                <?php foreach($teams as $team): ?>
                    <li>
                        <a target="_blank" href="<?=RouteMap::get('team', ['name' => $team]);?>"><?=_echo($team);?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
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
<div class="alert alert--warning">Chưa có truyện nào.</div>
<?php endif; ?>

<script type="text/javascript">
    $(document).ready(function() {
        
        const DATA_MANGA = <?=json_encode($lst_manga);?>;
        DATA_MANGA.map(o => {
            return o;
        });


        $(document).on('click', '[data-role="restore"]', async function(e) {
            e.preventDefault();

            const manga = DATA_MANGA.find(o => o.id == $(this).attr('data-id'));

            if(!manga) {
                return $.toastShow('Không tìm thấy truyện', {
					type: 'error',
					timeout: 3000
				});	;
            }

            if(await comfirm_dialog('Khôi phục truyện đã xoá', 'Người đọc có thể theo dõi truyện bình thường: <b>' + manga.name + '</b>?') == true) {
                const form = $(`
                <form method="post" style="display: none">
                    <?=$insertHiddenToken;?>
                    <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_RESTORE;?>">
                    <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="${manga.id}">
                </form>`);
                $('body').append(form);
                form.submit();
            }
        });


        $(document).on('click', '[data-role="delete"]', async function(e) {
            e.preventDefault();

            const manga = DATA_MANGA.find(o => o.id == $(this).attr('data-id'));

            if(!manga) {
                return $.toastShow('Không tìm thấy truyện', {
					type: 'error',
					timeout: 3000
				});	;
            }

            if(await comfirm_dialog('Xoá vĩnh viến truyện', 'Không thể hoàn tác. Bạn vẫn muốn tiếp tục xoá: <b>' + manga.name + '</b>?') == true) {
                const form = $(`
                <form method="post" style="display: none">
                    <?=$insertHiddenToken;?>
                    <input type="hidden" name="<?=adminPanelController::INPUT_ACTION;?>" value="<?=adminPanelController::ACTION_DELETE_FOREVER;?>">
                    <input type="hidden" name="<?=adminPanelController::INPUT_ID;?>" value="${manga.id}">
                </form>`);
                $('body').append(form);
                form.submit();
            }
        });

    });
</script>