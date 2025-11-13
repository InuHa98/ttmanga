<?php
	View::render_theme('layout.header', ['title' => $block_view['title']]);
	if($block_view)
	{
		View::render_theme($block_view['view'], $block_view['data']);
	}

?>

<script type="text/javascript" src="<?=APP_URL;?>/assets/js/form-validator.js?v=<?=$_version;?>"></script>
<?php View::render_theme('layout.footer');?>