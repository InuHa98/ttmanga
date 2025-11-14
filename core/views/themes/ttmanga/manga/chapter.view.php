<?php View::render_theme('layout.header', compact('title')); ?>

<?=themeController::load_css('css/chapter.css'); ?>


<?php

$options_page = null;
$chapter_list = null;

if($list_chapters)
{
	foreach($list_chapters as $item)
	{
		$chapter_list .= '<li'.($chapter['id'] == $item['id'] ? ' class="active"' : null).' data-id="'.$item['id'].'">'._echo($item['name']).'</li>';
	}
}

if($images)
{
	$total_page = count($images);
	for($i = 0; $i < $total_page; $i++)
	{
		$options_page .= '<option value="'.$i.'">'.($i + 1).'</option>';
	}
}
$url_pre_chapter = $pre_chapter ? RouteMap::get('chapter', ['id_manga' => $manga['id'], 'id_chapter' => $pre_chapter['id']]) : null;
$url_next_chapter = $next_chapter ? RouteMap::get('chapter', ['id_manga' => $manga['id'], 'id_chapter' => $next_chapter['id']]) : null;


?>

<div class="section-sub-header">
	<div class="container">
		<div class="chapter-infomation">
			<a class="chapter-infomation__manga" href="<?=RouteMap::get('manga', ['id' => $manga['id']]);?>"><?=_echo($manga['name']);?></a>
			<div class="chapter-infomation__chapter"><?=_echo($chapter['name']);?></div>
			<div class="chapter-infomation__desc">
				<span>Đăng bởi: <a href="<?=RouteMap::get('profile', ['id' => $uploader['id']]);?>"><?=$uploader['username'];?></a></span>
				<span>Ngày đăng: <?=_time($chapter['created_at']);?></span>
				<span>Số trang: <?=number_format(count($images), 0, ',', '.');?></span>
			</div>
		</div>
    </div>
</div>

<div class="show-chapter-list">
	<i class="fas fa-list"></i>
	<span class="d-none d-md-inline-block">Danh sách chương</span>
</div>
<div class="chapter-list">
	<div class="chapter-list__header">
		<div class="text">Số chương hiện tại: <b><?=number_format(count($list_chapters), 0, ',', '.');?></b></div>
		<div class="sort-list">
			<i class="fas fa-sort-amount-up"></i>
		</div>
	</div>

	<div class="chapter-list__view">
		<span class="label">Kiểu đọc</span>
		<div class="form-control">
			<div class="form-switch">
				<input type="checkbox" id="select-read-mode" value="1" <?=($read_mode == 1 ? 'checked' : null);?>>
				<label for="select-read-mode">Hiển thị từng trang truyện</label>
			</div>
		</div>
	</div>

	<div class="chapter-list__team">
		<span class="label">Nhóm dịch</span>
		<select class="select-team js-custom-select w-100">
			<option value="0" selected><?=$team_name;?></option>
		<?php if($other_teams): foreach($other_teams as $val): ?>
			<option value="<?=$val['url'];?>"><?=$val['team_name'];?></option>
		<?php endforeach; endif; ?>
		</select>
	</div>
	<ul class="chapter-list__items"><?=$chapter_list;?></ul>
	<div class="chapter-list__navigation">
		<a class="chapter-list__navigation-item <?=(!$url_pre_chapter ? 'disabled' : null);?>" href="<?=$url_pre_chapter;?>">
			<i class="fas fa-chevron-double-left"></i>
			<span>Chương trước</span>
		</a>
		<a class="chapter-list__navigation-item <?=(!$url_next_chapter ? 'disabled' : null);?>" href="<?=$url_next_chapter;?>">
			<span>Chương kế</span>
			<i class="fas fa-chevron-double-right"></i>
		</a>
	</div>
	<div class="chapter-list__report">
		<i class="fas fa-exclamation-triangle"></i>
		<a href="<?=RouteMap::build_query([reportController::PARAM_CHAPTER_ID => $chapter['id']], 'report', ['block' => reportController::BLOCK_SUBMIT]);?>">Báo lỗi chapter</a>
	</div>
	<div class="hide-chapter-list">
		<i class="fas fa-list"></i>
		<span>Ẩn danh sách chương</span>
	</div>
