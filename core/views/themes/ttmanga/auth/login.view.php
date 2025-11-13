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
					<span><?=lang('login', 'txt_title');?></span>
				</div>
				<input type="hidden" name="referer" value="<?=$referer;?>">
			<?php
				if($error)
				{
					echo '<div class="alert_error">'.$error.'</div>';
				}
			?>

				<div class="input-box <?=$error ? 'error' : '';?>">
					<i class="fa fa-user form-control-feedback"></i>
					<input type="text" placeholder="<?=lang('login', 'txt_username');?>" name="<?=Auth::INPUT_USERNAME;?>" value="<?=_echo($username);?>" required>
				</div>
				<div class="input-box <?=$error ? 'error' : '';?>">
					<i class="fa fa-lock form-control-feedback"></i>
					<input type="password" placeholder="<?=lang('login', 'txt_password');?>" name="<?=Auth::INPUT_PASSWORD;?>" value="<?=_echo($password);?>" required>
				</div>
				<div class="option_div">
					<div class="check_box">
						<span class="form-check">
							<input type="checkbox" id="stay_login" name="<?=Auth::INPUT_STAYLOGIN;?>" <?=($stay ? 'checked' : null);?>>
							<label for="stay_login"></label>
						</span>
						<span><?=lang('login', 'txt_stay');?></span>
					</div>
					<div class="forget_div">
						<a href="<?=RouteMap::get('forgot_password');?>"><?=lang('login', 'txt_forgot');?></a>
					</div>
				</div>
				<div class="input-box button">
					<input type="submit" name="<?=authController::SUBMIT_NAME;?>" value="<?=lang('login', 'txt_submit');?>">
				</div>
				<div class="option"><?=lang('login', 'txt_no_account');?> <a href="<?=RouteMap::get('register');?>"><?=lang('login', 'txt_signup');?></a></div>
			</form>
		</div>
	</body>
</html>