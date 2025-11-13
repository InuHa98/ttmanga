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
        <div class="mb-2"><?=$txt_description;?></div>

		<div class="form-group limit--width">
			<label class="control-label">Name</label>
			<div class="form-control">
				<input class="form-input" name="<?=adminPanelController::INPUT_NAME;?>" placeholder="Tên thể loại" type="text" value="<?=_echo($name);?>">
			</div>
		</div>
		<div class="form-group">
			<label class="control-label">Description</label>
			<div class="form-control">
				<textarea class="form-textarea" name="<?=adminPanelController::INPUT_TEXT;?>" placeholder="Mô tả"><?=_echo($text);?></textarea>
			</div>
		</div>

		<div class="d-flex justify-content-end my-4">
			<button type="submit" class="btn btn--round pull-right">Lưu lại</button>
			<a href="<?=Request::referer(RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_SYSTEM, 'block' => adminPanelController::BLOCK_GENRES]));?>" class="btn btn--round btn--gray pull-right">Trở lại</a>
		</div>
	</form>