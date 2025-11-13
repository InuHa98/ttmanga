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
    <div class="form-group limit--width">
        <label class="control-label">Facebook</label>
        <div class="form-control">
            <input class="form-input" name="<?=teamController::INPUT_FACEBOOK;?>" placeholder="Liên kết fanpage facebook" type="text" value="<?=_echo($facebook);?>">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label">Giới thiệu</label>
        <div class="form-control">
            <textarea class="form-textarea" name="<?=teamController::INPUT_DESC;?>" placeholder="Giới thiệu nhóm dịch" type="text"><?=_echo($desc);?></textarea>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label">Nội quy upload</label>
        <div class="form-control">
            <textarea id="rules" class="form-textarea" name="<?=teamController::INPUT_RULE;?>" placeholder="Quy định khi đăng truyện" type="text"><?=_echo($rule);?></textarea>
        </div>
    </div>
    <div class="d-flex justify-content-end my-4">
        <button type="submit" class="btn btn--round pull-right">Lưu lại</button>
    </div>
</form>