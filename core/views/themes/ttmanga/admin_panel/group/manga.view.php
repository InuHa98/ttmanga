<div class="box box--list">
	<div class="box__body">
		<a class="box__body-item <?=($block_name == adminPanelController::BLOCK_MANGA_LIST ? 'active' : null);?>" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_MANGA, 'block' => adminPanelController::BLOCK_MANGA_LIST]);?>">
			<span class="item-icon">
				<i class="fas fa-list"></i>
			</span>
			<div>
				<span class="item-title">Danh sách truyện</span>
			</div>
		</a>
		<a class="box__body-item <?=($block_name == adminPanelController::BLOCK_MANGA_TRASH ? 'active' : null);?>" href="<?=RouteMap::get('admin_panel', ['group' => adminPanelController::GROUP_MANGA, 'block' => adminPanelController::BLOCK_MANGA_TRASH]);?>">
			<span class="item-icon">
				<i class="fas fa-trash"></i>
			</span>
			<div>
				<span class="item-title">Truyện đã xoá</span>
			</div>
		</a>
	</div>
</div>