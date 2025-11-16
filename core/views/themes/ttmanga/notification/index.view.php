<?php View::render_theme('layout.header', ['title' => $title]); ?>

<?php

echo themeController::load_css('css/notification.css');

$insertHiddenToken = Security::insertHiddenToken();
?>

<div class="section-sub-header">
	<div class="container">
        <span>Thông báo</span>
    </div>
</div>

<div class="container">
	<div class="row">
		<div class="col-xs-12 col-md-4 col-lg-3">
			<div class="box box--list">
				<div class="box__body">
                    <a class="box__body-item active" href="<?=RouteMap::get('notification');?>">
						<span class="item-icon">
							<i class="fas fa-bell"></i>
						</span>
						<div>
							<span class="item-title">Thông báo</span>
						</div>
					<?php if($_count_notification > 0) : ?>
						<span class="count-new-item"><?=number_format($_count_notification, 0, ',', '.');?></span>
					<?php endif; ?>
					</a>
					<a class="box__body-item" href="<?=RouteMap::get('bookmark');?>">
						<span class="item-icon">
							<i class="fas fa-bookmark"></i>
						</span>
						<div>
							<span class="item-title">Truyện theo dõi</span>
						</div>
						<?php if($_count_bookmark > 0) : ?>
							<span class="count-new-item"><?=number_format($_count_bookmark, 0, ',', '.');?></span>
						<?php endif; ?>
					</a>
					<a class="box__body-item" href="<?=RouteMap::get('history');?>">
						<span class="item-icon">
							<i class="fas fa-history"></i>
						</span>
						<div>
							<span class="item-title">Lịch sử đọc truyện</span>
						</div>
					</a>
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-md-8 col-lg-9">

		<div class="flex-panel">
			<div class="flex-panel__box">
				<span>Tất cả thông báo (<strong><?=number_format($count, 0, ',', '.');?></strong>)</span>
			</div>
			<div class="flex-panel__box flex--right">
				<div class="drop-menu">
					<span class="btn btn--gray btn--small btn--no-with">
						<i class="fas fa-ellipsis-v"></i>
					</span>
					<ul class="drop-menu__content">
					<?php if($type == null || $type == notificationController::TYPE_UNSEEN): ?>
						<li role="make-seen-all">
							<i class="fas fa-check-double"></i> Đánh dấu đã xem tất cả
						</li>
					<?php endif; ?>
					<?php if($type == null || $type == notificationController::TYPE_SEEN): ?>
						<li role="make-unseen-all">
							<i class="fas fa-undo"></i> Đánh dấu tất cả chưa xem
						</li>
					<?php endif; ?>
						<li role="delete-all" class="border-bottom text-danger">
							<i class="fas fa-trash"></i> Xoá tất cả thông báo
						</li>
					<?php if($type == null || $type == notificationController::TYPE_UNSEEN): ?>
						<li role="make-seen-selected" class="disabled">
							Đánh dấu đã xem <span role="multiple_selected_count">(0)</span>
						</li>
					<?php endif; ?>
					<?php if($type == null || $type == notificationController::TYPE_SEEN): ?>
						<li role="make-unseen-selected" class="disabled">
							Đánh dấu chưa xem <span role="multiple_selected_count">(0)</span>
						</li>
					<?php endif; ?>
						<li role="delete-selected" class="disabled text-danger">
							Xoá mục đã chọn <span role="multiple_selected_count">(0)</span>
						</li>
					</ul>
				</div>
			</div>
		</div>

        
		<div class="notification-type">
			<div class="notification-status">
				<div class="notification-select">
					<span class="form-check">
						<input type="checkbox" class="notification-select" id="multiple_selected_all">
						<label for="multiple_selected_all"></label>
					</span>
				</div>
				<a class="notification-status__button <?=($type == null ? 'active' : null);?>" href="<?=RouteMap::get('notification');?>">Tất cả</a>
				<a class="notification-status__button <?=($type == notificationController::TYPE_UNSEEN ? 'active' : null);?>" href="<?=RouteMap::get('notification', ['id' => notificationController::TYPE_UNSEEN]);?>">Chưa xem</a>
				<a class="notification-status__button <?=($type == notificationController::TYPE_SEEN ? 'active' : null);?>" href="<?=RouteMap::get('notification', ['id' => notificationController::TYPE_SEEN]);?>">Đã xem</a>
			</div>
		</div>
        

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

        <?php if($notification_data): ?>
			<div class="notifications-list">
			<?php foreach($notification_data as $notifi): ?>
				<?php

					$user = [
						'id' => $notifi['user_from_id'],
						'name' => $notifi['user_from_name'],
						'username' => $notifi['user_from_username'],
						'avatar' => $notifi['user_from_avatar']
					];

					$isSeen = $notifi['seen'] == Notification::SEEN ? true : false;

				?>
				<a class="notifications-list__item <?=($isSeen == true ? null : 'unseen');?>" href="<?=RouteMap::get('notification', ['id' => $notifi['id']]);?>">
					<div class="form-check">
						<input type="checkbox" role="multiple_selected" name="id[]" value="<?=$notifi['id'];?>" id="label_<?=$notifi['id'];?>">
						<label for="label_<?=$notifi['id'];?>" class="notification-select"></label>
					</div>
					<div class="drop-menu">
                        <button class="drop-menu__button">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
						<ul class="drop-menu__content">
							<?=($notifi['seen'] == Notification::UNSEEN ? '
								<li role="make-seen">
									<form method="POST">
										'.$insertHiddenToken.'
										'.notificationController::insertHiddenAction(notificationController::ACTION_MAKE_SEEN).'
										'.notificationController::insertHiddenID($notifi['id']).'
									</form>
									<i class="fas fa-check"></i> Đánh dấu đã xem
								</li>
							' : '
								<li role="make-unseen">
									<form method="POST">
										'.$insertHiddenToken.'
										'.notificationController::insertHiddenAction(notificationController::ACTION_MAKE_UNSEEN).'
										'.notificationController::insertHiddenID($notifi['id']).'
									</form>
									<i class="fas fa-undo"></i> Đánh dấu chưa xem
								</li>
							');?>
							<li role="delete" class="text-danger">
								<form method="POST">
									<?=$insertHiddenToken;?>
									<?=notificationController::insertHiddenAction(notificationController::ACTION_DELETE);?>
									<?=notificationController::insertHiddenID($notifi['id']);?>
								</form>
								<i class="fa fa-trash"></i> Xoá thông báo
							</li>
						</ul>
					</div>

					<div class="notifications-list__item-avatar">
						<?=render_avatar($user);?>
					</div>
					<div class="notifications-list__item-preview">
						<div class="text"><?=Notification::renderHTML($notifi, true);?></div>
						<div class="time"><?=_time($notifi['created_at']);?></div>
					</div>
					<div class="notifications-list__item-status"></div>
				</a>
			<?php endforeach; ?>

			</div>

			<div class="pagination">
				<?=html_pagination($pagination);?>
			</div>

		<?php else: ?>
			<div class="alert alert--warning">Không có thông báo nào.</div>
		<?php endif; ?>
		</div>
	</div>
</div>

<form method="POST" id="form-action">
	<?=$insertHiddenToken;?>
	<input id="_action" type="hidden" name="<?=notificationController::NAME_FORM_ACTION;?>" value="" />
</form>

<script type="text/javascript" src="<?=APP_URL;?>/assets/script/form-validator.js?v=<?=$_version;?>"></script>

<script type="text/javascript">



	function request_action(type)
	{
		var form = $('#form-action');
		var action = $('#_action');
		action.val(type);
		$(role_multiple_selected+":checked").each(function(){
			form.append('<input type="hidden" name="<?=notificationController::INPUT_ID;?>[]" value="'+$(this).val()+'">');
		});
		form.submit();
	}

	var role_multiple_selected = '[role=multiple_selected]';

	$(document).ready(function() {

		$('.drop-menu').on('click', function(e) {
			e.preventDefault();
		});

		multiple_selected({
			role_select_all: "#multiple_selected_all",
			role_select: role_multiple_selected,
			onSelected: function(total_selected, config){
				$("[role=multiple_selected_count]").html('('+total_selected+')');
				$("[role=multiple_selected_count]").parents('li').removeClass("disabled");
			},
			onNoSelected: function(total_selected, config){
				$("[role=multiple_selected_count]").html('(0)');
				$("[role=multiple_selected_count]").parents('li').removeClass("disabled").addClass("disabled");
			}
		});

		role_click('make-seen-all', function(self) {
            var form = $('#form-action');
			var action = $('#_action');
			action.val('<?=notificationController::ACTION_MAKE_SEEN;?>');
			form.submit();
        });

		role_click('make-unseen-all', function(self) {
            var form = $('#form-action');
			var action = $('#_action');
			action.val('<?=notificationController::ACTION_MAKE_UNSEEN;?>');
			form.submit();
        });

		role_click('delete-all', async function(self) {
			if(await comfirm_dialog('Xoá thông báo', 'Bạn thực sự muốn xoá tất cả thông báo?') !== true)
			{
				return;
			}

            var form = $('#form-action');
			var action = $('#_action');
			action.val('<?=notificationController::ACTION_DELETE;?>');
			form.submit();
        });

        role_click('make-seen', function(self) {
            self.find('form').submit();
        });

        role_click('make-unseen', function(self) {
            self.find('form').submit();
        });

        role_click('delete', async function(self) {
			if(await comfirm_dialog('Xoá thông báo', 'Bạn thực sự muốn xoá thông báo này?') !== true)
			{
				return;
			}
            self.find('form').submit();
        });


		role_click('make-seen-selected', function(self) {
			request_action('<?=notificationController::ACTION_MAKE_SEEN;?>');
        });

		role_click('make-unseen-selected', function(self) {
			request_action('<?=notificationController::ACTION_MAKE_UNSEEN;?>');
        });

		role_click('delete-selected', async function(self) {
			if(await comfirm_dialog('Xoá thông báo', 'Xoá mục đã chọn?') !== true)
			{
				return;
			}
			request_action('<?=notificationController::ACTION_DELETE;?>');
        });

	});
</script>
<?php View::render_theme('layout.footer'); ?>