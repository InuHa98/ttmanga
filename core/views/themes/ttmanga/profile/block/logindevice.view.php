<div class="box__body">
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



    <?php if($auth_sessions) { ?>
        <div class="flex-panel">
            <div class="flex-panel__box">
                <span>Có <strong><?=$count;?></strong> thiết bị hiện đang đăng nhập</span>
            </div>
            <div class="flex-panel__box flex--right">
                <span class="btn btn--small btn--round" id="logout_all">
                    <i class="fas fa-power-off"></i> Đăng xuất tất cả
                </span>
            </div>
        </div>
    <?php } ?>

    <div class="table-scroll">
        <table class="auth-session">
            <tbody>
                <tr align="left">
                    <th></th>
                    <th width="100%">Thiết bị đã đăng nhập</th>
                    <th></th>
                </tr>
                <?php

                if($auth_sessions)
                {
                    foreach ($auth_sessions as $session) {

                        switch($session['user_agent']['device']) {
                            case 'Desktop':
                                $icon = '<i class="fas fa-desktop"></i>';
                                break;
                            case 'Tablet':
                                $icon = '<i class="fas fa-tablet-alt"></i>';
                                break;
                            case 'Mobile':
                                $icon = '<i class="fas fa-mobile-alt"></i>';
                                break;
                            default:
                                $icon = '<i class="far fa-question-circle"></i>';
                                break;
                        }

                        echo '<tr>';

                        echo '
                            <td class="auth-session__icon">
                                <span class="auth-session__icon--'.$session['user_agent']['device'].'">'.$icon.'</span>
                            </td>';

                        echo '
                            <td class="auth-session__device">
                                <div class="auth-session__device-browser">'.$session['user_agent']['browser'].' '.$session['user_agent']['browser_version'].' · '.$session['ip'].'</div>
                                <div class="auth-session__device-os">'.$session['user_agent']['os_platform'].' · '.($session['auth_session'] == Auth::current_auth_session() ? '<span class="current-device">Thiết bị này</span>' : _time($session['time'])).'</div>
                            </td>';

                        echo '<td>';
                        echo '
                            <div class="drop-menu">
                                <div class="drop-menu__button">
                                    <i class="fa fa-ellipsis-v"></i>
                                </div>
                                <ul class="drop-menu__content">
                                    <li class="border-top" role="logout-device" data-auth-session="'._echo($session['auth_session']).'">
                                        <i class="fas fa-sign-out-alt"></i> Đăng xuất
                                    </li>
                                </ul>
                            </div>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '<div class="pagination">'.html_pagination($pagination).'</div>';
                }
                else
                {
                    echo '<tr><td colspan="3" class="table__empty">Không có thiết bị đang đăng nhập nào!!!</td></tr>';
                }

                ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <?=html_pagination($pagination);?>
    </div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		$(document).on('click', '[role=logout-device]', function() {
			var idForm = 'dialogForm';

			$.dialogShow({
				title: 'Đăng xuất thiết bị',
				content: '\
					<form id="'+idForm+'" method="post">\
                        <?=Security::insertHiddenToken();?>\
                        <?=profileController::insertHiddenAction(Interface_controller::ACTION_LOGOUT_DEVICE);?>\
						<input type="hidden" name="<?=Interface_controller::INPUT_FORM_AUTH_SESSION;?>" value="'+$(this).data('auth-session')+'">\
						<div class="dialog-message">Thiết bị này sẽ bị đăng xuất?</div>\
					</form>',
				button: {
					confirm: 'Tiếp tục',
					cancel: 'Huỷ'
				},
				onBeforeConfirm: function(){
					$('#'+idForm).submit();
				}
			});
		});

		$(document).on('click', '#logout_all', function() {
			var idForm = 'dialogForm';

			$.dialogShow({
				title: 'Đăng xuất tất cả thiết bị',
				content: '\
					<form id="'+idForm+'" method="post">\
                        <?=Security::insertHiddenToken();?>\
						<?=profileController::insertHiddenAction(Interface_controller::ACTION_LOGOUT_ALL);?>\
						<div class="dialog-message">Mọi thiết bị đang đăng nhập sẽ bị đăng xuất?</div>\
					</form>',
				button: {
					confirm: 'Tiếp tục',
					cancel: 'Huỷ'
				},
				onBeforeConfirm: function(){
					$('#'+idForm).submit();
				}
			});
		});
	});
</script>