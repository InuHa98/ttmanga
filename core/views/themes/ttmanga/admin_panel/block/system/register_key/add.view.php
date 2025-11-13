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
        <div class="mb-2">Tạo key mới</div>
			<div class="form-group limit--width">
				<label class="control-label">Mã</label>
                <div class="form-control">
                    <input class="form-input" name="<?=adminPanelController::INPUT_KEY;?>" placeholder="Bỏ trống để tạo key ngẫu nhiên" type="text" value="<?=_echo($key);?>">
                </div>
			</div>

            <div class="form-group limit--width">
				<label class="control-label">Số lượng</label>
                <div class="form-control">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" role="btn-minus" class="btn btn-outline-light btn-dim"><i class="fa fa-minus"></i></button>
                        </div>
                        <input class="form-input only-number" name="<?=adminPanelController::INPUT_QUANTITY;?>" placeholder="Số lượng mã" type="text" value="<?=_echo($quantity);?>">
                        <div class="input-group-append">
                            <button type="button" role="btn-plus" class="btn btn-outline-light btn-dim"><i class="fa fa-plus"></i></button>
                        </div>                        
                    </div>
                </div>
            </div>

            <div class="form-group">
				<label class="control-label">Ghi chú</label>
                <div class="form-control">
                    <textarea class="form-textarea" placeholder="Ghi chú" name="<?=adminPanelController::INPUT_NOTE;?>"><?=_echo($note);?></textarea>
                </div>
            </div>

		<div class="d-flex justify-content-end my-4">
			<button type="submit" class="btn btn--round pull-right">Tạo Key</button>
            <a href="<?=Request::referer(RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_REGISTER_KEY]));?>" class="btn btn--round btn--gray pull-right">Trở lại</a>
		</div>
	</form>
<script type="text/javascript">
    (function() {
        $(document).ready(function() {
            $('[role="btn-add-input"]').on('click', function() {
                let input_group = $(this).parents('.input-group');
                let new_input = input_group.clone();
                new_input.find('input').val('');
                new_input.find('.input-group-append').html('<button type="button" role="btn-remove-input" class="btn btn-outline-danger btn-dim"><i class="fa fa-trash"></i></button>');
                input_group.parent().append(new_input);
            });

            $(document).on('click', '[role="btn-remove-input"]', function() {
                let input_group = $(this).parents('.input-group');
                input_group.remove();
            });
        });
    })();
</script>