<?php View::render_theme('layout.header', ['title' => $title]); ?>


<div class="section-sub-header">
	<div class="container">
        <span>Lịch sử đọc</span>
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
					<a class="box__body-item active" href="<?=RouteMap::get('history');?>">
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
					<span>Chỉ lưu lại tối đa <strong><?=History::LIMIT_ITEM;?></strong> truyện đã xem gần nhất.</span>
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

		<?php if(UserPermission::has('user_history')): ?>

        <?php if($manga_items): ?>
			
			<div class="manga-list-view">    
				<ul class="list-view <?=($view_mode == 'table' ? 'mode--table' : null);?>">
				<?php foreach($manga_items as $manga): ?>
				<?php
					$url_manga = RouteMap::get('manga', ['id' => $manga['id']]);
					$url_chapter = RouteMap::get('chapter', ['id_manga' => $manga['id'], 'id_chapter' => $manga['id_last_chapter']]);
					$teams = Manga::get_team_name($manga, true);
				?>
					<li class="list-view__item tooltip-data">
						<a class="list-view__item-image tooltip-target" href="<?=$url_manga;?>">
							<img data-tooltip="image" src="<?=_echo($manga['image']);?>">
						</a>
						<div class="list-view__item-info">
							<a class="info-name" data-tooltip="title" href="<?=$url_manga;?>"><?=_echo($manga['name']);?></a>
							<div class="info__group">

								<div class="info-team">
									<span class="info-label">Nhóm dịch:</span>
									<?php if($teams): foreach($teams as $val): ?>
										<a href="<?=RouteMap::get('team', ['name' => $val]);?>"><?=_echo($val);?></a>
									<?php endforeach; else: ?>
										<span class="empty">Không rõ</span>
									<?php endif; ?>
								</div>
								<div class="info-chapter">
									<span class="info-label">Đang đọc:</span>
									<?=($manga['id_last_chapter'] ? '<a href="'.$url_chapter.'">'._echo($manga['name_last_chapter']).'</a>' : '<span class="empty">Chưa có!!!</span>');?>
								</div>
								<div class="info-status">
									<span class="info-label">Đã xem:</span>
									<?=(isset($history[$manga['id']]) ? _time($history[$manga['id']][1]) : null);?>
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
			<div class="alert alert--warning">Danh sách trống.</div>
		<?php endif; ?>


		<?php else: ?>
			<div class="alert alert--error">Bạn đã bị cấm sử dụng chức năng này</div>
		<?php endif; ?>

		</div>
	</div>
</div>


<script type="text/javascript">


    $(document).ready(function() {
        tooltip({
            target: '.tooltip-target'
        });
        modeView('<?=App::COOKIE_VIEW_MODE;?>');
    });  
</script>
<?php View::render_theme('layout.footer'); ?>