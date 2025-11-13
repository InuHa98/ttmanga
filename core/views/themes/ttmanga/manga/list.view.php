<?php View::render_theme('layout.header', compact('title')); ?>
<?=themeController::load_css('css/manga.css'); ?>
<div class="section-sub-header">
	<div class="container">
        <span><?=($current_genres ? _echo($current_genres['name']) : 'Tất cả thể loại');?></span>
    </div>
</div>

<div class="container">
	<div class="row">
		<div class="col-xs-12 col-md-8 col-lg-9">
			<div class="tabmenu-horizontal">
				<div class="tabmenu-horizontal__item <?=(!$sort || $sort == mangaController::SORT_ALPHABET ? 'active' : null);?>">
					<a href="<?=Request::build_query([mangaController::PARAM_SORT => mangaController::SORT_ALPHABET]);?>">Xếp theo Alphabet</a>
				</div>
				<div class="tabmenu-horizontal__item <?=($sort == mangaController::SORT_VIEW ? 'active' : null);?>">
					<a href="<?=Request::build_query([mangaController::PARAM_SORT => mangaController::SORT_VIEW]);?>">Xếp theo lượt xem</a>
				</div>
				<div class="tabmenu-horizontal__item <?=($sort == mangaController::SORT_UPDATE ? 'active' : null);?>">
					<a href="<?=Request::build_query([mangaController::PARAM_SORT => mangaController::SORT_UPDATE]);?>">Mới cập nhật</a>
				</div>
				<div class="tabmenu-horizontal__item <?=($sort == mangaController::SORT_NEW ? 'active' : null);?>">
					<a href="<?=Request::build_query([mangaController::PARAM_SORT => mangaController::SORT_NEW]);?>">Truyện mới</a>
				</div>
				<div class="tabmenu-horizontal__item <?=($sort == mangaController::SORT_FOLLOW ? 'active' : null);?>">
					<a href="<?=Request::build_query([mangaController::PARAM_SORT => mangaController::SORT_FOLLOW]);?>">Theo dõi nhiều</a>
				</div>
			</div>

			<ul class="character-box">
				<li>
					<a class="<?=($character == mangaController::CHARACTER_ALL ? 'active' : null);?>" href="<?=Request::build_query([mangaController::PARAM_CHARACTER => mangaController::CHARACTER_ALL]);?>">Tất cả</a>
				</li>	
				<li>
					<span class="separator">|</span>
					<a class="<?=($character == mangaController::CHARACTER_SPECIAL ? 'active' : null);?>" href="<?=Request::build_query([mangaController::PARAM_CHARACTER => mangaController::CHARACTER_SPECIAL]);?>">#</a>
				</li>
				<?php foreach(range('a','z') as $val): ?>
				<li>
					<span class="separator">|</span>
					<a class="<?=($character == $val ? 'active' : null);?>" href="<?=Request::build_query([mangaController::PARAM_CHARACTER => $val]);?>"><?=$val;?></a>
				</li>
				<?php endforeach; ?>
			</ul>

			<div class="left-box">
				<select class="status js-custom-select" id="change-status">
					<option <?=($status == mangaController::STATUS_ALL ? 'selected' : null);?> value="<?=Request::build_query([mangaController::PARAM_STATUS => mangaController::STATUS_ALL]);?>">
						Tất cả tình trạng
					</option>
					<option <?=($status == mangaController::STATUS_ONGOING ? 'selected' : null);?> value="<?=Request::build_query([mangaController::PARAM_STATUS => mangaController::STATUS_ONGOING]);?>">
						Đang tiến hành
					</option>
					<option <?=($status == mangaController::STATUS_COMPLETE ? 'selected' : null);?> value="<?=Request::build_query([mangaController::PARAM_STATUS => mangaController::STATUS_COMPLETE]);?>">
						Đã hoàn thành
					</option>
					<option <?=($status == mangaController::STATUS_DROP ? 'selected' : null);?> value="<?=Request::build_query([mangaController::PARAM_STATUS => mangaController::STATUS_DROP]);?>">
						Đã tạm ngưng
					</option>
				</select>
				<select class="genres js-custom-select" id="change-genres">
					<option <?=(!$current_genres ? 'selected' : null);?> value="<?=Request::build_query([mangaController::PARAM_GENRES => 0]);?>">Tất cả thể loại</option>
				<?php foreach($_genres as $id => $name): ?>
					<option <?=($current_genres && $current_genres['id'] == $id ? 'selected' : null);?> value="<?=Request::build_query([mangaController::PARAM_GENRES => $id]);?>"><?=_echo($name);?></option>
				<?php endforeach; ?>
				</select>
			</div>

		<?php if($current_genres): ?>
			<div class="info-box mb-2"><?=_echo($current_genres['text']);?></div>
		<?php endif; ?>

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
					$teams = Manga::get_team_name($manga, true);
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


		<div class="col-xs-12 col-md-4 col-lg-3 right-box">
			<div class="_title">Tình trạng</div>
			<div class="sider-box">
				<ul class="status">
					<li class="<?=($status == mangaController::STATUS_ALL ? 'active' : null);?>">
						<a href="<?=Request::build_query([mangaController::PARAM_STATUS => mangaController::STATUS_ALL]);?>">
							Tất cả
						</a>
					</li>
					<li class="<?=($status == mangaController::STATUS_ONGOING ? 'active' : null);?>">
						<a href="<?=Request::build_query([mangaController::PARAM_STATUS => mangaController::STATUS_ONGOING]);?>">
							Đang tiến hành
						</a>
					</li>
					<li class="<?=($status == mangaController::STATUS_COMPLETE ? 'active' : null);?>">
						<a href="<?=Request::build_query([mangaController::PARAM_STATUS => mangaController::STATUS_COMPLETE]);?>">
							Đã hoàn thành
						</a>
					</li>
					<li class="<?=($status == mangaController::STATUS_DROP ? 'active' : null);?>">
						<a href="<?=Request::build_query([mangaController::PARAM_STATUS => mangaController::STATUS_DROP]);?>">
							Đã tạm ngưng
						</a>
					</li>
				</ul>
			</div>
			<div class="_title">Thể loại</div>
			<div class="sider-box">
				<ul class="genres">
					<li class="<?=(!$current_genres ? 'active' : null);?>">
						<a href="<?=Request::build_query([mangaController::PARAM_GENRES => 0]);?>">Tất cả thể loại</a>
					</li>
				<?php foreach($_genres as $id => $name): ?>
					<li class="<?=($current_genres && $current_genres['id'] == $id ? 'active' : null);?>">
						<a href="<?=Request::build_query([mangaController::PARAM_GENRES => $id]);?>"><?=_echo($name);?></a>
					</li>
				<?php endforeach; ?>
				</ul>
			</div>
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