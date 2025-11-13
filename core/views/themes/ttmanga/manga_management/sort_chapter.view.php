<?=themeController::load_css('css/manga.css'); ?>
<div class="section-sub-header">
	<div class="container">
        <span>Sắp xếp chương</span>
    </div>
</div>

<div class="container">

	<div class="my-2">
		<a href="<?=RouteMap::get('manga_management', ['action' => mangaManagementController::ACTION_DETAIL, 'id' => $manga['id']]);?>"><i class="fas fa-chevron-left"></i> <?=_echo($manga['name']);?></a>
	</div>

	<div class="row">

		<div class="col-12">
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
		</div>

		<div class="col-lg-9">
			<div class="d-flex justify-content-between align-items-center gap-2 flex-wrap my-2">
				<span>Có tất cả <b><?=number_format(count($chapters), 0, ',', '.');?></b> chương.</span>
			</div>
            <form id="form-validate" method="POST">
                <?=Security::insertHiddenToken();?>
			<?php if($chapters): ?>
				<ul class="sort-chapter-box">
                <?php $index = count($chapters); 
                    foreach($chapters as $chapter): ?>
                    <li data-id="<?=$chapter['id'];?>" data-index="<?=$index--;?>">
                        <input type="hidden" name="<?=mangaManagementController::INPUT_ID;?>[]" value="<?=$chapter['id'];?>" />
                        <span class="index"><?=$chapter['index'];?></span>
                        <span class="name"><?=_echo($chapter['name']);?></span>
                        <button type="button" class="btn btn-outline-gray btn-dim handle-drag"><i class="fas fa-grip-vertical"></i></button>
                    </li>
                <?php endforeach; ?>
				</ul>

                <div class="d-flex justify-content-end my-4">
                    <button id="reset-sort" type="button" class="btn btn--round btn--gray">Huỷ</button>
                    <button type="submit" class="btn btn--round btn--info">Lưu lại</button>
                </div>

			<?php else: ?>
				<div class="alert alert--warning">Chưa có chương truyện nào!!!</div>
			<?php endif; ?>
            </form>
		</div>
		<div class="col-lg-3">
			<div class="info-box mt-4">Kéo thả chương truyện đến vị trí muốn sắp xếp. Có thể chọn nhiều chương để kéo thả cùng lúc.</div>
		</div>
	</div>
</div>

<script type="text/javascript" src="<?=assets('js/jquery-sortable.js');?>"></script>
<script type="text/javascript">
	$(document).ready(() => {

        const sortContainer = $('.sort-chapter-box');
        const originItems = sortContainer.children().toArray();

        sortContainer.sortable({
            scroll: true,
            scrollSensitivity: 60,
            scrollSpeed: 10,
            forceFallback: true,
            swap: false,
            invertSwap: true,
            multiDrag: true,
            swapThreshold: 0.65,
            animation: 150,
            swapClass: 'swap-highlight',
            ghostClass: 'dragging-item',
            selectedClass: 'selected-drag',
            fallbackTolerance: 3,
            handle: '.handle-drag',
            onEnd: (evt) => {
                const $items = $(evt.to).find('li');
                const total = $items.length;
                $items.each(function(i) {
                    const index = total - i;
                    $(this).attr('data-index', index);
                    if (Math.abs(Number($(this).find('.index').html()) - index) >= 2) {
                        $(this).addClass('moved');
                    } else {
                        $(this).removeClass('moved');
                    }
                });
            }
        });

        $('#reset-sort').on('click', function() {
            sortContainer.empty().append(originItems);
            sortContainer.find('li').removeClass('moved');
            const $items = sortContainer.find('li');
            $items.each(function() {
                $(this).attr('data-index', $(this).find('.index').html());
                $(this).removeClass('moved');
            });
        });
	})
</script>