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
					<span>Cập nhật tài khoản</span>
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

				<?=$insertHiddenToken;?>
				<div class="input-box <?=in_array($code_error, ['error_username_empty', 'error_username_exist', 'error_username_length', 'error_username_char']) ? 'error' : '';?>">
					<i class="fas fa-user form-control-feedback"></i>
					<input type="text" placeholder="Tên tài khoản mới" name="<?=Auth::INPUT_USERNAME;?>" value="<?=_echo($username);?>" required>
				</div>

				<div class="input-box <?=in_array($code_error, ['error_email_format', 'error_email_exist']) ? 'error' : '';?>">
					<i class="fa fa-envelope form-control-feedback"></i>
					<input type="text" placeholder="Địa chỉ email mới" name="<?=Auth::INPUT_EMAIL;?>" value="<?=_echo($email);?>" required>
				</div>

				<div class="input-box <?=in_array($code_error, ['error_password_length']) ? 'error' : '';?>">
					<i class="fa fa-lock form-control-feedback"></i>
					<input type="password" placeholder="Mật khẩu mới" name="<?=Auth::INPUT_PASSWORD;?>" value="" required>
				</div>

				<div class="input-box <?=in_array($code_error, ['error_password_reinput']) ? 'error' : '';?>">
					<i class="fa fa-lock form-control-feedback"></i>
					<input type="password" placeholder="Nhập lại mật khẩu mới" name="<?=Auth::INPUT_REPASSWORD;?>" value="" required>
				</div>

				<div class="input-box button">
					<input type="submit" value="Cập nhật tài khoản">
				</div>
			</form>
		</div>

	</body>
</html>