</div>

<div class="chapter-navigation">
	<div class="container">
		<div class="navigation-item">
			<span>Chương</span>
			<select class="select-chapter js-custom-select" data-min-width="200px"></select>
			<div class="show-chapter-list">
				<i class="fas fa-list"></i>
			</div>
		</div>
	<?php if($read_mode == 1): ?>
		<div class="navigation-item">
			<span>Page</span>
			<select class="select-page js-custom-select" data-align="center" data-placeholder="Page" data-min-width="68px"><?=$options_page;?></select>
			<div class="pre-page">
				<i class="fas fa-arrow-left"></i>
			</div>
			<div class="next-page">
				<i class="fas fa-arrow-right"></i>
			</div>
		</div>
	<?php endif; ?>
		<div class="navigation-chapter">
			<a class="navigation-chapter__pre <?=(!$url_pre_chapter ? 'disabled' : null);?>" href="<?=$url_pre_chapter;?>">Chap trước</a>
			<a class="navigation-chapter__next <?=(!$url_next_chapter ? 'disabled' : null);?>" href="<?=$url_next_chapter;?>">Chap kế</a>
		</div>
	</div>
</div>

<div class="container">
	<div class="row">
		<div class="image-loading">
	<?php if($read_mode != 1): ?>
		<?php
		if($images)
		{
			$total_images = count($images);
			for($i = 0; $i < $total_images; $i++)
			{
				echo '<p><img src="" /></p>';
			}
		}
		?>
	<?php else: ?>
			<div id="scroll_current_image" class="read-one-page">
				<img id="current_image" src="" />
				<div class="pre-page-inline"></div>
				<div class="next-page-inline"></div>
			</div>
			<div class="msg-error">
				<div class="msg-error__text">Hiện giờ chỉ có chừng này chương à. Bạn thử chọn nhóm khác ở khung phía trên xem!</div>
				<div class="msg-error__image">
					<img src="<?=APP_URL;?>/assets/images/model.png" />
				</div>
			</div>
	<?php endif; ?>
			<p>
				<div id="icon_loading" class="animation-loading">
					<div></div>
					<div></div>
					<div></div>
					<div></div>
					<div></div>
				</div>
			</p>
		</div>
	</div>
</div>

<div class="container description">
	<div>Hầu hết các truyện đọc từ phải qua trái. Đối với VnComic (truyện Việt Nam), Manhwa (truyện Hàn Quốc) và Comic (truyện châu Âu + Mĩ) thì đọc từ trái qua phải.</div>
	<div>Có thể chọn kiểu đọc một trang/nhiều trang ở phía trên. Nếu chương bạn đang tìm không có, hãy thử chuyển sang nhóm dịch khác.</div>
</div>

<div class="chapter-navigation">
	<div class="container">
		<div class="navigation-item">
			<span>Chương</span>
			<select class="select-chapter js-custom-select" data-min-width="200px"></select>
			<div class="show-chapter-list">
				<i class="fas fa-list"></i>
			</div>
		</div>

	<?php if($read_mode == 1): ?>
		<div class="navigation-item">
			<span>Page</span>
			<select class="select-page js-custom-select" data-placeholder="Page" data-min-width="68px"><?=$options_page;?></select>
			<div class="pre-page">
				<i class="fas fa-arrow-left"></i>
			</div>
			<div class="next-page">
				<i class="fas fa-arrow-right"></i>
			</div>
		</div>
	<?php endif; ?>

		<div class="navigation-chapter">
			<a class="navigation-chapter__pre <?=(!$url_pre_chapter ? 'disabled' : null);?>" href="<?=$url_pre_chapter;?>">Chap trước</a>
			<a class="navigation-chapter__next <?=(!$url_next_chapter ? 'disabled' : null);?>" href="<?=$url_next_chapter;?>">Chap kế</a>
		</div>
	</div>
