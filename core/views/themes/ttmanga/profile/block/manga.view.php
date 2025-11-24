


<?php if($manga_items): ?>
<div class="flex-panel">
    <div class="flex-panel__box">
        <span>Có tất cả <strong><?=number_format($count, 0, ',', '.');?></strong> truyện.</span>
    </div>
    <div class="flex-panel__box flex--right">
        <div class="btn-group view-mode-change">
            <span role="change-view-mode" class="btn btn--small <?=($view_mode != 'table' ? 'active' : null);?>" data-mode="grid">
                <i class="fas fa-th"></i>
            </span>
            <span role="change-view-mode" class="btn btn--small <?=($view_mode == 'table' ? 'active' : null);?>" data-mode="table">
			    <i class="fas fa-list"></i>
		    </span>
        </div>
    </div>
</div>

<div class="manga-list-view">    
    <ul class="list-view <?=($view_mode == 'table' ? 'mode--table' : null);?>">
    <?php foreach($manga_items as $manga): ?>
    <?php
        $url_manga = RouteMap::get('manga', ['id' => $manga['id']]);
        $url_chapter = RouteMap::get('chapter', ['id_manga' => $manga['id'], 'id_chapter' => $manga['id_last_chapter']]);
        $teams = Manga::get_team_name($manga, true);
        $genres = array_intersect_key($_genres, array_flip(array_filter(explode(',', $manga['genres_id'] ?? ''))));
    ?>
        <li class="list-view__item tooltip-data">
            <a class="list-view__item-image tooltip-target" href="<?=$url_manga;?>">
                <span class="views">
                    <span class="me-1">
                        <i class="fas fa-eye"></i> <?=shortenNumber($manga['view']); ?>
                    </span>
                    <span>
                        <i class="fas fa-bookmark"></i> <?=shortenNumber($manga['follow']); ?>
                    </span>
                </span>
                <?php if ($manga['view'] >= env(DotEnv::VIEW_HOT)): ?>
                    <span class="hot"></span>
                <?php endif; ?>
                <img data-tooltip="image" src="<?=_echo($manga['image']);?>">
            </a>
            <div class="list-view__item-info">
                <a class="info-name" data-tooltip="title" href="<?=$url_manga;?>"><?=_echo($manga['name']);?></a>
                <div class="info__group">
                    <div class="info-genres">
                        <?php if($genres): foreach($genres as $id => $name): ?>
                            <a href="<?=RouteMap::build_query([mangaController::PARAM_GENRES => $id], 'manga');?>"><?=_echo($name);?></a>
                        <?php endforeach; else: ?>
                            <span class="empty">Không rõ</span>
                        <?php endif; ?>
                    </div>
                    <div class="info-status" data-status="<?=$manga['status'];?>">
                        <span class="info-label">Tình trạng:</span>
                        <?=Manga::get_status_name($manga);?>
                    </div>
                    <div class="info-team">
                        <span class="info-label">Nhóm dịch:</span>
                        <?php if($teams): foreach($teams as $val): ?>
                            <a href="<?=RouteMap::get('team', ['name' => $val]);?>"><?=_echo($val);?></a>
                        <?php endforeach; else: ?>
                            <span class="empty">Không rõ</span>
                        <?php endif; ?>
                    </div>
                    <div class="info-chapter">
                        <span class="info-label">Mới nhất:</span>
                        <?=($manga['id_last_chapter'] ? '<a href="'.$url_chapter.'">'._echo($manga['name_last_chapter']).'</a>' : '<span class="empty">Chưa có!!!</span>');?>
                    </div>
                    <div class="info-desc" data-tooltip="desc"><?=_echo($manga['text'], true, false);?></div>
                </div>
            </div>
        </li>
    <?php endforeach; ?>
    </ul>
</div>

<div class="pagination">
	<?=html_pagination($pagination);?>
</div>

<?php else: ?>
    <div class="alert alert--warning">Chưa có truyện nào!</div>
<?php endif; ?>


<script type="text/javascript">
    $(document).ready(function() {
        tooltip({
            target: '.tooltip-target'
        });
        modeView('<?=App::COOKIE_VIEW_MODE;?>');
    });  
</script>