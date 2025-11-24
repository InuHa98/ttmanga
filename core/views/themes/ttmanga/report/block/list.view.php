
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-2">
    <div>Báo lỗi đã gửi (<b><?=number_format($count, 0, ',', '.');?></b>)</div>
<?php if (Auth::$isLogin): ?>
    <span class="form-check">
        <input type="checkbox" id="only-report-pending" <?=($only_show_report_pending == 'true' ? 'checked' : null);?>>
        <label for="only-report-pending">Chỉ hiển thị báo lỗi đang chờ xử lý</label>
    </span>
<?php endif; ?>
</div>

<?php if($lst_report): ?>
<div class="table-scroll">
    <table class="list-manga">
        <thead>
            <tr>
                <th></th>
                <th>Truyện</th>
                <th>Lý do</th>
                <th width="50%">Nội dung</th>
                <th class="align-center">Trạng thái</th>
                <th>Người xử lý</th>
                <th>Ngày báo lỗi</th>
            </tr>
        </thead>
        <tbody>
<?php foreach($lst_report as $report):
    $user_update = [
        'id' => $report['user_update'],
        'name' => $report['user_update_name'],
        'username' => $report['user_update_username'],
        'avatar' => $report['user_update_avatar'],
        'user_ban' => $report['user_update_ban_id'],
        'role_color' => $report['user_update_role_color']
    ];
?>
    <tr>
        <td>
        <?php if (!empty($report['manga_image'])): ?>
            <img class="cover-manga" src="<?=_echo($report['manga_image']);?>" />
        <?php endif; ?>
        </td>
        <td class="nowrap">
            <div class="d-flex flex-column">
                <a target="_blank" href="<?=RouteMap::get('manga', ['id' => $report['manga_id']]);?>">
                    <strong><?=_echo($report['manga_name']);?></strong>
                </a>
                <a class="name" target="_blank" href="<?=RouteMap::get('chapter', ['id_manga' => $report['manga_id'], 'id_chapter' => $report['chapter_id']]);?>">
                    <?=_echo($report['chapter_name']);?>
                </a>
            </div>
        </td>
        <td><?=Report::getTypeName($report['type']);?></td>
        <td>
            <?=_echo($report['note']);?>
        </td>
        <td class="align-center"><?=Report::getStatusName($report['status']);?></td>
        <td>
        <?php if (!empty($user_update['id'])): ?>
            <a target="_blank" class="user-infomation" href="<?=RouteMap::get('profile', ['id' => $user_update['id']]);?>">
                <?=render_avatar($user_update, null, true, true);?>
            </a>
        <?php endif; ?>
        </td>
        <td class="nowrap"><?=_time($report['created_at']);?></td>
    </tr>
<?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="pagination">
    <?=html_pagination($pagination);?>
</div>

<?php else: ?>
<div class="alert alert--warning">Chưa có báo lỗi nào.</div>
<?php endif; ?>

<script type="text/javascript">
    $(document).ready(() => {
        $('#only-report-pending').on('change', function() {
            const value = $(this).is(':checked') || false;
            document.cookie = "<?=reportController::COOKIE_ONLY_SHOW_REPORT_PENDING;?>=" + value + "; expires=Thu, 2 Aug <?=(date('Y') + 10);?> 20:47:11 UTC;path=/";
            window.location.reload();
        });
    });
</script>