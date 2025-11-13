<?php
if($error)
{
    echo '<div class="alert alert--error">'.$error.'</div>';
}
else if($success)
{
    echo '<div class="alert alert--success">'.$success.'</div>';
}

?>

	<form id="form-validate" method="POST">
        <?=Security::insertHiddenToken();?>
            <div class="form-group">
				<label class="control-label">Lý do:</label>
				<div class="form-control">
					<select class="form-select js-custom-select" name="<?=reportController::INPUT_REASON;?>" data-placeholder="Lý do" data-max-width="300px">
                        <option value="<?=Report::TYPE_DUPLICATE;?>"><?=Report::getTypeName(Report::TYPE_DUPLICATE);?></option>
                        <option value="<?=Report::TYPE_INCORRECT;?>" <?=($type == Report::TYPE_INCORRECT ? 'selected' : null);?>><?=Report::getTypeName(Report::TYPE_INCORRECT);?></option>
                        <option value="<?=Report::TYPE_VANDALISM;?>" <?=($type == Report::TYPE_VANDALISM ? 'selected' : null);?>><?=Report::getTypeName(Report::TYPE_VANDALISM);?></option>
                        <option value="<?=Report::TYPE_OTHER;?>" <?=($type == Report::TYPE_OTHER ? 'selected' : null);?>><?=Report::getTypeName(Report::TYPE_OTHER);?></option>
                    </select>
				</div>
			</div>
            
            <div class="form-group">
				<label class="control-label">Truyện</label>
                <div class="form-control">
                    <span class="form-control-feedback"><i class="fas fa-search"></i></span>
                    <input id="search-manga" class="form-input" placeholder="Tìm kiếm tên truyện..." role="search-manga" />
                    <input type="hidden" name="<?=reportController::PARAM_MANGA_ID;?>" role="result-data">
                    <div class="search-user__selected"></div>
                    <ul class="search-user__results p-0"></ul>
                </div>
            </div>

        <?php if ($manga): ?>
            <div class="form-group">
				<label class="control-label"></label>
                <div class="form-control">
                    <div class="report-info">
                        <img src="<?=_echo($manga['image']);?>" />
                        <div class="report-info__detail">
                            <a class="report-info__detail--title" target="_blank" href="<?=RouteMap::get('manga', ['id' => $manga['id']]);?>"><?=_echo($manga['name']);?></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group" id="list-chapter">
				<label class="control-label">Chương</label>
                <div class="form-control">
					<select class="form-select js-custom-select" name="<?=reportController::PARAM_CHAPTER_ID;?>" data-placeholder="Chương truyện lỗi">
                        <?php foreach($lst_chapter as $o): ?>
                            <option value="<?=$o['id'];?>" <?=(!empty($chapter['id']) && $chapter['id'] == $o['id'] ? 'selected' : null);?>><?=_echo($o['name']);?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        <?php endif; ?>

            <div class="form-group">
				<label class="control-label">Nội dung</label>
                <div class="form-control">
				    <textarea class="form-textarea" name="<?=reportController::INPUT_NOTE;?>" placeholder="Nội dung báo lỗi"><?=_echo($note);?></textarea>
                </div>
            </div>
			<button type="submit" class="btn btn--round pull-right my-4">Gửi báo lỗi</button>
	</form>


<script type="text/javascript">
    $(document).ready(() => {
        const delay_search = (function () {
            let timer = 0;
            return function (callback, ms) {
                clearTimeout(timer);
                timer = setTimeout(callback, ms);
            };
        })();

        const result_search = $('.search-user__results');
        const selected_search = $('.search-user__selected');

        $(document).off('keyup.search_manga').on('keyup.search_manga', '[role="search-manga"]', function() {


            delay_search(() => {

                if($(this).val().trim() == '') {
                    return result_search.hide();
                }

                result_search.show();
                selected_search.hide();
                
                const offset_search_user = result_search.parent()[0].getBoundingClientRect();

                result_search.css({
                    position: 'absolute',
                    top: offset_search_user.height + 5 + 'px',
                });


                $.ajax({
                    type: "GET",
                    url: "<?=appendUrlApi(RouteMap::get('ajax', ['name' => ajaxController::SEARCH_REPORT]));?>",
                    data: {
                        <?=InterFaceRequest::KEYWORD;?>: $(this).val()
                    },
                    dataType: 'json',
                    cache: false,
                    success: function(response)
                    {
                        if(response.code == 200)
                        {
                            let html = '<div class="list-result-search--report">';
                            response.data.forEach(o => {
                                let name_other = '';
                                if (o.name_other?.length) {
                                    name_other += '<ul class="list-result-search__manga-detail--name_other">';
                                    o.name_other.forEach(no => {
                                        name_other += `<li>${no}</li>`;
                                    })
                                    name_other += '</ul>';
                                }
                                html += `
                                    <a class="list-result-search__manga" href="?<?=reportController::PARAM_MANGA_ID;?>=${o.id}">
                                        <img src="${o.image}" />
                                        <div class="list-result-search__manga-detail">
                                            <div class="list-result-search__manga-detail--name"><span>#${o.id}</span>${o.name}</div>
                                            ${name_other}
                                        </div>
                                    </a>
                                `;
                            })
                            html += '</div>';
                            return result_search.html(html);
                        }
                        result_search.html('<span class="empty">' + response.message + '</span>');
                    },
                    error: function(response)
                    {
                        result_search.html('<span class="error">Có lỗi xảy ra. Vui lòng thử lại</span>');
                    }
                });
            }, 500);
        });

        $(document).off('click.search_manga').on('click.search_manga', '.search-user__results-item', function(e) {
            e.stopPropagation();
            selected_search.html($(this).html());
            selected_search.show();
            result_search.hide();
            form.find('input[role="search-manga"]').val('');
            form.find('input[role="result-data"]').val($(this).attr('data-id'));
        });
    });
</script>