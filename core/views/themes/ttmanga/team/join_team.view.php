<?php View::render_theme('layout.header', ['title' => $title]); ?>


<div class="section-sub-header">
	<div class="container">
        <span>Tham gia nhóm</span>
    </div>
</div>

<div class="container">
	<div class="row">
		<div class="col-xs-12 col-md-4 col-lg-3">
			<div class="box box--list">
				<div class="box__body">
					<a class="box__body-item active" href="<?=RouteMap::get('my_team');?>">
						<span class="item-icon">
							<i class="fas fa-layer-group"></i>
						</span>
						<div>
							<span class="item-title">Tham gia nhóm dịch</span>
						</div>
					</a>

					<a class="box__body-item" href="<?=RouteMap::get('my_team', ['block' => teamController::BLOCK_CREATE_TEAM]);?>">
						<span class="item-icon">
							<i class="fas fa-layer-plus"></i>
						</span>
						<div>
							<span class="item-title">Đăng ký nhóm dịch</span>
						</div>
					</a>
				</div>
			</div>
		</div>

		<div class="col-xs-12 col-md-8 col-lg-9">

		<?php if($request_join_team): ?>
			<div class="alert alert--warning">Đang chờ trưởng nhóm chấp thuận yêu cầu tham gia nhóm.</div>
		<?php elseif($request_create_team): ?>
			<div class="alert alert--warning">Đang chờ quản trị viên chấp thuận yêu cầu tạo nhóm.</div>
		<?php else: ?>
			<div class="alert alert--warning">Bạn chưa tham gia nhóm dịch nào. Vui lòng tham gia hoặc <a href="<?=RouteMap::get('my_team', ['block' => teamController::BLOCK_CREATE_TEAM]);?>" class="btn btn--small"><i class="fas fa-plus"></i> đăng ký mới</a> một nhóm dịch để có thể upload truyện.</div>
		<?php endif; ?>

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


			<div class="d-flex justify-content-end">
				<form method="GET" class="action">
					<div class="form-control">
						<span class="form-control-feedback"><i class="fas fa-search"></i></span>
						<input type="text" class="form-input" name="<?=InterFaceRequest::KEYWORD;?>" placeholder="Tìm kiếm tên nhóm" value="<?=_echo($keyword);?>"/>
					</div>
				</form>
			</div>

			<?php if($team_list): ?>


				<div class="table-scroll">
					<table>
						<thead>
							<tr>
								<th></th>
								<th>Tên nhóm</th>
								<th>Trưởng nhóm</th>
								<th width="30%" class="align-center">Thành viên</th>
								<th width="30%" class="align-center">Manga</th>
								<th width="30%" class="align-center">Chapter</th>
								<th></th>
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

						<td>
							<?=render_avatar($team, Team::get_avatar($team));?>
						</td>
						<td class="nowrap">
							<a target="_blank" href="<?=RouteMap::get('team', ['name' => $team['name']]);?>">
								<strong class="btn btn--small btn--round btn-outline-info  btn--small"><?=_echo($team['name']);?></strong>
							</a>
						</td>
						<td>
							<a target="_blank" class="user-infomation" href="<?=RouteMap::get('profile', ['id' => $user_own['id']]);?>">
								<?=render_avatar($user_own, null, true, true);?>
							</a>
						</td>
						<td class="nowrap align-center"><i class="fad fa-users"></i> <?=$team['total_members'];?></td>
						<td class="nowrap align-center"><?=$team['total_mangas'];?></td>
						<td class="nowrap align-center"><?=$team['total_chapters'];?></td>
						<td class="nowrap">
						<?php if(!$request_join_team): ?>
							<span class="btn btn--small btn--info" data-id="<?=$team['id'];?>" role="join-team">Xin vào nhóm</span>
						<?php endif; ?>
						<?php if($request_join_team && $request_join_team['team_id'] == $team['id']): ?>
							<span class="btn btn--small btn-outline-warning">Đang chờ phê duyệt</span>
							<span class="btn btn--small btn--danger" data-id="<?=$request_join_team['id'];?>" role="cancel-join-team">Huỷ</span>
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
				<div class="alert alert--warning">Không có nhóm dịch nào.</div>
			<?php endif; ?>
		</div>
	</div>
</div>


<script type="text/javascript">
    $(document).ready(function() {
        
        $(document).on('click', '[role="join-team"]', function(e) {
            e.preventDefault();

			const id = $(this).attr('data-id');
			if(!id) {
                return $.toastShow('Không tìm thấy id nhóm dịch', {
					type: 'error',
					timeout: 3000
				});	;
			}

            const form = $(`
            <form method="post" class="margin-b-4">
                <?=Security::insertHiddenToken();?>
                <input type="hidden" name="<?=teamController::INPUT_ACTION;?>" value="<?=teamController::ACTION_JOIN;?>">
                <input type="hidden" name="<?=InterFaceRequest::ID;?>" value="${id}">
                <div class="dialog-label">Ghi chú:</div>
                <div class="form-group">
                    <div class="form-control">
                        <textarea class="form-textarea" name="<?=teamController::INPUT_NOTE;?>" placeholder="Có thể bỏ trống"></textarea>
                    </div>
                </div>
            </form>`);


			$.dialogShow({
				title: 'Yêu cầu tham gia nhóm dịch',
				content: form,
				button: {
					confirm: 'Gửi yêu cầu',
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

		$(document).on('click', '[role="cancel-join-team"]', function(e) {
            e.preventDefault();

			const id = $(this).attr('data-id');
			if(!id) {
                return $.toastShow('Không tìm thấy id nhóm dịch', {
					type: 'error',
					timeout: 3000
				});	;
			}

            const form = $(`
            <form method="post" class="margin-b-4">
                <?=Security::insertHiddenToken();?>
                <input type="hidden" name="<?=teamController::INPUT_ACTION;?>" value="<?=teamController::ACTION_CANCEL_JOIN;?>">
                <input type="hidden" name="<?=InterFaceRequest::ID;?>" value="${id}">
                <div class="text-danger">Bạn thực sự muốn huỷ yêu cầu tham gia nhóm này?</div>
            </form>`);


			$.dialogShow({
				title: 'Huỷ yêu cầu tham gia nhóm',
				content: form,
				button: {
					confirm: 'Huỷ yêu cầu',
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

    });
</script>

<?php View::render_theme('layout.footer'); ?>