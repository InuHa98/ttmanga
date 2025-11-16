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


	<form id="form-validate" method="POST">
        <?=Security::insertHiddenToken();?>
        <div class="my-2"><?=$txt_description;?></div>

			<div class="form-group limit--width">
				<label class="control-label--mini">Tên</label>
                <div class="form-control">
                    <input class="form-input" name="<?=profileController::INPUT_SMILEY_NAME;?>" placeholder="Tên nhãn dán" type="text" value="<?=_echo($name);?>">
                </div>
			</div>
            <div class="form-group  limit--width">
				<label class="control-label--mini">Link ảnh</label>
                <div class="form-control">
                    <div class="input-group">
                        <input class="form-input" name="<?=profileController::INPUT_SMILEY_IMAGES;?>[]" placeholder="https://" type="text" />
                        <div class="input-group-append">
                            <button type="button" role="btn-add-input" class="btn btn-outline-warning btn-dim"><i class="fa fa-plus"></i></button>
                        </div>
                    </div>
                </div>
            </div>


            <div class="smiley-edit-box">
            <?php if(isset($images)): ?>
                <?php foreach($images as $image): ?>
                <div class="smiley-edit-box__item">
                    <img src="<?=_echo($image);?>">
                    <div class="form-group">
                        <div class="input-group">
                            <input class="form-input" name="<?=profileController::INPUT_SMILEY_IMAGES;?>[]" placeholder="https://" type="text" value="<?=_echo($image);?>">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-gray btn-dim DraggableListJS-handle"><i class="fas fa-grip-vertical"></i></button>
                                <button type="button" role="btn-remove-input" class="btn btn-outline-danger btn-dim"><i class="fa fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
            </div>
		<div class="d-flex justify-content-end my-4">
			<button type="submit" class="btn btn--round pull-right">Lưu lại</button>
            <a href="<?=Request::referer(RouteMap::get('profile', ['id' => 'me', 'block' => profileController::BLOCK_SMILEY]));?>" class="btn btn--round btn--gray pull-right">Trở lại</a>
		</div>
	</form>


<link rel="stylesheet" href="<?=assets('css/draggable-list.css');?>">
<script type="text/javascript" src="<?=assets('script/jquery-sortable.js');?>"></script>

<script type="text/javascript">
    (function() {
        $(document).ready(function() {

            $('.smiley-edit-box').sortable({
                scroll: true,
                scrollSensitivity: 60,
                scrollSpeed: 10,
                forceFallback: true,
                swap: false,
                invertSwap: true,
                swapThreshold: 0.65,
                animation: 150,
                swapClass: 'swap-highlight',
                ghostClass: 'dragging-item',
                handle: '.handle-drag',
            });

            $('[role="btn-add-input"]').on('click', function() {
                let input = $(this).parents('.input-group').find('textarea');

                let value = input && input.val().trim();

                if(value == '') {
                    return;
                }

                value.split("\n").reverse().filter(link => link.match(/(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g)).forEach(link => {
                    let item = $(`
                    <div class="smiley-edit-box__item">
                        <img src="">
                        <div class="form-group">
                            <div class="input-group">
                                <input class="form-input" name="<?=profileController::INPUT_SMILEY_IMAGES;?>[]" placeholder="https://" type="text">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-gray btn-dim DraggableListJS-handle"><i class="fas fa-grip-vertical"></i></button>
                                    <button type="button" role="btn-remove-input" class="btn btn-outline-danger btn-dim"><i class="fa fa-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>`);
                    
                    item.find('img').attr('src', link);
                    item.find('input').val(link);
                    $('.smiley-edit-box').prepend(item);
                });


                input.val('');
                $('.smiley-edit-box').DraggableListJS();
            });

            $(document).on('click', '[role="btn-remove-input"]', function() {
                let item = $(this).parents('.smiley-edit-box__item');
                item.remove();
            });

        });
    })();
</script>