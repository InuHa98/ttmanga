<?php View::render_theme('layout.header', ['title' => $block_view['title']]); ?>


<div class="section-sub-header">
	<div class="container">
        <span>Report</span>
    </div>
</div>




<div class="container">
	<div class="row">

		<div class="col-xs-12 col-md-12">
			<div class="tabmenu-horizontal margin-b-2">

				<div class="tabmenu-horizontal__item <?=($block == reportController::BLOCK_SUBMIT ? 'active' : null);?>">
					<a href="<?=RouteMap::get('report', ['block' => reportController::BLOCK_SUBMIT]);?>">Báo lỗi</a>
				</div>

				<div class="tabmenu-horizontal__item <?=($block == reportController::BLOCK_LIST ? 'active' : null);?>">
					<a href="<?=RouteMap::get('report', ['block' => reportController::BLOCK_LIST]);?>">Báo lỗi đã gửi</a>
				</div>

			<?php if(UserPermission::isAdmin()): ?>
				<div class="tabmenu-horizontal__item <?=($block == reportController::BLOCK_ALL ? 'active' : null);?>">
					<a href="<?=RouteMap::get('report', ['block' => reportController::BLOCK_ALL]);?>">
						<span>Tất cả báo lỗi</span>
					<?php if($block != reportController::BLOCK_ALL && $notification_report > 0): ?>
						<span class="count-new-item"><?=number_format($notification_report, 0, ',', '.');?></span>
					<?php endif; ?>
					</a>
				</div>
			<?php endif; ?>
			
			</div>

			<?php 
				if($block_view)
				{
					View::render_theme($block_view['view'], $block_view['data']);
				}
			?>

		</div>



	</div>
</div>

<?php View::render_theme('layout.footer'); ?>