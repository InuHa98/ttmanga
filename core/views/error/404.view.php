<!DOCTYPE html>
<html lang="en" dir="ltr">
	<head>
		<meta charset="UTF-8">
    	<title><?=$title;?></title>
   		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
		<link rel="icon" type="image/x-icon" href="<?=APP_URL;?>/assets/favico.ico">
		<link rel="shortcut icon" type="image/x-icon" href="<?=APP_URL;?>/assets/favico.ico">
		<link rel="stylesheet" type="text/css" href="<?=APP_URL;?>/assets/css/error_404.css?t=<?=$_version;?>" />
	</head>
	<body class="background">
		<div class="container">
			<div class="box">
				<h1>4<span>0</span>4</h1>
				<div class="title"><?=_echo($text);?></div>
				<div class="desc"><?=_echo($desc);?></div>
			</div>
		</div>
	</body>
</html>