
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-2">
    <div>Báo lỗi đã nhận (<b><?=number_format($count, 0, ',', '.');?></b>)</div>
    <span class="form-check">
        <input type="checkbox" id="only-report-pending" <?=($only_show_report_pending == 'true' ? 'checked' : null);?>>
        <label for="only-report-pending">Chỉ hiển thị báo lỗi đang chờ xử lý</label>
    </span>
</div>

<?php if($lst_report): ?>
<div class="table-scroll">
    <table class="list-manga">
        <thead>
            <tr>
                <th></th>
                <th></th>
                <th>Truyện</th>
                <th>Lý do</th>
                <th width="50%">Nội dung</th>
                <th class="align-center">Trạng thái</th>
                <th>Người báo lỗi</th>
                <th>Ngày báo lỗi</th>
            </tr>
        </thead>
        <tbody>
<?php foreach($lst_report as $report):

    $user_report = [
        'id' => $report['user_id'],
        'name' => $report['user_report_name'],
        'username' => $report['user_report_username'],
        'avatar' => $report['user_report_avatar'],
        'user_ban' => $report['user_report_ban_id'],
        'role_color' => $report['user_report_role_color']
    ];
?>
    <tr>
        <td>
            <div class="drop-menu">
                <div class="drop-menu__button">
                    <i class="fa fa-ellipsis-v"></i>
                </div>
                <ul class="drop-menu__content" data-id="<?=$report['id'];?>">
                <?php if ($report['status'] == Report::STATUS_PENDING): ?>
                    <li role="make-success" class="text-success">
                        <i class="fas fa-check"></i> Đánh dấu đã xử lý
                    </li>
                    <li role="make-reject" class="text-danger">
                        <i class="fas fa-times"></i> Đánh dấu không phải lỗi
                    </li>
                <?php else: ?>
                    <li role="make-pending" class="text-warning">
                        <i class="fas fa-spinner"></i> Đánh dấu đang chờ xử lý
                    </li>
                <?php endif; ?>
                </ul>
            </div>
        </td>
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
        <?php if (!empty($user_report['id'])): ?>
            <a target="_blank" class="user-infomation" href="<?=RouteMap::get('profile', ['id' => $user_report['id']]);?>">
                <?=render_avatar($user_report, null, true, true);?>
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

        const DATA_REPORT = <?=json_encode($lst_report);?>;

        $(document).on('click', '[role="make-success"]', function(e) {
            e.preventDefault();

            const report = DATA_REPORT.find(o => o.id == $(this).parent().attr('data-id'));
            if(!report) {
                return $.toastShow('Không tìm thấy báo lỗi', {
					type: 'error',
					timeout: 3000
				});	;
            }

            const form = $(`
            <form method="post" class="margin-b-4">
                <?=$insertHiddenToken;?>
                <input type="hidden" name="<?=teamController::INPUT_ACTION;?>" value="<?=teamController::ACTION_MAKE_SUCCESS;?>">
                <input type="hidden" name="<?=teamController::INPUT_ID;?>" value="${report.id}">
                <div class="dialog-message">Đánh dấu là đã xử lý</div>
            </form>`);


			$.dialogShow({
				title: 'Thay đổi trạng thái báo lỗi',
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

        $(document).on('click', '[role="make-reject"]', function(e) {
            e.preventDefault();

            const report = DATA_REPORT.find(o => o.id == $(this).parent().attr('data-id'));
            if(!report) {
                return $.toastShow('Không tìm thấy báo lỗi', {
					type: 'error',
					timeout: 3000
				});	;
            }

            const form = $(`
            <form method="post" class="margin-b-4">
                <?=$insertHiddenToken;?>
                <input type="hidden" name="<?=teamController::INPUT_ACTION;?>" value="<?=teamController::ACTION_MAKE_REJECT;?>">
                <input type="hidden" name="<?=teamController::INPUT_ID;?>" value="${report.id}">
                <div class="dialog-message">Đánh dấu đây không phải là lỗi</div>
            </form>`);


			$.dialogShow({
				title: 'Thay đổi trạng thái báo lỗi',
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

        $(document).on('click', '[role="make-pending"]', function(e) {
            e.preventDefault();

            const report = DATA_REPORT.find(o => o.id == $(this).parent().attr('data-id'));
            if(!report) {
                return $.toastShow('Không tìm thấy báo lỗi', {
					type: 'error',
					timeout: 3000
				});	;
            }

            const form = $(`
            <form method="post" class="margin-b-4">
                <?=$insertHiddenToken;?>
                <input type="hidden" name="<?=teamController::INPUT_ACTION;?>" value="<?=teamController::ACTION_MAKE_PENDING;?>">
                <input type="hidden" name="<?=teamController::INPUT_ID;?>" value="${report.id}">
                <div class="dialog-message">Đánh dấu đang chờ xử lý lỗi</div>
            </form>`);

			$.dialogShow({
				title: 'Thay đổi trạng thái báo lỗi',
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

        $('#only-report-pending').on('change', function() {
            const value = $(this).is(':checked') || false;
            document.cookie = "<?=teamController::COOKIE_ONLY_SHOW_REPORT_PENDING;?>=" + value + "; expires=Thu, 2 Aug <?=(date('Y') + 10);?> 20:47:11 UTC;path=/";
            window.location.reload();
        });
    });
</script>