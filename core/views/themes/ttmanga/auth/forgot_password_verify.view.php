<!DOCTYPE html>
<html lang="en" dir="ltr">
	<head>
		<meta charset="UTF-8">
    	<title><?=$title;?></title>
   		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
		<link rel="icon" type="image/x-icon" href="<?=APP_URL;?>/assets/favico.ico">
		<link rel="shortcut icon" type="image/x-icon" href="<?=APP_URL;?>/assets/favico.ico">
		<link rel="stylesheet" type="text/css" href="<?=APP_URL;?>/assets/css/font-awesome/css/all.css" />

		<link rel="stylesheet" type="text/css" href="<?=APP_URL;?>/assets/css/login.css?t=<?=$_version;?>" />
	</head>
	<body>

		<div class="container">
			<img class="logo" src="<?=APP_URL;?>/assets/images/logo.png">
			<form class="form-box" method="POST">
				<div class="title">
					<span><?=lang('forgot_password', 'txt_title');?></span>
				</div>

			<?php
				if($error)
				{
					echo '<div class="alert_error">'.$error.'</div>';
				}
				else if($success)
				{
					echo '<div class="alert_success">'.$success.'</div>';
				}
			?>

				<div class="input-box <?=in_array($code_error, ['error_password_length']) ? 'error' : '';?>">
					<i class="fa fa-lock form-control-feedback"></i>
					<input type="password" placeholder="<?=lang('forgot_password', 'txt_password');?>" name="<?=Auth::INPUT_PASSWORD;?>" value="<?=_echo($password);?>" required>
				</div>

				<div class="input-box <?=in_array($code_error, ['error_password_reinput']) ? 'error' : '';?>">
					<i class="fa fa-lock form-control-feedback"></i>
					<input type="password" placeholder="<?=lang('forgot_password', 'txt_confirm_password');?>" name="<?=Auth::INPUT_REPASSWORD;?>" value="<?=_echo($rePassword);?>" required>
				</div>

				<div class="input-box button">
					<input type="submit" name="<?=authController::SUBMIT_NAME;?>" value="<?=lang('forgot_password', 'txt_submit_change_password');?>">
				</div>
				<div class="option"><?=lang('forgot_password', 'txt_already_account');?> <a href="<?=RouteMap::get('login');?>"><?=lang('forgot_password', 'txt_login_now');?></a></div>
			</form>
		</div>

	</body>
</html>