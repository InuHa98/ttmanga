
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-2">
    <div>Truyện đã đăng (<b><?=number_format($count, 0, ',', '.');?></b>)</div>
    <a class="btn btn--small btn--round btn--info" href="<?=RouteMap::get('manga_management', ['action' => mangaManagementController::ACTION_NEW_MANGA]);?>"><i class="fas fa-plus"></i> Thêm truyện mới</a>
</div>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-2">
    <span class="form-check">
        <input type="checkbox" id="only-my-upload" <?=($only_show_my_uploader == 'true' ? 'checked' : null);?>>
        <label for="only-my-upload">Chỉ hiển thị truyện của tôi</label>
    </span>
    <form method="GET" class="d-flex justify-content-start align-items-center gap-2 flex-wrap">
        <div>
            <div class="input-group">
                <span class="form-control-feedback"><i class="fas fa-search"></i></span>
                <input type="text" class="form-input border-radius-left" name="<?=InterFaceRequest::KEYWORD;?>" placeholder="Tìm kiếm theo..." value="<?=_echo($keyword);?>"/>
                <div class="input-group-append">
                    <select class="js-custom-select" name="<?=InterFaceRequest::TYPE;?>" onchange="this.form.submit()">
                        <option value="<?=teamController::INPUT_NAME;?>">Tên truyện</option>
                    <?php if ($only_show_my_uploader != 'true'): ?>
                        <option <?=($type == teamController::INPUT_UPLOADER ? 'selected' : null);?> value="<?=teamController::INPUT_UPLOADER;?>">Người đăng</option>
                    <?php endif; ?>
                        <option <?=($type == teamController::INPUT_TEAM ? 'selected' : null);?> value="<?=teamController::INPUT_TEAM;?>">Nhóm chia sẻ</option>
                    </select>               
                </div>
            </div>
        </div>
        <div>
            <select class="js-custom-select" name="<?=InterFaceRequest::STATUS;?>" onchange="this.form.submit()" data-max-width="200px">
                <option value="<?=Manga::STATUS_ALL;?>">Tất cả trạng thái</option>
                <option <?=($status == Manga::STATUS_ONGOING ? 'selected' : null);?> value="<?=Manga::STATUS_ONGOING;?>">Đang tiến hành</option>
                <option <?=($status == Manga::STATUS_COMPLETE ? 'selected' : null);?> value="<?=Manga::STATUS_COMPLETE;?>">Đã hoàn thành</option>
                <option <?=($status == Manga::STATUS_DROP ? 'selected' : null);?> value="<?=Manga::STATUS_DROP;?>">Tạm ngưng</option>
            </select>
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
                <th>Trạng thái</th>
                <th>Đăng bởi</th>
                <th>Nhóm cộng sự</th>
            </tr>
        </thead>
        <tbody>
<?php foreach($lst_manga as $manga):
    $uploader = [
        'id' => $manga['user_upload'],
        'name' => $manga['uploader_name'],
        'username' => $manga['uploader_username'],
        'avatar' => $manga['uploader_avatar'],
        'user_ban' => $manga['uploader_user_ban'],
        'role_color' => $manga['uploader_role_color']
    ];
    $teamPartner = TeamManga::select(['team_id'])::list(['manga_id' => $manga['id'], 'team_id[!]' => teamController::$team['id']]);
    $team = Team::select(['name'])::list(['id' => $teamPartner ? array_column($teamPartner, 'team_id') : 0]);
    $teams = $team ? array_column($team, 'name') : [];
?>
    <tr>
        <td>
            <a target="_blank" href="<?=RouteMap::get('manga', ['id' => $manga['id']]);?>"><i class="fas fa-eye"></i></a>
        </td>
        <td>
            <img class="cover-manga" src="<?=_echo($manga['image']);?>" />
        </td>
        <td class="name-manga">
            <a href="<?=RouteMap::get('manga_management', ['id' => $manga['id'], 'action' => mangaManagementController::ACTION_DETAIL]);?>">
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
        <td class="nowrap"><?=Manga::get_status_name($manga);?></td>
        <td class="nowrap">
            <a target="_blank" class="user-infomation" href="<?=RouteMap::get('profile', ['id' => $uploader['id']]);?>">
                <?=render_avatar($uploader, null, true, true);?>
            </a>
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
    $(document).ready(() => {
        $('#only-my-upload').on('change', function() {
            const value = $(this).is(':checked') || false;
            document.cookie = "<?=teamController::COOKIE_ONLY_SHOW_MY_UPLOADER;?>=" + value + "; expires=Thu, 2 Aug <?=(date('Y') + 10);?> 20:47:11 UTC;path=/";
            window.location.reload();
        });
    });
</script>