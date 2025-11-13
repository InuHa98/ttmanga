<?php View::render_theme('layout.header', compact('title')); ?>
<?=themeController::load_css('css/manga.css'); ?>
<div class="section-sub-header">
	<div class="container">
        <span><?=!empty($team['name']) ? _echo($team['name']) : null;?></span>
    </div>
</div>

<div class="container">
	<div class="row">
		<div class="col-xs-12 col-md-8 col-lg-8">

			<div class="flex-panel">
				<div class="flex-panel__box">
					<span>Tìm thấy <strong><?=number_format($count, 0, ',', '.');?></strong> truyện.</span>
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
				
		<?php if($manga_items): ?>

			<div class="manga-list-view">    
				<ul class="list-view <?=($view_mode == 'table' ? 'mode--table' : null);?>">
				<?php foreach($manga_items as $manga): ?>
				<?php
					$url_manga = RouteMap::get('manga', ['id' => $manga['id']]);
					$url_chapter = RouteMap::get('chapter', ['id_manga' => $manga['id'], 'id_chapter' => $manga['id_last_chapter']]);
					$auths = Manga::get_auth($manga);
					$genres = array_intersect_key($_genres, array_flip(array_filter(explode(',', $manga['genres_id'] ?? ''))));
				?>
					<li class="list-view__item tooltip-data">
						<a class="list-view__item-image tooltip-target" href="<?=$url_manga;?>">
							<span class="views">
								<span class="me-1">
									<i class="fas fa-eye"></i> <?=shortenNumber($manga['view']); ?>
								</span>
								<span>
									<i class="fas fa-bookmark"></i> <?=shortenNumber($manga['follow']); ?>
								</span>
							</span>
							<?php if ($manga['view'] >= env(DotEnv::VIEW_HOT)): ?>
								<span class="hot"></span>
							<?php endif; ?>
							<img data-tooltip="image" src="<?=_echo($manga['image']);?>">
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
									<span class="info-label">Tác giả:</span>
									<?php if($auths): foreach($auths as $val): ?>
										<a href="<?=RouteMap::build_query([mangaController::INPUT_AUTHOR => $val], 'search_manga');?>"><?=_echo($val);?></a>
									<?php endforeach; else: ?>
										<span class="empty">Không rõ</span>
									<?php endif; ?>
								</div>
								<div class="info-chapter">
									<span class="info-label">Mới nhất:</span>
									<?=($manga['id_last_chapter'] ? '<a href="'.$url_chapter.'">'._echo($manga['name_last_chapter']).'</a>' : '<span class="empty">Chưa có!!!</span>');?>
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
				<div class="alert alert--warning">Chưa có truyện nào!</div>
			<?php endif; ?>

		</div>


		<div class="col-xs-12 col-md-4 col-lg-4 mb-4">
			<div class="team-infomation">
				<div class="section-team-cover">
					<div id="preview_cover" class="section-team-cover__bg-cover" style="background-image: url(<?=Team::get_cover($team);?>);">
					</div>
					<div class="section-team-cover__bg-alpha"></div>
					<div class="team-name">
						<?=render_avatar($team, Team::get_avatar($team));?>
						<div class="name"><?=_echo($team['name']);?></div>
					</div>

				</div>
			<?php if(!empty($own['id'])): ?>
				<div class="team-infomation__line">
					<span class="label"><i class="fab fa-empire"></i> Trưởng nhóm:</span>
					<span class="text">
						<a target="_blank" class="user-infomation" href="<?=RouteMap::get('profile', ['id' => $own['id']]);?>">
							<?=render_avatar($own, null, true, true);?>
						</a>
					</span>
				</div>
			<?php endif;?>
			<?php if(isset($team['total_members'])): ?>
				<div class="team-infomation__line">
					<span class="label"><i class="fas fa-users"></i> Thành viên:</span>
					<span class="number"><?=number_format($team['total_members'], 0, ',', '.');?></span>
				</div>
			<?php endif; ?>
			<?php if(isset($team['total_mangas'])): ?>
				<div class="team-infomation__line">
					<span class="label"><i class="fas fa-books"></i> Số truyện:</span>
					<span class="number"><?=number_format($team['total_mangas'], 0, ',', '.');?></span>
				</div>
			<?php endif; ?>
			<?php if(isset($team['total_chapters'])): ?>
				<div class="team-infomation__line">
					<span class="label"><i class="fas fa-database"></i> Số chương:</span>
					<span class="number"><?=number_format($team['total_chapters'], 0, ',', '.');?></span>
				</div>
			<?php endif; ?>
			<?php if(!empty($team['facebook'])): ?>
				<div class="team-infomation__line">
					<span class="label"><i class="fab fa-facebook-square"></i> Facebook:</span>
					<span class="text"><a target="_blank" href="<?=_echo($team['facebook']);?>"><?=_echo($team['facebook']);?></a></span>
				</div>
			<?php endif; ?>
			</div>
		<?php if(!empty($team['desc'])): ?>
			<div class="info-box mb-4"><?=_echo($team['desc']);?></div>
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

		$('#change-status, #change-genres').on('change', function() {
			window.location.href = $(this).val();
		});
    });  
</script>


<?php View::render_theme('layout.footer'); ?>