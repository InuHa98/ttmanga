<?php View::render_theme('layout.header', ['title' => $title]); ?>

<?php


echo themeController::load_css('css/bookmark.css');

?>

<div class="section-sub-header">
	<div class="container">
        <span>Bookmark</span>
    </div>
</div>

<div class="container">
	<div class="row">
		<div class="col-xs-12 col-md-4 col-lg-3">
			<div class="box box--list">
				<div class="box__body">
                    <a class="box__body-item" href="<?=RouteMap::get('notification');?>">
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
					<a class="box__body-item active" href="<?=RouteMap::get('bookmark');?>">
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

			<div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
				<div class="bookmark-type">
					<a class="bookmark-type__button <?=($type == null ? 'active' : null);?>" href="<?=RouteMap::get('bookmark');?>">Tất cả</a>
					<a class="bookmark-type__button <?=($type == bookmarkController::TYPE_NEW_CHAPTER ? 'active' : null);?>" href="<?=RouteMap::get('bookmark', ['type' => bookmarkController::TYPE_NEW_CHAPTER]);?>">Có chương mới</a>
				</div>
				<div class="drop-menu">
					<span class="btn btn--gray btn--round btn--no-with px-4">
						Tuỳ chọn <i class="fas fa-ellipsis-v"></i>
					</span>
					<ul class="drop-menu__content">
						<li role="make-read-all">
							<i class="fas fa-check-double text-success"></i> Đánh dấu đã đọc tất cả
						</li>
						<li role="make-unread-all">
							<i class="fas fa-exclamation-triangle text-warning"></i> Đánh dấu chưa đọc tất cả
						</li>
					</ul>
				</div>
			</div>
			<div class="flex-panel">
				<div class="flex-panel__box">
					<span>Có <strong id="total_manga"><?=number_format($count, 0, ',', '.');?></strong> truyện đang theo dõi.</span>
				</div>
				<div class="flex-panel__box flex--right">
					<div class="btn-group view-mode-change">
						<span role="change-view-mode" class="btn btn--small <?=($view_mode != 'table' ? 'active' : null);?>" data-mode="grid">
							<i class="fas fa-th"></i>
						</span>
						<span role="change-view-mode" class="btn btn--small <?=($view_mode == 'table' ? 'active' : null);?>" data-mode="table">
							<i class="fas fa-list"></i>
						</span>
					</div>
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


	<?php if(UserPermission::has('user_follow')): ?>
        <?php if($manga_items): ?>
			<div class="manga-list-view">    
				<ul class="list-view <?=($view_mode == 'table' ? 'mode--table' : null);?>">
				<?php foreach($manga_items as $manga): ?>
				<?php
					$is_unread = $manga['is_read'] == Bookmark::TYPE_UNREAD;
					$url_manga = RouteMap::get('manga', ['id' => $manga['id']]);
					$url_chapter = RouteMap::get('chapter', ['id_manga' => $manga['id'], 'id_chapter' => $manga['id_last_chapter']]);
					$teams = Manga::get_team_name($manga, true);
					$genres = array_intersect_key($_genres, array_flip(array_filter(explode(',', $manga['genres_id'] ?? ''))));
				?>
					<li class="list-view__item tooltip-data <?=($is_unread ? 'is-unread' : 'is-read');?>">
						<a class="list-view__item-image tooltip-target" href="<?=$url_manga;?>">
							<img data-tooltip="image" src="<?=_echo($manga['image']);?>">
							<div class="progress-bar"></div>
							<div class="action-wrapper" data-id="<?=$manga['id'];?>">
								<span class="unread" role="make-read">
									<i class="fas fa-exclamation-triangle"></i> Chưa đọc
								</span>
								<span class="read" role="make-unread">
									<i class="fas fa-check"></i> Đã đọc
								</span>
								<span class="remove" role="remove-manga">xoá</span>
							</div>
						</a>
						<div class="list-view__item-info">
							<a class="info-name" data-tooltip="title" href="<?=$url_manga;?>"><?=_echo($manga['name']);?></a>
							<div class="info__group">
								<div class="info-genres">
									<?php if($genres): foreach($genres as $id => $name): ?>
                            			<a href="<?=RouteMap::build_query([mangaController::PARAM_GENRES => $id], 'manga');?>"><?=_echo($name);?></a>
									<?php endforeach; else: ?>
										<span class="empty">Không rõ</span>
									<?php endif; ?>
								</div>
								<div class="info-status" data-status="<?=$manga['status'];?>">
									<span class="info-label">Tình trạng:</span>
									<?=Manga::get_status_name($manga);?>
								</div>
								<div class="info-team">
									<span class="info-label">Nhóm dịch:</span>
									<?php if($teams): foreach($teams as $val): ?>
										<a href="<?=RouteMap::get('team', ['name' => $val]);?>"><?=_echo($val);?></a>
									<?php endforeach; else: ?>
										<span class="empty">Không rõ</span>
									<?php endif; ?>
								</div>
								<div class="info-chapter">
									<span class="info-label">Mới nhất:</span>
									<?=($manga['id_last_chapter'] ? '<a href="'.$url_chapter.'">'._echo($manga['name_last_chapter']).'</a>' : '<span class="empty">Chưa có!!!</span>');?>
								</div>
								<div class="info-action">
									<div class="progress-bar"></div>
									<div class="bookmark-action" data-id="<?=$manga['id'];?>">
										<div class="unread" role="make-read">
											<i class="fas fa-exclamation-triangle"></i> Chưa đọc
										</div>
										<div class="read" role="make-unread">
											<i class="fas fa-check"></i> Đã đọc
										</div>
										<div class="remove" role="remove-manga">
											<i class="fas fa-times"></i> Xoá
										</div>
									</div>
								</div>
								<div class="info-desc" data-tooltip="desc"><?=_echo($manga['text'], true, false);?></div>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
				</ul>
			</div>

			<div class="pagination">
				<?=html_pagination($pagination);?>
			</div>

		<?php else: ?>
			<div class="alert alert--warning">Chưa có truyện đang theo dõi nào.</div>
		<?php endif; ?>
		
	<?php else: ?>
		<div class="alert alert--error">Bạn đã bị cấm sử dụng chức năng này</div>
	<?php endif; ?>
		</div>
	</div>
