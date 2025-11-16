<?php View::render_theme('layout.header', ['title' => $title]); ?>


<div class="section-sub-header">
	<div class="container">
        <span>Messenger</span>
    </div>
</div>

<div class="container">
	<div class="row">
		<div class="col-xs-12 col-md-4 col-lg-3">
			<div class="box box--list">
				<div class="box__body">
					<a class="box__body-item" href="<?=RouteMap::get('messenger', ['block' => messengerController::BLOCK_INBOX]);?>">
						<span class="item-icon">
							<i class="fab fa-facebook-messenger"></i>
						</span>
						<div>
							<span class="item-title">Danh sách trò chuyện</span>
						</div>
                    <?php if($_count_message > 0) : ?>
						<span class="count-new-item"><?=number_format($_count_message, 0, ',', '.');?></span>
					<?php endif; ?>
					</a>
					<a class="box__body-item active" href="<?=RouteMap::get('messenger', ['block' => messengerController::BLOCK_NEW]);?>">
						<span class="item-icon">
							<i class="fas fa-edit"></i>
						</span>
						<div>
							<span class="item-title">Tin nhắn mới</span>
						</div>
					</a>
					<a class="box__body-item" href="<?=RouteMap::get('messenger', ['block' => messengerController::BLOCK_SPAM]);?>">
						<span class="item-icon">
							<i class="fas fa-ban"></i>
						</span>
						<div>
							<span class="item-title">Tin nhắn spam</span>
						</div>
					</a>
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-md-8 col-lg-9">
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
            <div class="box">
                <form id="form-validate" method="POST">
                    <?=Security::insertHiddenToken();?>
                    <input type="hidden" name="<?=messengerController::SUBMIT_NAME;?>" value="submit">
                    <div class="box__header">Chọn một người nhận</div>
                    <div class="box__body">
                        <div class="form-group">
                            <div class="form-control">
                                <div class="search-user">
                                    <input type="hidden" name="<?=messengerController::INPUT_USER_ID;?>" role="result-data">
                                    <span class="form-control-feedback"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-input" placeholder="Tìm kiếm thành viên" role="search-user">
                                    <div class="search-user__selected"></div>
                                    <ul class="search-user__results"></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box__footer">
                        <button type="submit" class="btn btn--round pull-right" name="<?=messengerController::SUBMIT_NAME;?>">Gửi tin nhắn</button>
                    </div>
                </form>
            </div>
		</div>
	</div>
</div>

<script type="text/javascript" src="<?=APP_URL;?>/assets/script/form-validator.js?v=<?=$_version;?>"></script>
<script type="text/javascript">
	$(document).ready(function(){

        const form = $('#form-validate');
        
        const delay_search = (function () {
            let timer = 0;
            return function (callback, ms) {
                clearTimeout(timer);
                timer = setTimeout(callback, ms);
            };
        })();

        const result_search = $('.search-user__results');
        const selected_search = $('.search-user__selected');

        $(document).off('keyup.search_user').on('keyup.search_user', '[role="search-user"]', function() {


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
                    url: "<?=appendUrlApi(RouteMap::get('ajax', ['name' => ajaxController::SEARCH_USER]));?>",
                    data: {
                        <?=InterFaceRequest::KEYWORD;?>: $(this).val()
                    },
                    dataType: 'json',
                    cache: false,
                    success: function(response)
                    {
                        if(response.code == 200)
                        {
                            result_search.html('');
                            response.data.forEach(user => {
                                result_search.append(`
                                <li class="search-user__results-item" data-id="${user.id}">
                                    <div class="user-infomation bg--none">
                                        <div class="user-avatar" data-text="${user.first_name}" style="--bg-avatar: ${user.bg_avatar}">
                                            <img src="${user.avatar}">
                                        </div>
                                        <div>
                                            <div class="user-display-name">${user.display_name}</div>
                                            <div class="user-username">@${user.username}</div>
                                        </div>
                                    </div>
                                </li>`);
                            });
                            return;
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

        $(document).off('click.search_user').on('click.search_user', '.search-user__results-item', function(e) {
            e.stopPropagation();
            selected_search.html($(this).html());
            selected_search.show();
            result_search.hide();
            form.find('input[role="search-user"]').val('');
            form.find('input[role="result-data"]').val($(this).attr('data-id'));
        });


        Validator({
            form: '#form-validate',
            selector: '.form-control',
            class_error: 'error',
            class_message: null,
            rules: {
                'input[role="result-data"]': [
                    Validator.isRequired()
                ]
            }
        });
    });
</script>
<?php View::render_theme('layout.footer'); ?>



