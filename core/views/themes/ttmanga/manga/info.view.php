<?php View::render_theme('layout.header', compact('title')); ?>

<?=themeController::load_css('css/manga.css'); ?>

<?php

$manga_name = _echo($manga['name']);
$manga_image = _echo($manga['image']);
$manga_cover = _echo($manga['cover']);

?>
<div class="mb-4">
	<div class="section-cover-manga">
		<div class="section-cover-manga__bg-cover" style="background-image: url(<?=$manga_cover;?>), url(<?=$manga_image;?>);"></div>
		<div class="section-cover-manga__bg-alpha <?=(!$manga['cover'] ? 'backdrop-filter' : null);?>"></div>
		<div class="container container-cover">
			<div class="container-cover__image">
				<img src="<?=$manga_image;?>" />
			</div>
			<div class="container-cover__title">
				<div class="title"><?=$manga_name;?></div>
				<div class="button">
				<?php if($first_chapter): ?>
					<a href="<?=RouteMap::get('chapter', ['id_manga' => $first_chapter['manga_id'], 'id_chapter' => $first_chapter['id']]);?>">Đọc ngay</a>
					<a href="<?=RouteMap::get('chapter', ['id_manga' => $last_chapter['manga_id'], 'id_chapter' => $last_chapter['id']]);?>">Mới nhất</a>
				<?php else: ?>
					<a class="disabled" href="#">Đọc ngay</a>
					<a class="disabled" href="#">Mới nhất</a>
				<?php endif; ?>
				</div>
			</div>
		</div>
		<div class="statistic">
			<div class="container">
				<div class="container__statistic">
					<ul>
						<li class="number"><?=number_format(count($chapters), 0, ',', '.');?></li>
						<li><i class="fa fa-database"></i> Chương</li>
					</ul>
					<ul>
						<li class="number"><?=number_format($views, 0, ',', '.');?></li>
						<li><i class="fa fa-eye"></i> Lượt xem</li>
					</ul>
					<ul>
						<li class="number" id="total_follow"><?=number_format($follows, 0, ',', '.');?></li>
						<li><i class="fas fa-bookmark"></i> Theo dõi</li>
					</ul>
				</div>
				<div role="follow" class="container__follow <?=($hasFollow ? 'hasFollow' : null);?>">
				<?php if(Auth::$isLogin): ?>
					<div class="follow">
						<i class="fas fa-bookmark"></i>
						<span>Theo dõi</span>
					</div>
					<div class="unfollow">
						<i class="fad fa-minus-square"></i>
						<span>Bỏ theo dõi</span>
					</div>
				<?php else: ?>
					<a class="follow" href="<?=RouteMap::get('login');?>">
						<span>Đăng nhập theo dõi</span>
					</a>
				<?php endif; ?>
					<div class="progress-bar"></div>
				</div>
			</div>
		</div>
	</div>

	<div class="container">
		<div class="row">
			<div class="col-lg-9">
				
				<div class="manga-infomation">
					<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 _title">
						<div><?=$manga_name;?></div>
						<?php if($is_own): ?>
							<a target="_blank" href="<?=RouteMap::get('manga_management', ['action' => mangaManagementController::ACTION_DETAIL, 'id' => $manga['id']]);?>">
								<button class="btn btn--gray p-2">
									<i class="fas fa-pen"></i> Chỉnh sửa
								</button>
							</a>
						<?php endif; ?>
					</div>

					<?php if($name_other): ?>
					<div class="manga-infomation__item">
						<div class="label"><i class="fas fa-pen"></i> Tên khác:</div>
						<div class="text">
						<?php foreach($name_other as $name): ?>
							<span class="other-name"><?=_echo($name);?></span>
						<?php endforeach; ?>
						</div>
					</div>
					<?php endif; ?>
					<div class="manga-infomation__item">
						<div class="label"><i class="fas fa-tags"></i> Thể loại:</div>
						<div class="text">
						<?php if($genres): foreach($genres as $val): ?>
							<a class="genres" href="<?=RouteMap::build_query([mangaController::PARAM_GENRES => $val['id']], 'manga');?>"><?=_echo($val['name']);?></a>
						<?php endforeach; else: ?>
							<span class="unknown">Không rõ</span>
						<?php endif; ?>
						</div>
					</div>
					<div class="manga-infomation__item">
						<div class="label"><i class="fa fa-user"></i> Tác giả:</div>
						<div class="text">
						<?php if($auths): foreach($auths as $val): ?>
							<a class="auth" href="<?=RouteMap::build_query([mangaController::INPUT_AUTHOR => $val], 'search_manga');?>"><?=_echo($val);?></a>
						<?php endforeach; else: ?>
							<span class="unknown">Không rõ</span>
						<?php endif; ?>
						</div>
					</div>
					<div class="manga-infomation__item">
						<div class="label"><i class="fas fa-layer-group"></i> Nhóm dịch:</div>
						<div class="text">
						<?php if($teams): foreach($teams as $val): ?>
							<a class="team" href="<?=RouteMap::get('team', ['name' => $val['name']]);?>"><?=_echo($val['name']);?></a>
						<?php endforeach; else: ?>
							<span class="unknown">Không rõ</span>
						<?php endif; ?>
						</div>
					</div>
					<div class="manga-infomation__item">
						<div class="label"><i class="fa fa-rss"></i> Tình trạng:</div>
						<div class="text"><?=$status;?></div>
					</div>

					<div class="manga-infomation__item d-flex d-lg-none">
						<div class="label"><i class="fas fa-upload"></i> Đăng bởi:</div>
						<div class="text">
							<a target="_blank" class="user-infomation" href="<?=RouteMap::get('profile', ['id' => $uploader['creator']['id']]);?>">
								<?=render_avatar($uploader['creator'], null, true, true);?>
							</a>
						</div>
					</div>

					<div class="manga-infomation__item column">
						<div class="label">Sơ lược:</div>
						<div class="text"><?=_echo($manga['text'], true);?></div>
					</div>
				</div>
				
				<div class="_title mt-0"><i class="fas fa-list"></i> Danh sách chương</div>
				<div class="chapter-list mt-4">
				<?php if($chapters): ?>
					<table>
						<tbody>
							<tr>
								<th class="chapter-name">Tên chương</th>
								<th class="date">Ngày đăng</th>
								<th class="download"></th>
							</tr>
						<?php foreach($chapters as $val):
							$download = $val != '' ? json_decode($val['download'], true) : [];
						?>
							<tr>
								<td class="chapter-name">
									<a href="<?=RouteMap::get('chapter', ['id_manga' => $manga['id'], 'id_chapter' => $val['id']]);?>"><?=_echo($val['name']);?></a>
									<?php if (time() - $val['created_at'] <= 3 * 24 * 60 * 60): ?>
										<span class="update"></span>
									<?php endif;?>
								</td>
								<td class="date"><?=_time($val['created_at']);?></td>
								<td class="download">
								<?php if ($download): ?>
									<a class="download" target="_blank" href="<?=_echo($download[0]);?>"><i class="fad fa-download"></i></a>
								<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				<?php else: ?>
					<div class="alert alert--warning">Chưa có chương truyện nào!!!</div>
				<?php endif; ?>
				</div>


			</div>
			<div class="col-lg-3">
				<div class="d-none d-lg-block">
					<div class="_title">Nhân sự</div>
					<div class="upload-infomation">
						<a target="_blank" class="user" href="<?=RouteMap::get('profile', ['id' => $uploader['creator']['id']]);?>">
							<?=render_avatar($uploader['creator']);?>
							<div class="info">
								<div class="username"><?=ucwords(User::get_display_name($uploader['creator']));?></div>
								<div class="job">người đăng</div>
							</div>			
						</a>
					<?php if($uploader['uploader']): foreach($uploader['uploader'] as $user_upload): ?>
						<a target="_blank" class="user" href="<?=RouteMap::get('profile', ['id' => $user_upload['id']]);?>">
							<?=render_avatar($user_upload);?>
							<div class="info">
								<div class="username"><?=ucwords(User::get_display_name($user_upload));?></div>
								<div class="job">tham gia upload</div>
							</div>			
						</a>
					<?php endforeach; endif; ?>
					</div>
				</div>

			<?php if($other_teams): ?>
				<div class="_title">Bản dịch khác</div>
				<div class="other-chapter-list">
					<ul>
					<?php foreach($other_teams as $val): ?>
						<li class="odd">
							<i class="fas fa-layer-group"></i>
							<a href="<?=$val['url'];?>">Nhóm dịch <strong><?=$val['team_name'];?></strong></a>
						</li>
					<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

				<div class="<?=(empty($links) ? 'right-box' : '');?>">
					<div class="_title">Liên kết</div>
					<div class="links-box">
					<?php if($links): foreach($links as $link): ?>
						<a class="link" href="<?=_echo($link['url']);?>"><?=_echo($link['text']);?></a>
					<?php endforeach; else: ?>
						<div class="empty-link">Không có liên kết nào</div>
					<?php endif; ?>
					</div>
				</div>


			</div>
		</div>
	</div>

	<div class="section-comment mb-0">
		<div class="container">
			<div class="title-comment pb-0"><i class="fas fa-comments"></i> Bình luận (<span class="total-comment">0</span>)</div>
		<?php if(Auth::$isLogin == true): ?>
			<?php if(UserPermission::has('user_comment')): ?>
			<form class="comment-editor" method="POST">
				<div class="form-group">
					<div class="form-control">
						<textarea class="form-textarea" name="text" placeholder="Nhập bình luận..." rows="1" style="height: 50px"></textarea>
					</div>
				</div>
				<div class="comment-submit">
					<button type="submit" class="btn comment-submit__button">
						<div class="text">Gửi bình luận</div>
						<div class="comment-submit__loading">
							<div class="animation-spinner">
								<div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>
							</div>
							<span>Đang gửi...</span>
						</div>
					</button>
				</div>
			</form>
			<?php else: ?>
				<div class="alert alert--error mt-2">Bạn đã bị cấm bình luận</div>
			<?php endif; ?>
		<?php else: ?>
			<div class="error-login round mt-2">Vui lòng <a href="<?=RouteMap::get('login');?>">đăng nhập</a> để có thể bình luận</div>
		<?php endif; ?>
			<div class="comment-container px-0"></div>
			<div class="comment-loading">
				<div class="animation-spinner">
					<div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>
				</div>
				<span>Đang tải bình luận...</span>
			</div>
			<div class="comment-pagination"></div>
		</div>
	</div>
