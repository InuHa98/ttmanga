


<?php if($lst_comment): ?>
<div class="flex-panel">
    <div class="flex-panel__box">
        <span>Có tất cả <strong><?=number_format($count, 0, ',', '.');?></strong> bình luận.</span>
    </div>
</div>

<div class="section-comment">
    <div class="comment-container p-0 mt-4">
    <?php foreach($lst_comment as $val):
        $user = [
            'id' => $val['user_id'],
            'name' =>  $val['user_name'],
            'username' =>  $val['user_username'],
            'avatar' =>  $val['user_avatar'],
            'user_ban' =>  $val['user_ban'],
            'role_color' =>  $val['user_role_color'],
        ];
    ?>
        <div class="comment-item px-0 mb-2">
            <div class="comment-wrapper ms-2">
                <div class="comment-wrapper__body">
                    <div class="comment-wrapper__body-text w-100">
                        <div class="text mt-1"><?=_echo($val['text'], true, true);?></div>
                    </div>
                </div>
                <div class="comment-wrapper__footer">
                    <div class="time"><?=_time($val['created_at']);?></div>
                    <span class="chapter">
                        <a title="<?=_echo($val['manga_name']);?>" href="<?=$val['chapter_name'] ? RouteMap::get('chapter', ['id_manga' => $val['manga_id'], 'id_chapter' => $val['chapter_id']]) : RouteMap::get('manga', ['id' => $val['manga_id']]);?>"><?=$val['chapter_name'] ? _echo($val['chapter_name']) :  _echo($val['manga_name']);?></a>
                    </span>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
</div>

<div class="pagination">
	<?=html_pagination($pagination);?>
</div>

<?php else: ?>
    <div class="alert alert--warning">Chưa có bình luận nào!</div>
<?php endif; ?>