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
					<span><?=lang('register', 'txt_title');?></span>
				</div>
			<?php
				if($error)
				{
					echo '<div class="alert_error">'.$error.'</div>';
				}
				else if($success)
				{
					echo '<div class="alert_success">'.$success.' <a href="'.RouteMap::get('login').'">'.lang('register', 'txt_login_now').'</a>.</div>';
				}
	
			?>

				<div class="input-box <?=in_array($code_error, ['error_username_empty', 'error_username_exist', 'error_username_length', 'error_username_char']) ? 'error' : '';?>">
					<i class="fa fa-user form-control-feedback"></i>
					<input type="text" placeholder="<?=lang('register', 'txt_username');?>" name="<?=Auth::INPUT_USERNAME;?>" value="<?=_echo($username);?>" required>
				</div>

				<div class="input-box <?=in_array($code_error, ['error_password_length']) ? 'error' : '';?>">
					<i class="fa fa-lock form-control-feedback"></i>
					<input type="password" placeholder="<?=lang('register', 'txt_password');?>" name="<?=Auth::INPUT_PASSWORD;?>" value="<?=_echo($password);?>" required>
				</div>

				<div class="input-box <?=in_array($code_error, ['error_password_reinput']) ? 'error' : '';?>">
					<i class="fa fa-lock form-control-feedback"></i>
					<input type="password" placeholder="<?=lang('register', 'txt_confirm_password');?>" name="<?=Auth::INPUT_REPASSWORD;?>" value="<?=_echo($rePassword);?>" required>
				</div>

				<div class="input-box <?=in_array($code_error, ['error_email_format', 'error_email_exist']) ? 'error' : '';?>">
					<i class="fa fa-envelope form-control-feedback"></i>
					<input type="email" placeholder="<?=lang('register', 'txt_email');?>" name="<?=Auth::INPUT_EMAIL;?>" value="<?=_echo($email);?>" required>
				</div>

				<div class="input-box <?=in_array($code_error, ['error_registerkey_not_exist', 'error_registerkey_is_used']) ? 'error' : '';?>">
					<i class="fa fa-key form-control-feedback"></i>
					<input type="text" placeholder="<?=lang('register', 'txt_register_key');?>" name="<?=Auth::INPUT_REGISTER_KEY;?>" value="<?=_echo($registerKey);?>" required>
				</div>

				<div class="input-box button">
					<input type="submit" name="<?=authController::SUBMIT_NAME;?>" value="<?=lang('register', 'txt_submit');?>">
				</div>
				<div class="option"><?=lang('register', 'txt_already_account');?> <a href="<?=RouteMap::get('login');?>"><?=lang('register', 'txt_login_now');?></a></div>
			</form>
		</div>

	</body>
</html>