</div>

<div class="section-comment">
	<div class="container">
		<div class="title-comment">Bình luận (<span class="total-comment">0</span>)</div>
	<?php if(Auth::$isLogin == true): ?>
		<?php if(UserPermission::has('user_comment')): ?>
		<form class="comment-editor px-4" method="POST">
			<div class="form-group">
				<div class="form-control">
					<textarea class="form-textarea mt-4" name="text" placeholder="Nhập bình luận..." rows="1" style="height: 50px"></textarea>
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
			<div class="alert alert--error">Bạn đã bị cấm bình luận</div>
		<?php endif; ?>
	<?php else: ?>
		<div class="error-login">Vui lòng <a href="<?=RouteMap::get('login');?>">đăng nhập</a> để có thể bình luận</div>
	<?php endif; ?>
		<div class="comment-container"></div>
		<div class="comment-loading">
			<div class="animation-spinner">
				<div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>
			</div>
			<span>Đang tải bình luận...</span>
		</div>
		<div class="comment-pagination"></div>
	</div>
</div>


<script type="text/javascript" src="<?=APP_URL;?>/assets/js/tinymce/tinymce.min.js?v=<?=$_version;?>"></script>
<script type="text/javascript" src="<?=APP_URL;?>/assets/js/form-validator.js?v=<?=$_version;?>"></script>
<script type="text/javascript" src="<?=APP_URL;?>/assets/js/swiped-events.js?v=<?=$_version;?>"></script>
<?=themeController::load_js('js/comments.js');?>

