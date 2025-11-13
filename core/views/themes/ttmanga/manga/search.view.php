<?php View::render_theme('layout.header', compact('title')); ?>
<?=themeController::load_css('css/manga.css'); ?>
<div class="section-sub-header">
	<div class="container">
        <span>Tìm kiếm nâng cao</span>
    </div>
</div>

<div class="container">
	<div class="row">
		<div class="col-xs-12 col-md-12 col-lg-12">
			<div class="accordion <?=$count_filter < 1 ? 'show' : null;?>">
				<div class="accordion__header">
					<div class="_title mt-0"><i class="fas fa-filter"></i> Bộ lọc <?=($count_filter > 0 ? '('.$count_filter.')' : null);?></div>
				</div>
				<div class="accordion__content">
					<form id="form-validate" method="POST">
						<div class="box__body">
							<div class="form-group limit--width">
								<label class="control-label">Tên truyện</label>
								<div class="form-control">
									<input class="form-input" name="<?=mangaController::INPUT_KEYWORD;?>" placeholder="Tìm theo tên truyện" type="text" value="<?=_echo($keyword);?>">
								</div>
							</div>
							<div class="form-group limit--width">
								<label class="control-label">Tác giả</label>
								<div class="form-control">
									<input class="form-input" name="<?=mangaController::INPUT_AUTHOR;?>" placeholder="Tìm theo tên tác giả" type="text" value="<?=_echo($author_name);?>">
								</div>
							</div>
							<div class="form-group limit--width">
								<label class="control-label">Nhóm dịch</label>
								<div class="form-control">
									<input class="form-input" name="<?=mangaController::INPUT_TEAM;?>" placeholder="Tìm theo tên nhóm dịch" type="text" value="<?=_echo($team_name);?>">
								</div>
							</div>
							
							<div class="form-group">
								<label class="control-label">Thể loại:</label>
								<div class="form-control">
									<div class="genre-list">
									<?php
										foreach($_genres as $id => $name):
											$is_include = isset($genres[$id]) && $genres[$id] == 1;
											$is_exclude = isset($genres[$id]) && $genres[$id] == -1;
									?>
										<div class="state-btn <?=($is_include ? 'include' : null);?> <?=($is_exclude ? 'exclude' : null);?>">
											<select name="<?=mangaController::INPUT_GENRES;?>[<?=$id;?>]">
												<option value="0"></option>
												<option value="-1" <?=($is_exclude ? 'selected' : null);?>></option>
												<option value="1" <?=($is_include ? 'selected' : null);?>></option>
											</select>
											<label><?=_echo($name);?></label>
										</div>
									<?php endforeach; ?>
									</div>
								</div>
							</div>

							<div class="form-group limit--width">
								<label class="control-label">Tình trạng:</label>
								<div class="form-control">
									<select class="form-select js-custom-select" name="<?=mangaController::INPUT_STATUS;?>" data-placeholder="Vui lòng chọn 1 trạng thái" data-max-width="300px">
										<option value="<?=mangaController::STATUS_ALL;?>" <?=($status == mangaController::STATUS_ALL ? 'selected' : null);?>>Sao cũng được</option>
										<option value="<?=mangaController::STATUS_ONGOING;?>" <?=($status == mangaController::STATUS_ONGOING ? 'selected' : null);?>>Đang tiến hành</option>
										<option value="<?=mangaController::STATUS_COMPLETE;?>" <?=($status == mangaController::STATUS_COMPLETE ? 'selected' : null);?>>Đã hoàn thành</option>
										<option value="<?=mangaController::STATUS_DROP;?>" <?=($status == mangaController::STATUS_DROP ? 'selected' : null);?>>Tạm ngưng</option>
									</select>
								</div>
							</div>
						</div>
						<div class="box__footer">
							<div class="d-flex justify-content-end align-items-center">
								<button type="submit" class="btn" name="submit">Tìm kiếm</button>
								<button type="submit" class="btn btn--gray" name="reset">Huỷ lọc</button>
							</div>
						</div>
					</form>
				</div>
			</div>

	<?php if ($count_filter > 0): ?>
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

		$('.state-btn').on('click', function() {
			var selectedGenre = $(this).children('select');
			if ($(this).hasClass('include')) {
				$(this).removeClass('include').addClass('exclude');
				selectedGenre.val(-1).change();
			} else if ($(this).hasClass('exclude')) {
				$(this).removeClass('exclude');
				selectedGenre.val(0).change();
			} else {
				$(this).addClass('include');
				selectedGenre.val(1).change();
			}
		});
    });  
</script>


<?php View::render_theme('layout.footer'); ?>