</div>

<script type="text/javascript" src="<?=APP_URL;?>/assets/script/tinymce/tinymce.min.js?v=<?=$_version;?>"></script>
<script type="text/javascript" src="<?=APP_URL;?>/assets/script/form-validator.js?v=<?=$_version;?>"></script>
<?=themeController::load_js('script/comments.js');?>

<script type="text/javascript">

	var btn_follow = $('[role="follow"]'),
		total_follow = $('#total_follow'),
		class_has_follow = 'hasFollow',
		class_is_loading = 'isLoading';
	var hasFollow = btn_follow.hasClass(class_has_follow);

	function request_bookmark(id) {

		var type = hasFollow ? '<?=bookmarkController::ACTION_REMOVE;?>' : '<?=bookmarkController::ACTION_ADD;?>';

		btn_follow.removeClass(class_is_loading).addClass(class_is_loading);

		$.ajax({
			type: "POST",
			url: "<?=RouteMap::get('bookmark', ['type' => 'api']);?>",
			data: {<?=bookmarkController::NAME_FORM_ACTION;?>: type, <?=bookmarkController::INPUT_ID;?>: id},
			dataType: 'json',
			cache: false,
			success: function(response) {
				if(response.code == 200)
				{
					var current_follow = parseInt(total_follow.html());
					if(hasFollow)
					{
						btn_follow.removeClass(class_has_follow);
						total_follow.html(current_follow - 1);
					}
					else
					{
						btn_follow.removeClass(class_has_follow).addClass(class_has_follow);
						total_follow.html(current_follow + 1);

					}
				}
				else
				{
					$.toastShow(response.message, {
						type: 'error',
						timeout: 3000
					});	
				}
				hasFollow = response.data.hasFollow;
			},
			error: function() {
				$.toastShow('Có lỗi xảy ra. Vui lòng thử lại!', {
					type: 'error',
					timeout: 3000
				});
			},
			complete: function() {
				btn_follow.removeClass(class_is_loading);
			}
		});
	}

	(function() {
		new Comment({
			manga_id: <?=$manga['id'];?>,
			comment_id: <?=Request::get(InterFaceRequest::COMMENT, 0);?>,
			ajax_url: "<?=RouteMap::get('comment');?>",
			editor_theme: 'ttmanga',
			meme_sources: <?=Smiley::build_meme_source();?>
		});


		$(document).ready(function() {
			role_click('follow', function(self) {
				request_bookmark(<?=$manga['id'];?>);
			});
		});

	})();
</script>

<?php View::render_theme('layout.footer'); ?>