<script type="text/javascript" src="<?=APP_URL;?>/assets/js/wasm_exec.js"></script>
<script type="text/javascript">

	const go = new Go();
	(async () => {
		const result = await WebAssembly.instantiateStreaming(fetch("<?=APP_URL;?>/assets/wasm/anti-ADBlock.wasm"), go.importObject);
		go.run(result.instance);

		var lstImages = <?=json_encode($images); ?>;
		var lstImagesLoaded = new Array();

		var icon_loading = $('#icon_loading');
		var class_select_page = '.select-page',
			class_select_chapter = '.select-chapter',
			class_select_team = '.select-team',
			select_read_mode = '#select-read-mode';

		new Comment({
			manga_id: <?=$manga['id'];?>,
			chapter_id: <?=$chapter['id'];?>,
			comment_id: <?=Request::get(InterFaceRequest::COMMENT, 0);?>,
			ajax_url: "<?=RouteMap::get('comment');?>",
			editor_theme: 'ttmanga',
			meme_sources: <?=Smiley::build_meme_source();?>
		});

	<?php if($read_mode != 1): ?>

		var container_images = $('.image-loading');

		if(lstImages.length  < 1)
		{
			icon_loading.hide();
		}

		function loadNextImage() {
			let nextImg = container_images.find('img[src=""]:first');
			if (currImage < lstImages.length && nextImg.length) {
				nextImg.attr('src', window.trim(lstImages[currImage++]));
			}
			if (currImage >= lstImages.length) {
				icon_loading.hide();
			}
		}

		container_images.find("img").on("load", function() {

			var srcImage = window.trim($(this).attr('src'));
			if ($.inArray(srcImage, lstImagesLoaded) < 0) {
				lstImagesLoaded.push(srcImage);
			}
			$(this).off("click");
			loadNextImage()
		}).on('error', function() {

			var srcImage = $(this).attr('src');
			if ($.inArray(srcImage, lstImagesLoaded) >= 0) {
				let index = lstImagesLoaded.indexOf(srcImage);
				if (index > -1) {
					lstImagesLoaded.splice(index, 1);
				}
			}

			$(this).off("click").on("click", function() {
				let srcImage = $(this).attr('src');
				if($.inArray(srcImage, lstImagesLoaded) < 0) {
					$(this).removeAttr("src");
					$(this).attr('src', window.trim(srcImage));
				}
			});

			loadNextImage()
		});


		var currImage = 0;
		container_images.find('img[src=""]:first').attr('src', window.trim(lstImages[currImage++]));


	<?php else: ?>

		var msg_error = $('.msg-error'),
			current_image = $('#current_image'),
			nxtImage,
			preImage;

		function isNumber(n) {
			return !isNaN(parseFloat(n)) && isFinite(n);
		}


		var currImage = 0;
		var start = location.href.indexOf('#');

		if (start > 0)
		{
			start++;
			currImage = (location.href.substr(start, location.href.length - start)) - 1;
			if (currImage < 1 || !isNumber(currImage))
			{
				currImage = 0;
			}

			var max_page = $(class_select_page).find('option').length - 1;
			if(currImage > max_page)
			{
				currImage = max_page;
			}
		}
		
		function scrollToImage() {
		    const rect = current_image[0].getBoundingClientRect();
		    let scrollTop;

		    if (rect.height <= window.innerHeight) {
		       
		        scrollTop = window.scrollY + rect.top + rect.height / 2 - window.innerHeight / 2;
		    } else {
		      
		        scrollTop = window.scrollY + rect.top - 30;
		    }

		    scrollTop = Math.max(0, Math.min(scrollTop, document.body.scrollHeight - window.innerHeight));

		    window.scrollTo({ top: scrollTop, behavior: 'smooth' });
		}

		function onImageLoad() {
			$(this).removeClass('error');
			icon_loading.hide();

			scrollToImage();
		}

		function onImageError() {
			$(this).addClass('error');
			icon_loading.hide();
		}

		function debounce(func, ms) {
			let timer;
			return function(...args) {
				clearTimeout(timer);
				timer = setTimeout(() => func.apply(this, args), ms);
			}
		}

		const debouncedPrevious = debounce(Previous, 150);
		const debouncedNext = debounce(Next, 150);

		current_image.on('load', onImageLoad).on('error', onImageError);

		SetImage();

		function SetImage()
		{
			if(lstImages.length < 1)
			{
				icon_loading.hide();
				return;
			}
			
			icon_loading.show();
			current_image.show();
			const temp_image = nxtImage || preImage || null
			if (temp_image) {
					temp_image
						.off('load error')
						.on('load', onImageLoad)
						.on('error', onImageError);					
				
				current_image.replaceWith(temp_image)
				current_image = temp_image
				if (temp_image[0].complete) {
					icon_loading.hide();
					scrollToImage()
				}
			} else {
				current_image.attr("src", window.trim(lstImages[currImage]))
			}

			current_image.off('click').on('click', function() {
			if (!$(this).hasClass('error')) {
				return debouncedNext();
			}
			
			$(this).attr('src', window.trim($(this).attr('src')));
		});

			$(class_select_page).val(currImage).change()

			msg_error.removeClass('show');

			if ($.inArray(lstImages[currImage], lstImagesLoaded) < 0) {
				lstImagesLoaded.push(lstImages[currImage]);
			}

			PreloadImage();
		}

		function PreloadImage()
		{
			if (typeof lstImages[currImage + 1] != "undefined")
			{
				nxtImage = $('<img />').attr('src', window.trim(lstImages[currImage + 1]));
			}

			if (typeof lstImages[currImage - 1] != "undefined")
			{
				preImage = $('<img />').attr('src', window.trim(lstImages[currImage - 1]));
			}
		} 

		function Next()
		{
			if (typeof lstImages[currImage + 1] != "undefined")
			{
				preImage = null
				currImage++;
				$(class_select_page).val(currImage).change()
			}
			else
			{
				var nextOption = $(class_select_chapter).find('option:selected').prev();
				if (nextOption.length)
				{
					location.href = nextOption.val();
				}
				else
				{
					current_image.hide();
					msg_error.addClass('show');
				}
			}
		}

		function Previous()
		{
			if (currImage > 0)
			{
				nxtImage = null
				currImage--;
				$(class_select_page).val(currImage).change()
			}
			else
			{
				var previousOption = $(class_select_chapter).find('option:selected').next();
				if (previousOption.length)
				{
					location.href = previousOption.val();
				}
				else
				{
					$.toastShow("Đây đã là chương đầu tiên!!!", {
						type: 'warning',
						timeout: 3000
					});
				}
			}
		}

		$(document).on("keyup", function (event) {
			if (event.keyCode === 37) debouncedPrevious();
			else if (event.keyCode === 39) debouncedNext();
		}); 

		document.body.addEventListener("swiped-left", () => {
			debouncedNext()
		});

		document.body.addEventListener("swiped-right", () => {
			debouncedPrevious()
		});

		$('.next-page, .next-page-inline').on('click', function(e) {
			e.isPropagationStopped();
			debouncedNext();
		});

		$('.pre-page, .pre-page-inline').on('click', function(e) {
			e.isPropagationStopped();
			debouncedPrevious();
		});

		$(class_select_page).on('change', function () {
			currImage = parseInt($(this).val())
			window.location.hash = currImage + 1;
		});

		window.addEventListener("hashchange", () => {
			current_image.attr('src', '')
			SetImage()
		});

	<?php endif; ?>

		$(document).ready(function() {

			let background = null;

			$(select_read_mode).on('change', function() {
				document.cookie = "<?=App::COOKIE_READ_MODE;?>=" + ($(this).is(':checked') ? 1 : 0) + "; expires=Thu, 2 Aug <?=(date('Y') + 10);?> 20:47:11 UTC;path=/";
				window.location.reload();
			});

			$(class_select_chapter).on('change', function() {
				location.href = $(this).val();
			});
			
			$(class_select_team).on('change', function() {
				if ($(this).val() != 0) {
					location.href = $(this).val();
				}
			});

			$('.show-chapter-list, .hide-chapter-list').on('click', function() {
				
				const chapter_list = $('.chapter-list');
				chapter_list.toggleClass('show');

				
				if(chapter_list.hasClass('show')) {
					const active_chapter = chapter_list.find(".chapter-list__items > li.active");
					active_chapter.length && active_chapter[0].scrollIntoView({ behavior: "smooth", block: "center" });
					background = $('<div style="background: #fff; opacity: 0; position: absolute; top: 0; bottom: 0; left: 0; right: 0; z-index: 999; transition: 1s opacity ease-in-out;"></div>');
					$('body').append(background);
					background.css({
						opacity: 0.4
					});
					background.on('click', () => {
						$(this).click();
					});
				} else {
					background.remove();
				}
			});

			$('.sort-list').on('click', function() {
				if($(this).hasClass('asc'))
				{
					$(this).removeClass('asc').addClass('desc');
				}
				else
				{
					$(this).removeClass('desc').addClass('asc');
				}
				$('.chapter-list').find('ul').toggleClass('reverse');
				const active_chapter = $('.chapter-list').find(".chapter-list__items > li.active");
				active_chapter.length && active_chapter[0].scrollIntoView({ behavior: "smooth", block: "center" });
			});

			$('.chapter-list__items').find('li').each(function() {
				var id = $(this).data('id'),
					name = htmlEntities($(this).html()),
					isActive = $(this).hasClass('active');
				if(id)
				{
					$(class_select_chapter).append('<option value="'+id+'" '+(isActive ? 'selected' : '')+'>'+name+'</option>');
				}
			});

			$('.chapter-list__items').on('click', 'li', function() {
				if (!$(this).hasClass('active')) {
					location.href = $(this).data('id');
				}
				
			});
		});


	})();
</script>

<?php View::render_theme('layout.footer'); ?>