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
        <div class="mb-4">
            <?=$txt_description;?>
        </div>

        <div class="form-group limit--width">
            <label class="control-label">Name</label>
            <div class="form-control">
                <input class="form-input" name="<?=adminPanelController::INPUT_NAME;?>" placeholder="Tên cấu hình" type="text" value="<?=_echo($name);?>">
            </div>
        </div>
        <div class="form-group">
            <label class="control-label">Cookie</label>
            <div class="form-control">
                <textarea class="form-textarea" name="<?=adminPanelController::INPUT_COOKIE;?>" placeholder="Cookie google account"><?=_echo($cookie);?></textarea>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label">Note</label>
            <div class="form-control">
                <textarea class="form-textarea" name="<?=adminPanelController::INPUT_NOTE;?>" placeholder="Ghi chus"><?=_echo($note);?></textarea>
            </div>
        </div>

    <div class="d-flex justify-content-end align-items-center mt-4">
        <a href="<?=Request::referer(RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_TEAM, 'block' => adminPanelController::BLOCK_CONFIG_UPLOAD]));?>" class="btn btn--round btn--gray pull-right">Trở lại</a>
        <button type="submit" class="btn btn--round pull-right">Lưu lại</button>
    </div>
</form>
