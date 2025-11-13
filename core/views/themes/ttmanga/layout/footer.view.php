<?php

	//echo round(memory_get_usage() / 1024 / 1024, 2) . ' MB / '.round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB<br>';


?>
		</div>
		<div id="back-to-top" class="back-to-top">
			<i class="fas fa-arrow-up"></i>
		</div>

		<div id="section-footer" class="section-footer">
			<div class="section-footer__header"></div>
			<div class="section-footer__body">
				<div class="container">
					<img class="footer__logo" src="<?=APP_URL;?>/assets/images/logo.png">
					<div class="footer__content">
						<ul>
							<li>
								<a href="#" target="_blank">Nội quy &amp; Hướng dẫn</a>
							</li>
							<li>
								<a href="#" target="_blank">Liên hệ: <?=_echo(env(DotEnv::APP_EMAIL));?></a>
							</li>
						</ul>
						<div class="copyright">
							<p>Copyright © 2014 - <?=date('Y');?> TTmanga All rights reserved.</p>
							<p>Powered By Inuha - Version 2.0</p>
						</div>
					</div>
				</div>
			</div>
		</div>

		<link rel="stylesheet" href="<?=APP_URL;?>/assets/css/toast-dialog.css?v=<?=$_version;?>">

		<script type="text/javascript" src="<?=APP_URL;?>/assets/styles/<?=themeController::$current_theme;?>/js/app.js?v=<?=$_version;?>"></script>
		<script type="text/javascript" src="<?=APP_URL;?>/assets/js/toast-dialog.js?v=<?=$_version;?>"></script>
		<script type="text/javascript" src="<?=APP_URL;?>/assets/js/custom-select.js?v=<?=$_version;?>"></script>

		<script type="text/javascript">
			$(document).ready(() => {
				const resize_footer = function() {
					$('body').css('padding-bottom', $('#section-footer').outerHeight(true) + 'px');
				};
				resize_footer();
				$(window).on('resize', resize_footer);

				const alerts = <?=json_encode(Alert::show());?>;
				for(let alert of alerts)
				{
					$.toastShow(alert.message, {
						type: alert.type,
						timeout: alert.timeout || 3000
					});				
				}

				const highlightSearch = (str, search) => {
					const removeAccent = s => s.normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/đ/g, "d").replace(/Đ/g, "D");

					const raw = str;
					const strNoAccent = removeAccent(str);
					const searchNoAccent = removeAccent(search);

					const regex = new RegExp(searchNoAccent, "gi");

					let result = "";
					let lastIndex = 0;
					strNoAccent.replace(regex, (match, offset) => {
						result += raw.slice(lastIndex, offset) + `<span>${raw.slice(offset, offset + match.length)}</span>`;
						lastIndex = offset + match.length;
					});
					result += raw.slice(lastIndex);
					return result;
				};

				const delay = (function () {
					let timer = 0;
					return function (callback, ms) {
						clearTimeout(timer);
						timer = setTimeout(callback, ms);
					};
				})();

				const inputSearch = $('input[data-search="input-keyword"]')
				const inputSearchMini = $('input[data-search="input-keyword-mini"]')
				const selectSearchType = $('select[data-search="input-type"]')

				const SuggestSearch = (type) => {
					const result = $('#result-search')
					const keyword = $.trim(inputSearch.val() || inputSearchMini.val());
					type = type || selectSearchType.val()
					
					if (keyword?.length > 2) {
						result.html('<div class="progress-bar"></div>');
						result.addClass('active');
						$.ajax({
							type: "POST",
							dataType: 'json',
							url: "<?=appendUrlApi(RouteMap::get('search_manga'));?>",
							data: {
								'<?=mangaController::PARAM_TYPE_SEARCH;?>': type,
								'<?=mangaController::INPUT_KEYWORD;?>': keyword
							},
							success: function (response) {
								if (response.length) {
									let html = '<div class="list-result-search">';

									if (type !== '<?=mangaController::INPUT_KEYWORD;?>') {
										response.forEach(o => {
											html += `
												<a class="list-result-search__text" href="${o.url}">${highlightSearch(o.name, keyword)}</a>
											`;
										})
									}
									else {
										response.forEach(o => {
											let name_other = '';
											if (o.name_other?.length) {
												name_other += '<ul class="list-result-search__manga-detail--name_other">';
												o.name_other.forEach(no => {
													name_other += `<li>${highlightSearch(no, keyword)}</li>`;
												})
												name_other += '</ul>';
											}
											html += `
												<a class="list-result-search__manga" href="${o.url}">
													<img src="${o.image}" />
													<div class="list-result-search__manga-detail">
														<div class="list-result-search__manga-detail--name">${highlightSearch(o.name, keyword)}</div>
														${name_other}
														<div class="list-result-search__manga-detail--chapter">${o.last_chapter}</div>
													</div>
												</a>
											`;
										})
									}
									html += '</div>'
									result.html(html);
								} else {
									result.html('');
									result.removeClass('active');
								}
							}
						});
					} else {
						result.html('');
						result.removeClass('active');
					}
				}

				inputSearch.on('keyup', function () {
					delay(function () {
						SuggestSearch();
					}, 500);
				});

				inputSearchMini.on('keyup', function () {
					delay(function () {
						SuggestSearch('<?=mangaController::INPUT_KEYWORD;?>');
					}, 500);
				});

				selectSearchType.on('change', function () {
					SuggestSearch($(this).val());
				});

			});
		</script>
	</body>
</html>
<?php
	Alert::clear();
?>