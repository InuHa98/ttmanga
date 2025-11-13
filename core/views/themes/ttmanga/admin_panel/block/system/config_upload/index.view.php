    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <span>Cấu hình upload ảnh (<b><?=$count;?></b>)</span>
        <?php if($is_access_create): ?>
            <div class="action">
                <a class="btn btn--small btn--round" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_CONFIG_UPLOAD, 'action' => adminPanelController::ACTION_ADD]);?>"><i class="fas fa-plus"></i> Add</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if($config_upload_list): ?>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th></th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Note</th>
                        <th width="100%">Teams Used</th>
                    </tr>
                </thead>
                <tbody>
        <?php foreach($config_upload_list as $config_upload):
            $teams_used = json_decode('['.$config_upload['teams_used'].']', true);
        ?>
            <tr data-id="<?=$config_upload['id'];?>">
                <td>
                    <div class="drop-menu">
                        <div class="drop-menu__button">
                            <i class="fa fa-ellipsis-v"></i>
                        </div>
                        <ul class="drop-menu__content">
                            <li role="check-config">
                                <i class="fas fa-sync-alt"></i> Check status
                            </li>
                        <?php if($is_access_edit): ?>
                            <li>
                                <a href="<?=RouteMap::build_query([InterFaceRequest::ID => $config_upload['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_CONFIG_UPLOAD, 'action' => adminPanelController::ACTION_EDIT]);?>"><i class="fas fa-edit"></i> Edit</a>
                            </li>
                        <?php endif; ?>
                        <?php if($is_access_delete): ?>
                            <li class="border-top">
                                <a role="delete-config" href="<?=RouteMap::build_query([InterFaceRequest::ID => $config_upload['id']], 'admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_CONFIG_UPLOAD, 'action' => adminPanelController::ACTION_DELETE]);?>"><i class="fas fa-trash"></i> Delete</a>
                            </li>
                        <?php endif; ?>
                        </ul>
                    </div>
                </td>
                <td class="nowrap">
                    <i class="fas fa-hdd"></i> <?=_echo($config_upload['name']);?>
                </td>
                <td>
                    <span class="nowrap" role="txt-config-status"></span>
                </td>
                <td>
                    <?=_echo($config_upload['note'], true);?>
                </td>
                <td class="nowrap">
                    <?php if($teams_used): ?>
                        <i class="fas fa-layer-group"></i>
                        <ul class="list-teams">
                            <?php foreach($teams_used as $team): ?>
                                <li><?=_echo($team);?></li>
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
        <div class="alert alert--waring">Chưa có chức vụ nào.</div>
    <?php endif; ?>


<script type="text/javascript">
    $(document).ready(function() {

        const DATA_CONFIGS = <?=json_encode($config_upload_list);?>;

		const check_status = function(parent_tr){

			let config = DATA_CONFIGS.find(function(o){
				return o['id'] == parent_tr.attr('data-id');
			});

			if(!config || !parent_tr)
			{
				return;
			}

			let status = parent_tr.find('[role="txt-config-status"]');
			let btn_check = parent_tr.find('[role="check-config"]');

            status.removeClass('text-success').removeClass('text-danger').addClass('text-warning');
            status.html('<i class="fas fa-sync-alt"></i> Checking...');
			btn_check.removeClass("disabled").addClass("disabled");

			$.ajax({
                type: "POST",
                url: "<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_CONFIG_UPLOAD, 'action' => adminPanelController::ACTION_CHECK_CONFIG]);?>",
                data: {
                    <?=InterFaceRequest::ID;?>: config.id
                },
                dataType: 'json',
				cache: false,
				success: function(response)
				{
					if(response.code == 200)
					{
                        status.addClass('text-success');
						return status.html('<i class="fas fa-check-circle"></i> ' + response.message);
					}
                    status.addClass('text-danger');
					status.html('<i class="fas fa-exclamation-circle"></i> ' + response.message);
				},
				error: function(response)
				{
                    status.addClass('text-danger');
					status.html('<i class="fas fa-exclamation-circle"></i> ReTry');
				},
				complete: function(response){
                    status.removeClass('text-warning');
					btn_check.removeClass("disabled");
				}
			});
		};

		$('table').find('tr').each(function(i){
			check_status($(this));
		});

		$(document).on('click', '[role="check-config"]', function(){
			check_status($(this).parents('tr'));
		});

        $('[role="delete-config"]').on('click', async function(e) {
            e.preventDefault();

            if(await comfirm_dialog('Xoá bỏ cấu hình upload', 'Bạn thực sự muốn xoá bỏ mục này?') == true) {
                window.location.href = $(this).attr('href');
            }
        });
    });
</script>