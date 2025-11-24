<?php View::render_theme('layout.header', compact('title')); ?>

<div class="section-random-manga">
	<div class="section-random-manga__bg-cover" style="background-image: url(<?=$random_manga['image'];?>);">
	</div>
	<div class="section-random-manga__bg-alpha"></div>

	<div class="section-random-manga__container">
		<a class="section-random-manga__container-title" href="<?= RouteMap::get('manga', ['id' => $random_manga['id'] ?? null]);?>"><?=_echo($random_manga['name'] ?? '');?></a>
		<div class="section-random-manga__container-description"><?=_echo($random_manga['text'] ?? '');?></div>
	</div>
</div>
<div class="banner-menu">
	<div class="banner-menu__container">
		<img class="banner-menu__container-image" src="<?=APP_URL;?>/assets/images/model.png">

		<div class="banner-menu__container-button">
			<a href="<?=RouteMap::get('history');?>">
				<span>Đã xem gần đây</span>
			</a>
			<a href="<?=RouteMap::get('search_manga');?>">
				<span>Tìm kiếm nâng cao</span>
			</a>
		</div>
	</div>
</div>

<div class="container my-6">
	<div class="row">
		<div class="col-lg-9 col-md-12">
			<div class="_title border-left mt-0 mb-4">
				<div class="d-flex justify-content-between align-items-center">
					<span>Truyện mới cập nhật</span>
					<a href="<?=RouteMap::build_query(['sort' => 'update'], 'manga');?>">Xem tất cả »</a>
				</div>
			</div>
			<div class="owl-carousel owl-theme">
			<?php
				$i = 0;
    			foreach ($new_chapters as $arr) {
					if (!($i % 2)) {
						echo '<div class="item">';
					}
					echo '
						<a class="item-new-chapter" href="'.RouteMap::get('manga', ['id' => $arr['id']]).'">
							<div class="item-new-chapter__image">
								<img src="'.$arr['image'].'">
							</div>
							<div class="item-new-chapter__detail">
								<div class="name">'._echo($arr['name']).'</div>
								<div class="chapter">'._echo($arr['name_last_chapter']).'</div>
							</div>
						</a>
					';
					if ($i % 2) {
						echo '</div>';
					}
					if (!($i % 2) && $i == count($new_chapters) - 1) {
						echo '</div>';
					}
					$i++;
				}
			?>
			</div>
			<div class="list-tab-manga">
				<div class="list-tab-manga__item active" data-content="new">Truyện mới</div>
				<div class="list-tab-manga__item" data-content="view">Xem nhiều nhất</div>
				<div class="list-tab-manga__item" data-content="follow">Theo dõi nhiều</div>
			</div>

			<div class="list-top-manga manga-list-view active" id="top-new">    
				<ul class="list-view mode--table">
				<?php
					$i = 0;
					foreach($new_manga as $manga):
					$i++;
					$url_manga = RouteMap::get('manga', ['id' => $manga['id']]);
					$url_chapter = RouteMap::get('chapter', ['id_manga' => $manga['id'], 'id_chapter' => $manga['id_last_chapter']]);
					$teams = Manga::get_team_name($manga, true);
					$genres = array_intersect_key($_genres, array_flip(array_filter(explode(',', $manga['genres_id'] ?? ''))));
				?>
					<li class="list-view__item">
						<a class="list-view__item-image tooltip-target" href="<?=$url_manga;?>">
							<img src="<?=_echo($manga['image']);?>">
						</a>
						<div class="list-view__item-info">
							<a class="info-name" href="<?=$url_manga;?>"><?=_echo($manga['name']);?></a>
							<div class="info__group">
								<div class="info-genres">
									<?php if($genres): foreach($genres as $id => $name): ?>
										<a href="<?=RouteMap::build_query([mangaController::PARAM_GENRES => $id], 'manga');?>"><?=_echo($name);?></a>
									<?php endforeach; else: ?>
										<span class="empty">Không rõ</span>
									<?php endif; ?>
								</div>
								<div class="info-status">
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
							</div>
						</div>
						<div class="star-<?=$i;?>"></div>
					</li>
				<?php endforeach; ?>
				</ul>
				<div class="d-flex justify-content-center align-items-center">
					<a href="<?=RouteMap::build_query(['sort' => 'new'], 'manga');?>">Xem tất cả »</a>
				</div>
			</div>

			<div class="list-top-manga manga-list-view" id="top-view">    
				<ul class="list-view mode--table">
				<?php
					$i = 0;
					foreach($view_manga as $manga):
					$i++;
					$url_manga = RouteMap::get('manga', ['id' => $manga['id']]);
					$url_chapter = RouteMap::get('chapter', ['id_manga' => $manga['id'], 'id_chapter' => $manga['id_last_chapter']]);
					$teams = Manga::get_team_name($manga, true);
					$genres = array_intersect_key($_genres, array_flip(array_filter(explode(',', $manga['genres_id'] ?? ''))));
				?>
					<li class="list-view__item">
						<a class="list-view__item-image tooltip-target" href="<?=$url_manga;?>">
							<span class="views">
								<i class="fas fa-eye"></i> <?=shortenNumber($manga['view']); ?>
							</span>
							<img src="<?=_echo($manga['image']);?>">
						</a>
						<div class="list-view__item-info">
							<a class="info-name" href="<?=$url_manga;?>"><?=_echo($manga['name']);?></a>
							<div class="info__group">
								<div class="info-genres">
									<?php if($genres): foreach($genres as $id => $name): ?>
										<a href="<?=RouteMap::build_query([mangaController::PARAM_GENRES => $id], 'manga');?>"><?=_echo($name);?></a>
									<?php endforeach; else: ?>
										<span class="empty">Không rõ</span>
									<?php endif; ?>
								</div>
								<div class="info-status">
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
							</div>
						</div>
						<div class="star-<?=$i;?>"></div>
					</li>
				<?php endforeach; ?>
				</ul>
				<div class="d-flex justify-content-center align-items-center">
					<a href="<?=RouteMap::build_query(['sort' => 'view'], 'manga');?>">Xem tất cả »</a>
				</div>
			</div>

			<div class="list-top-manga manga-list-view" id="top-follow">    
				<ul class="list-view mode--table">
				<?php
					$i = 0;
					foreach($follow_manga as $manga):
					$i++;
					$url_manga = RouteMap::get('manga', ['id' => $manga['id']]);
					$url_chapter = RouteMap::get('chapter', ['id_manga' => $manga['id'], 'id_chapter' => $manga['id_last_chapter']]);
					$teams = Manga::get_team_name($manga, true);
					$genres = array_intersect_key($_genres, array_flip(array_filter(explode(',', $manga['genres_id'] ?? ''))));
				?>
					<li class="list-view__item">
						<a class="list-view__item-image tooltip-target" href="<?=$url_manga;?>">
							<span class="views">
								<i class="fas fa-bookmark"></i> <?=shortenNumber($manga['follow']); ?>
							</span>
							<img src="<?=_echo($manga['image']);?>">
						</a>
						<div class="list-view__item-info">
							<a class="info-name" href="<?=$url_manga;?>"><?=_echo($manga['name']);?></a>
							<div class="info__group">
								<div class="info-genres">
									<?php if($genres): foreach($genres as $id => $name): ?>
										<a href="<?=RouteMap::build_query([mangaController::PARAM_GENRES => $id], 'manga');?>"><?=_echo($name);?></a>
									<?php endforeach; else: ?>
										<span class="empty">Không rõ</span>
									<?php endif; ?>
								</div>
								<div class="info-status">
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
							</div>
						</div>
						<div class="star-<?=$i;?>"></div>
					</li>
				<?php endforeach; ?>
				</ul>
				<div class="d-flex justify-content-center align-items-center">
					<a href="<?=RouteMap::build_query(['sort' => 'follow'], 'manga');?>">Xem tất cả »</a>
				</div>
			</div>

		</div>


		<div class="col-lg-3 col-md-12">
			<div class="d-none d-lg-block mb-4">
				<div class="_title border-left mt-0 mb-4">Thống kê</div>
				<div class="fanpage-facebook">
					<div class="fb-page" data-href="https://www.facebook.com/ttManga" data-tabs="events" data-width="" data-height="" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true">
						<blockquote cite="https://www.facebook.com/ttManga" class="fb-xfbml-parse-ignore">
							<a href="https://www.facebook.com/ttManga">TT manga</a>
						</blockquote>
					</div>
				</div>
				<div class="my-1 mt-2"><i class="fas fa-books"></i> Số truyện: <strong><?=number_format($total_manga, 0, ',', '.');?></strong></div>
				<div class="my-1"><i class="fas fa-database"></i> Số chương: <strong><?=number_format($total_chapter, 0, ',', '.');?></strong></div>
				<div class="_title border-left mt-4">Truyện mới cập nhật</div>
				<div class="list-new-chapter">
					<ul>
					<?php foreach (array_slice($new_chapters, 0, 20) as $val): ?>
						<?php if (!empty($val['id_last_chapter'])) :?> 
						<li>
							<i class="fas fa-link"></i>
							<div>
								<a href="<?=RouteMap::get('manga', ['id' => $val['id']]);?>"><?=_echo($val['name']);?></a> <a href="<?=RouteMap::get('chapter', ['id_manga' => $val['id'], 'id_chapter' => $val['id_last_chapter']]);?>"><span><?=_echo($val['name_last_chapter']);?></span></a>
							</div>
						</li>
						<?php endif; ?>
					<?php endforeach; ?>
					</ul>
					<div class="d-flex justify-content-center">
						<a href="<?=RouteMap::build_query(['sort' => 'update'], 'manga');?>">Xem thêm...</a>
					</div>
				</div>
			</div>
			<div class="_title border-left mt-0">
				<div class="d-flex justify-content-between align-items-center">
					<span>Bình luận mới</span>
					<a href="<?=RouteMap::get('comments');?>">Xem tất cả »</a>
				</div>
			</div>
			<div class="section-comment">
				<div class="comment-container p-0 mt-4">
				<?php foreach($new_comments as $val):
					$user = [
						'id' => $val['user_id'],
						'name' =>  $val['user_name'],
						'username' =>  $val['user_username'],
						'avatar' =>  $val['user_avatar'],
						'user_ban' =>  $val['user_ban'],
						'role_color' =>  $val['user_role_color'],
					];
				?>
					<div class="comment-item px-0 mb-2">
						<?=render_avatar($user, null, false, false, ['comment-avatar']);?>
						<div class="comment-wrapper ms-2">
							<div class="comment-wrapper__body">
								<div class="comment-wrapper__body-text w-100">
									<div class="username">
										<a target="_blank" class="user" href="<?=RouteMap::get('profile', ['id' => $user['id']]);?>">
											<?=ucwords(User::get_display_name($user));?>	
										</a>
									</div>
									<div class="text mt-1 w-100"><?=_echo($val['text'], true, true);?></div>
								</div>
							</div>
							<div class="comment-wrapper__footer">
								<div class="time"><?=_time($val['created_at']);?></div>
								<span class="chapter">
									<a title="<?=_echo($val['manga_name']);?>" href="<?=$val['chapter_name'] ? RouteMap::get('chapter', ['id_manga' => $val['manga_id'], 'id_chapter' => $val['chapter_id']]) : RouteMap::get('manga', ['id' => $val['manga_id']]);?>"><?=$val['chapter_name'] ? _echo($val['chapter_name']) :  _echo($val['manga_name']);?></a>
								</span>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="fb-root"></div>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/vi_VN/sdk.js#xfbml=1&version=v23.0&appId=1545946785646444"></script>

<link rel="stylesheet" href="<?=APP_URL;?>/assets/css/owl.carousel.css?v=<?=$_version;?>">
<script type="text/javascript" src="<?=APP_URL;?>/assets/script/owl.carousel.min.js?v=<?=$_version;?>"></script>
<script type="text/javascript">
	$('.owl-carousel').owlCarousel({
		loop: false,
		nav: false,
		margin: 10,
		responsive:{
			0:{
				items: 2
			},
			478:{
				items: 3
			},
			678:{
				items: 4
			},
			768:{
				items: 5
			}
		}
	})

	$(document).ready(() => {
		$('.list-tab-manga__item').on('click', function(e) {
			const contentData = $(this).data('content')

			$('.list-tab-manga__item').parent().find('.active').removeClass('active')
			$('.list-top-manga.active').removeClass('active')

			$(this).addClass('active')
			$(`#top-${contentData}`).addClass('active')
		})
	})
</script>
<?php View::render_theme('layout.footer'); ?>