</div>

<form method="POST" id="form-action">
	<?=$insertHiddenToken;?>
	<input id="_action" type="hidden" name="<?=bookmarkController::NAME_FORM_ACTION;?>" value="" />
</form>

<script type="text/javascript">

	function request_api(self, type, callback) {
		var parent = self.parents('.action-wrapper, .bookmark-action');
		var id = parent.data('id') || null;
		if (parent.length) {
			parent.hide();
			parent.siblings('.progress-bar').show();
		}

		$.ajax({
			type: "POST",
			url: "<?=RouteMap::get('bookmark', ['type' => 'api']);?>",
			data: {<?=bookmarkController::NAME_FORM_ACTION;?>: type, <?=bookmarkController::INPUT_ID;?>: id},
			dataType: 'json',
			cache: false,
			success: function(response) {
				if(response.code == 200)
				{
					callback(response, parent);
				}
				else
				{
					$.toastShow(response.message, {
						type: 'error',
						timeout: 3000
					});	
				}
			},
			error: function() {
				$.toastShow('Có lỗi xảy ra. Vui lòng thử lại!', {
					type: 'error',
					timeout: 3000
				});
			},
			complete: function() {
				if(parent.length > 0)
				{
					parent.siblings('.progress-bar').hide();
					parent.show();					
				}
			}
		});
	}

    $(document).ready(function() {
        tooltip({
            target: '.tooltip-target',
			exclude_element: ['unread', 'read', 'remove', 'fas', 'fa']
        });
        modeView('<?=App::COOKIE_VIEW_MODE;?>');

		var element_list_view_item = '.list-view__item';
		var element_total_manga = '#total_manga';

		role_click('make-read-all', function(self, event) {
			event.preventDefault();
            var form = $('#form-action');
			var action = $('#_action');
			action.val('<?=bookmarkController::ACTION_MAKE_READ_ALL;?>');
			form.submit();
		});

		role_click('make-unread-all', function(self, event) {
			event.preventDefault();
            var form = $('#form-action');
			var action = $('#_action');
			action.val('<?=bookmarkController::ACTION_MAKE_UNREAD_ALL;?>');
			form.submit();
		});

		role_click('make-read', function(self, event) {
			event.preventDefault();
			request_api(self, '<?=bookmarkController::ACTION_MAKE_READ;?>', function(response, parent) {
				parent.parents(element_list_view_item).removeClass('is-unread').addClass('is-read');
			});
		});

		role_click('make-unread', function(self, event) {
			event.preventDefault();
			request_api(self, '<?=bookmarkController::ACTION_MAKE_UNREAD;?>', function(response, parent) {
				parent.parents(element_list_view_item).removeClass('is-read').addClass('is-unread');
			});
		});

		role_click('remove-manga', async function(self, event) {
			event.preventDefault();
			if(await comfirm_dialog('Bỏ theo dõi', 'Bạn thực sự muốn bỏ theo dõi truyện này?') !== true)
			{
				return;
			}
			request_api(self, '<?=bookmarkController::ACTION_REMOVE;?>', function(response, parent) {
				parent.parents(element_list_view_item).remove();
				$(element_total_manga).html(parseInt($(element_total_manga).html()) - 1);
			});
		});
    });  
</script>
<?php View::render_theme('layout.footer'); ?>