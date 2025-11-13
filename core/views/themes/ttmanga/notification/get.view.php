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
						<span class="count-new-item"><?=$_count_notification;?></span>
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
							<span class="count-new-item"><?=$_count_bookmark;?></span>
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
			<div class="notification-nav">
				<a class="notification-nav__back" href="<?=$referrer;?>">
					<i class="fa fa-arrow-left"></i> Trở lại
				</a>
				<button class="notification-nav__delete">Xoá thông báo</button>
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
			<div class="notifications-get">
				<div class="notifications-get__info">
					<div class="notifications-get__info-avatar">
						<?=render_avatar($user_from);?>
					</div>
					<div class="notifications-get__info-user">
						<a class="username" target="_blank" href="<?=RouteMap::get('profile', ['id' => $user_from['id']]);?>"><?=ucwords(User::get_display_name($user_from));?></a>
						<div class="time"><?=_time($notification['created_at']);?></div>
					</div>
				</div>
				<div class="notifications-get__content"><?=Notification::renderHTML($notification);?></div>
			</div>

		</div>
	</div>
</div>

<form method="POST" id="form-action">
	<?=$insertHiddenToken;?>
	<input id="_action" type="hidden" name="<?=notificationController::NAME_FORM_ACTION;?>" value="" />
</form>

<script type="text/javascript" src="<?=APP_URL;?>/assets/js/form-validator.js?v=<?=$_version;?>"></script>

<script type="text/javascript">

	function comfirm_dialog(title, text)
	{
		return new Promise(function(resolve, reject) {
			$.dialogShow({
				title: title,
				content: '<div class="dialog-message">'+text+'</div>',
				button: {
					confirm: 'Continue',
					cancel: 'Cancel'
				},
				bgHide: false,
				onConfirm: function(){
					resolve(true);
				},
				onCancel: function(){
					resolve(false);
				}
			});
		});
	}

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