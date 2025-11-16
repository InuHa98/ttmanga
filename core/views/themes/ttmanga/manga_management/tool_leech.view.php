<?=themeController::load_css('css/manga.css'); ?>
<div class="section-sub-header">
	<div class="container">
        <span>Tool leech</span>
    </div>
</div>

<div class="container">

	<div class="my-2">
		<a href="<?=RouteMap::get('manga_management', ['action' => mangaManagementController::ACTION_DETAIL, 'id' => $manga['id']]);?>"><i class="fas fa-chevron-left"></i> <?=_echo($manga['name']);?></a>
	</div>

	<div class="row">

		<div class="col-12">
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
		</div>

		<div class="col-lg-12">

            <div class="dialog-label">Nhập link truyện:</div>
            <div class="form-group">
                <div class="form-control">
                    <input class="form-input" placeholder="https://" id="link">
                    <button type="button" class="btn" id="btn-get-chapter">Get list chapter</button>
                    <div class="label-desc">Hiện tại chức năng chỉ hỗ trợ: foxtruyen, truyenqq, mto, hangtruyen, mangadex, minotruyen</div>
                </div>
            </div>

            <div class="rule-team my-2">
                <div class="rule-team__title">Chú ý:</div>
                <div class="rule-team__text">- Chapter leech có tên trùng với chapter cũ đã có thì sẽ bị ghi đè ảnh. Còn lại sẽ được tạo chương mới.</div>
                <div class="rule-team__text">- Dữ liệu sẽ được lưu lại cho đến khi thêm thành công vào truyện hoặc nhập link get khác.</div>
            </div>

            <div id="getlink-content" style="display: none">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 mb-2">
                    <div class="dialog-label">
                        <span role="progress-title">Tiến trình</span>: <span role="progress">--/--</span>
                    </div>
                    <button class="btn btn--info  disabled" id="btn-select-chapter">
                        Leech mục đã chọn <span role="multiple_selected_count">(0)</span>
                    </button>
                </div>
                <div class="chapter-list mt-0 p-0 mb-2 table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>
                                    <span class="form-check">
                                        <input type="checkbox" id="multiple_selected_all">
                                        <label for="multiple_selected_all"></label>
                                    </span>
                                </th>
                                <th width="100%">Tên chương</th>
                                <th>Số lượng ảnh</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="reupload-content" style="display: none">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 mb-2">
                    <div class="dialog-label">
                        <span role="progress-title">Tiến trình</span>: <span role="progress">--/--</span>
                    </div>
                    <div class="d-flex justify-content-start align-items-center flex-wrap gap-1 mt-3">
                        <button type="button" class="btn btn--small btn--gray" id="btn-rename-all"><i class="fas fa-file-signature"></i> Rename all</button>
                        <button type="button" class="btn btn--small btn--info disabled" id="btn-reupload"><i class="fas fa-play"></i> Reupload</button>
                        <button type="button" class="btn btn--small btn--danger disabled" id="btn-stop"><i class="fas fa-stop"></i> Stop</button>
                        <button type="button" class="btn btn--small btn--warning disabled" id="btn-retry"><i class="fas fa-sync-alt"></i> Retry</button>
                        <button type="button" class="btn btn--small btn--success disabled" id="btn-save"><i class="fas fa-save"></i> Thêm vào truyện</button>
                    </div>
                </div>
                <div class="chapter-list mt-0 p-0 mb-2 table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th></th>
                                <th width="100%">Tên chương</th>
                                <th>Reupload</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>

		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(() => {

        const role_multiple_selected = '[role=multiple_selected]';

		multiple_selected({
			role_select_all: "#multiple_selected_all",
			role_select: role_multiple_selected,
			onSelected: function(total_selected, config){
				$("[role=multiple_selected_count]").html('('+total_selected+')');
				$("[role=multiple_selected_count]").parents('.btn').removeClass("disabled");
			},
			onNoSelected: function(total_selected, config){
				$("[role=multiple_selected_count]").html('(0)');
				$("[role=multiple_selected_count]").parents('.btn').removeClass("disabled").addClass("disabled");
			}
		});


        const linkGet = $('#link')
        const btnGetListChapter = $('#btn-get-chapter')
        const btnSelectChapter = $('#btn-select-chapter')
        const btnRenameAll = $('#btn-rename-all')
        const btnReupload = $('#btn-reupload')
        const btnStop = $('#btn-stop')
        const btnRetry = $('#btn-retry')
        const btnSave = $('#btn-save')
        const contentGetLink = $('#getlink-content')
        const contentReupload = $('#reupload-content')


        const storageKeyItems = "TOOL_LEECH_ITEMS";
        const storageKeyLink = "TOOL_LEECH_LINK";
        const storageKeyReferer = "TOOL_LEECH_REFERER";
        let stopGetlink = false
        let stopReupload = false

        let REFERER = localStorage.getItem(storageKeyReferer)
        let ITEMS = JSON.parse(localStorage.getItem(storageKeyItems) || "[]")

        linkGet.val(localStorage.getItem(storageKeyLink) || '')

        const saveItems = () => {
            const current_items = JSON.parse(localStorage.getItem(storageKeyItems) || "[]");
            ITEMS.forEach(o => {
                const index = current_items.findIndex(c => c.link == o.link);
                if (index !== -1) {
                    current_items[index] = o;
                } else {
                    current_items.push(o);
                }
            });
            localStorage.setItem(storageKeyItems, JSON.stringify(current_items));
        };
        const clearItems = () => {
            localStorage.setItem(storageKeyItems, JSON.stringify([]));
        };
        const saveLink = (link = '') => {
            localStorage.setItem(storageKeyLink, link);
        }
        const saveReferer = (referer = '') => {
            localStorage.setItem(storageKeyReferer, referer);
        }

        const STATUS_PENDING = 1
        const STATUS_ERROR = 2
        const STATUS_COMPLETE = 3
        const STATUS_UPLOADING = 4

        const render_status_get = (content) => {
            const titleProgress = content.find('[role="progress-title"]')
            const progress = content.find('[role="progress"]')

            const total_pending = ITEMS.filter(
                (o) => o.status == STATUS_PENDING
            ).length;
            const total_error = ITEMS.filter(
                (o) => o.status == STATUS_ERROR
            ).length;
            const total_complete = ITEMS.filter(
                (o) => o.status == STATUS_COMPLETE
            ).length;


            if (!ITEMS.length) {
                progress.html('--/--')
                return titleProgress.html('Tiến trình')
            }
            titleProgress.html('Lấy dữ liệu chapter')
            
            if (total_pending > 0) {
                progress
                .removeClass("text-success text-danger")
                .addClass("text-warning")
                .html(`${total_complete}/${ITEMS.length}`);
            } else {
                if (total_complete == ITEMS.length && total_complete > 0) {
                    progress
                        .removeClass("text-warning text-danger")
                        .addClass("text-success")
                        .html("Complete!");
                } else if (total_error > 0) {
                    progress
                        .removeClass("text-warning text-success")
                        .addClass("text-danger")
                        .html(`Error(${total_error})`);
                } else {
                    progress
                        .removeClass("text-warning text-success text-danger")
                        .html(`--/--`);
                }
            }
        }

        const render_status_reupload = () => {
            const titleProgress = contentReupload.find('[role="progress-title"]')
            const progress = contentReupload.find('[role="progress"]')

            const total_pending = ITEMS.filter(
                (o) => o?.images?.some(o2 => o2.status == STATUS_PENDING)
            ).length;
            const total_error = ITEMS.filter(
                (o) => o?.images?.some(o2 => o2.status == STATUS_ERROR)
            ).length;
            const total_complete = ITEMS.filter(
                (o) => o?.images?.every(o2 => o2.status == STATUS_COMPLETE)
            ).length;

            if (!ITEMS.length) {
                progress.html('--/--')
                return titleProgress.html('Tiến trình')
            }
            titleProgress.html('Reupload ảnh')

            if (total_pending > 0) {
                progress
                .removeClass("text-success text-danger")
                .addClass("text-warning")
                .html(`${total_complete}/${ITEMS.length}`);
            } else {
                if (total_complete == ITEMS.length && total_complete > 0) {
                    progress
                        .removeClass("text-warning text-danger")
                        .addClass("text-success")
                        .html("Complete!");
                } else if (total_error > 0) {
                    progress
                        .removeClass("text-warning text-success")
                        .addClass("text-danger")
                        .html(`Error(${total_error})`);
                } else {
                    progress
                        .removeClass("text-warning text-success text-danger")
                        .html(`--/--`);
                }
            }
        }

        const render_status_save = () => {
            const titleProgress = contentReupload.find('[role="progress-title"]')
            const progress = contentReupload.find('[role="progress"]')

            const total_pending = ITEMS.filter(
                (o) => o.status == STATUS_PENDING
            ).length;
            const total_error = ITEMS.filter(
                (o) => o.status == STATUS_ERROR
            ).length;
            const total_complete = ITEMS.filter(
                (o) => o.status == STATUS_COMPLETE
            ).length;


            if (!ITEMS.length) {
                progress.html('--/--')
                return titleProgress.html('Tiến trình')
            }
            titleProgress.html('Lưu chapter')

            if (total_pending > 0) {
                progress
                .removeClass("text-success text-danger")
                .addClass("text-warning")
                .html(`${total_complete}/${ITEMS.length}`);
            } else {
                if (total_complete == ITEMS.length && total_complete > 0) {
                    progress
                        .removeClass("text-warning text-danger")
                        .addClass("text-success")
                        .html("Complete!");
                } else if (total_error > 0) {
                    progress
                        .removeClass("text-warning text-success")
                        .addClass("text-danger")
                        .html(`Error(${total_error})`);
                } else {
                    progress
                        .removeClass("text-warning text-success text-danger")
                        .html(`--/--`);
                }
            }
        }

        const render_status_chapter = (chapter) => {
            const target = chapter?.target
            if (!target || !chapter?.images) {
                return render_status_reupload();
            }

            const titleProgress = target.find('[role="chapter-progress-title"]')
            const progress = target.find('[role="chapter-progress"]')

            const total_pending = chapter.images.filter(
                (o) => o.status == STATUS_PENDING
            ).length;
            const total_uploading = chapter.images.filter(
                (o) => o.status == STATUS_UPLOADING
            ).length;
            const total_error = chapter.images.filter(
                (o) => o.status == STATUS_ERROR
            ).length;
            const total_complete = chapter.images.filter(
                (o) => o.status == STATUS_COMPLETE
            ).length;
            

            progress.html(`${total_complete}/${chapter.images.length}`);

            if (total_complete == chapter.images.length && total_complete > 0) {
                titleProgress
                    .removeClass("text-warning text-danger text-info")
                    .addClass("text-success")
                    .html("Complete!");
            } else if (total_uploading > 0) {
                titleProgress
                    .removeClass("text-warning text-success text-danger")
                    .addClass("text-info")
                    .html(`Uploading...`);
            } else if (total_error > 0) {
                titleProgress
                    .removeClass("text-warning text-success text-info")
                    .addClass("text-danger")
                    .html(`Error(${total_error})`);
            } else {
                titleProgress
                    .removeClass("text-success text-danger text-info")
                    .addClass("text-warning")
                    .html(`Pending`);
            }
            render_status_reupload();
        }

        const render_list_getlink_chapter = (content) => {
            const table = content.find('table')
            const bodyTable = table.find('tbody')
            bodyTable.html(null)
            ITEMS.forEach((item, index) => {
                let status = '<span>--</span>'
                if (item.status == STATUS_ERROR) {
                    status = '<span class="text-danger">ERROR</span>'
                }
                else if (item.status == STATUS_COMPLETE) {
                    status = '<span class="text-success">OK</span>'
                }
                const tr = $(`
                <tr>
                    <td>
                        <div class="form-check">
                            <input type="checkbox" role="multiple_selected" value="${item.link}" id="tr_${index}">
                            <label for="tr_${index}"></label>
                        </div>
                    </td>
                    <td class="chapter-name">
                        <a target="_blank" class="text-primary" href="${item.link}">${item.name}</a>
                    </td>
                    <td class="nowrap align-center">${item?.images?.length || '--'}</td>
                    <td class="nowrap align-center">${status}</td>
                </tr>`)
                bodyTable.append(tr)
            })
        }

        const render_list_reupload_chapter = () => {
            const table = contentReupload.find('table')
            const bodyTable = table.find('tbody')
            bodyTable.html(null)
            ITEMS.forEach((item, index) => {
                let status
                if (item.status == STATUS_ERROR) {
                    status = '<span class="text-danger">Không thể lấy dữ liệu</span>'
                }
                else if (item.status == STATUS_UPLOADING) {
                    status = '<span class="text-info">Đang lấy dữ liệu...</span>'
                }
                else if (item.status == STATUS_PENDING) {
                    status = '<span class="text-info">Chờ lấy dữ liệu...</span>'
                }
                else {
                    if (!item?.images?.length) {
                        status = '<span class="text-warning">Chờ lấy dữ liệu...</span>'
                    }
                    else if (item.images.every(o => o.status == STATUS_COMPLETE)) {
                        status = '<span class="text-success">Complete!</span>'
                    }
                    else if (item.images.some(o => o.status == STATUS_PENDING)) {
                        status = '<span class="text-success">Pending reupload</span>'
                    }
                    else if (item.images.some(o => o.status == STATUS_ERROR)) {
                        const total_error = item.images.filter(o => o.status == STATUS_ERROR).length
                        status = '<span class="text-danger">Error(' + total_error + ')</span>'
                    } else {
                        status = '<span class="text-info">Đang lấy dữ liệu...</span>'
                    }
                }
                const tr = $(`
                <tr>
                    <td class="nowrap">
                        <button role="btn-edit-chapter" class="btn btn--small btn--gray text-">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </button>
                        <button role="btn-delete-chapter" class="btn btn--small btn--gray text-danger">
                            <i class="fas fa-times"></i> Xoá
                        </button>
                    </td>
                    <td class="chapter-name" role="name">
                        <a target="_blank" class="text-primary" href="${item.link}">${item.name}</a>
                    </td>
                    <td class="nowrap align-center" role="chapter-progress">${item?.images?.filter(o => o.status == STATUS_COMPLETE).length || 0}/${item?.images?.length || 0}</td>
                    <td class="nowrap align-center" role="chapter-progress-title">${status}</td>
                </tr>`)
                bodyTable.append(tr)
                item.target = tr
            })
        }

        const get_link_chapter = () => {
            if (stopGetlink) {
                return
            }

            render_status_get(contentReupload)
            render_list_reupload_chapter()
            const lst_pending_getlink = ITEMS.filter(o => o.status == STATUS_PENDING);
            if (!lst_pending_getlink.length) {
                if (ITEMS.some(o => o.status != STATUS_COMPLETE)) {
                    btnReupload.addClass('disabled')
                    btnRetry.removeClass('disabled')
                } else {
                    if (!ITEMS.every(item => item.images.every(o => o.status == STATUS_COMPLETE))) {
                        btnReupload.removeClass('disabled')
                    }
                    btnRetry.addClass('disabled')
                }
                return;
            } 

            const item = lst_pending_getlink.shift();
            if (!item?.link) {
                return get_link_chapter()
            }

            item.status = STATUS_UPLOADING
            render_list_reupload_chapter()

            $.ajax({
                url: '<?=appendUrlApi(RouteMap::get('tool_leech', ['block' => toolLeechController::BLOCK_GET_CHAPTER]));?>',
                type: "POST",
                data: {
                    <?=toolLeechController::INPUT_LINK;?>: item.link,
                },
                dataType: "json",
                success: (res) => {
                    if (res?.code === 200 && res?.data?.length) {
                        item.status = STATUS_COMPLETE
                        item.images = res.data.map((o, i) => ({index: i, link: o, status: STATUS_PENDING}))
                    } else {
                        item.status = STATUS_ERROR
                    }
                },
                error: (err) => {
                    item.status = STATUS_ERROR
                },
                complete: () => {
                    saveItems()
                    get_link_chapter()
                }
            });
        }

        const start_reupload = () => {

            if (ITEMS.filter((o) => o?.images?.some(o2 => o2.status == STATUS_UPLOADING)).length) {
                return;
            }

            const CHAPTERS = ITEMS.filter((o) => o?.images?.some(o2 => o2.status == STATUS_PENDING));
            if (CHAPTERS.length < 1 || stopReupload) {
                if (!ITEMS.every((o) => o?.images?.every(o2 => o2.status == STATUS_COMPLETE))) {
                    btnRetry.removeClass("disabled");
                    btnSave.addClass("disabled");
                } else {
                    btnRetry.addClass("disabled");
                    btnSave.removeClass("disabled");
                }
                btnRenameAll.removeClass("disabled");
                btnReupload.addClass("disabled");
                btnGetListChapter.removeClass("disabled");
                return btnStop.addClass("disabled");
            }

            const chapter = CHAPTERS.shift();
            chapter.target.find('[role="btn-edit-chapter"]').addClass('disabled')
            $('[role="btn-delete-chapter"]').addClass('disabled')
            btnGetListChapter.addClass("disabled");
            btnRetry.addClass("disabled");
            btnReupload.addClass("disabled");
            btnStop.removeClass("disabled");
            btnRenameAll.addClass("disabled");
            btnSave.addClass("disabled");

            process_reupload(chapter)
        };

        const process_reupload = (chapter) => {
            const lst_links = chapter.images.filter(o => o.status == STATUS_PENDING);
            if (lst_links.length < 1 || stopReupload) {
                chapter.target.find('[role="btn-edit-chapter"]').removeClass('disabled')
                chapter.status = STATUS_COMPLETE
                return start_reupload();
            }

            const link = lst_links.shift();

            if (link.status == STATUS_PENDING) {
                link.status = STATUS_UPLOADING;

                render_status_chapter(chapter);

                link.xhr = $.ajax({
                    url: '<?=appendUrlApi(RouteMap::get('tool_leech', ['block' => toolLeechController::BLOCK_REUPLOAD_IMAGE]));?>',
                    type: "POST",
                    data: {
                        link: link.link,
                        name: chapter.images.indexOf(link),
                        referer: REFERER,
                    },
                    dataType: "json",
                    success: function (res) {
                        if (res?.code === 200 && res?.data) {
                            link.status = STATUS_COMPLETE;
                            link.link = res.data;
                        } else {
                            link.status = STATUS_ERROR;
                        }
                    },
                    error: function (err) {
                        link.status = STATUS_ERROR;
                    },
                    complete: function () {
                        render_status_chapter(chapter);
                        process_reupload(chapter);
                        saveItems();
                    },
                });
            }
        };

        let total_add = 0
        let total_update = 0
        const save_chapter = () => {
            render_status_save()

            const lst_items = ITEMS.filter(o => o.status == STATUS_PENDING);
            if (!lst_items.length) {
                if (ITEMS.filter(o => o.status == STATUS_ERROR).length) {
                    return btnSave.removeClass('disabled')
                }
                ITEMS = [];
                saveLink('')
                saveReferer('')
                clearItems();
                btnSave.addClass('disabled')
                return  $.toastShow(`Có ${total_add} chapter được thêm vào và ${total_update} chapter được cập nhật`, {
                    type: "success",
                    timeout: 3000
                });
            } 

            const item = lst_items.shift();
            const titleProgress = item.target.find('[role="chapter-progress-title"]')

            titleProgress.html('<span class="text-info">Đang lưu...</span>')

            $.ajax({
                url: '<?=appendUrlApi(RouteMap::get('tool_leech', ['block' => toolLeechController::BLOCK_SAVE_CHAPTER]));?>',
                type: "POST",
                data: {
                    <?=toolLeechController::INPUT_ID;?>: <?=$manga['id'];?>,
                    <?=toolLeechController::INPUT_NAME;?>: item.name,
                    <?=toolLeechController::INPUT_IMAGES;?>: JSON.stringify(item.images.map(o => o.link)),
                },
                dataType: "json",
                success: (res) => {
                    if (res?.code === 200) {
                        item.status = STATUS_COMPLETE
                        titleProgress.html(`<span class="text-success">${res.message}</span>`)
                        btnSave.addClass('disabled')
                        if (res.data == 'add') {
                            total_add++;
                        } else {
                            total_update++;
                        }
                    } else {
                        item.status = STATUS_ERROR
                        titleProgress.html(`<span class="text-danger">${res.message}</span>`)
                    }
                },
                error: (err) => {
                    item.status = STATUS_ERROR
                    titleProgress.html('<span class="text-danger">Lưu thất bại!</span>')
                },
                complete: () => {
                    save_chapter()
                }
            });
        }

        btnGetListChapter.on('click', function() {
            if (!$.trim(linkGet.val())) {
                return $.toastShow("Vui lòng nhập link truyện muốn leech", {
                    type: "error",
                    timeout: 3000
                });
            }

            stopGetlink = true

            $(this).attr('data-html', $(this).html())
            $(this).addClass("disabled").html("Getting...");
            linkGet.addClass("disabled");

            btnRenameAll.removeClass("disabled");
            btnReupload.addClass("disabled");
            btnStop.addClass("disabled");
            btnRetry.addClass("disabled");
            btnSave.addClass("disabled");


            contentGetLink.show();
            contentReupload.hide()

            $.ajax({
                url: '<?=appendUrlApi(RouteMap::get('tool_leech', ['block' => toolLeechController::BLOCK_GET_MANGA]));?>',
                type: "POST",
                data: {
                    <?=toolLeechController::INPUT_LINK;?>: linkGet.val(),
                },
                dataType: "json",
                success: (res) => {
                    if (res?.code === 200 && res?.data?.length) {
                        REFERER = res.message;
                        let items = res.data.map(o =>  ({ ...o, status: STATUS_PENDING }))
                        const currentLink = localStorage.getItem(storageKeyLink) || ''
                        if (currentLink == linkGet.val()) {
                            items.forEach(o => {
                                const saveItem = ITEMS.find(i => i.link == o.link);
                                if (saveItem) {
                                    if (saveItem.images) {
                                        saveItem.images.map(i => {
                                            i.status = i.status != STATUS_COMPLETE ? STATUS_PENDING : STATUS_COMPLETE
                                            return i
                                        })
                                    }
                                    o.images = saveItem.images
                                    o.status = saveItem.status
                                }
                            })
                        } else {
                            clearItems()
                            saveLink(linkGet.val())
                            saveReferer(REFERER)
                        }
                        ITEMS = items
                        render_list_getlink_chapter(contentGetLink)
                    } else {
                        $.toastShow(res?.message || "Không thể getlink ảnh", {
                            type: "error",
                            timeout: 3000
                        });
                    }
                },
                error: (err) => {
                    $.toastShow("Có lỗi xảy ra. Vui lòng thử lại sau ít phút", {
                        type: "error",
                        timeout: 3000
                    });
                },
                complete: () => {
                    btnGetListChapter.removeClass("disabled").html(btnGetListChapter.attr('data-html'));
                    linkGet.removeClass("disabled");
                }
            });

        })

        btnSelectChapter.on('click', function() {
            contentGetLink.hide()
            contentReupload.show()
            btnReupload.addClass("disabled");
            
            const selectedLink = []
            $(role_multiple_selected+":checked").each(function(){
				selectedLink.push($(this).val())
			});

            ITEMS = ITEMS.filter(item => selectedLink.some(o => o == item.link)).reverse()

            ITEMS.map(o => {
                o.status = o?.images?.length ? STATUS_COMPLETE : STATUS_PENDING
                return o
            })

            $("#multiple_selected_all").prop('checked', false);
            btnSelectChapter.addClass('disabled')

            stopGetlink = false
            if (ITEMS.every(item => item?.images?.every(o => o.status == STATUS_COMPLETE))) {
                btnSave.removeClass('disabled')
                btnRetry.addClass('disabled')
                btnReupload.addClass('disabled')
                btnStop.addClass('disabled')
                ITEMS = ITEMS.map(o => ({...o, status: STATUS_COMPLETE}))
            }
            get_link_chapter()
        })

        btnRenameAll.on('click', function() {
            if (!ITEMS.length) {
                return
            }

            const form = $(`
            <div>
                <div class="dialog-label">Tìm kiếm:</div>
                <div class="form-group">
                    <div class="form-control">
                        <input class="form-input" placeholder="Tìm kiếm ký tự muốn thay thế">
                        <div class="label-desc">Có phân biệt chữ hoa, chữ thường</div>
                    </div>
                </div>
                <div class="dialog-label mt-3">Thay thế:</div>
                <div class="form-group">
                    <div class="form-control">
                        <input class="form-input" placeholder="Ký tự thay thế">
                    </div>
                </div>
            </div>`);

            $.dialogShow({
                title: "Đổi tên tất cả",
                content: form,
                button: {
                    confirm: "Thay thế",
                    cancel: "Huỷ",
                },
                bgHide: false,
                hideButton: false,
                isCenter: true,
                minWidth: 500,
                onBeforeConfirm: async function () {
                    const find = form.find('input')[0].value
                    const replace = form.find('input')[1].value

                    if (find == '') {
                        $.toastShow("Vui lòng nhập ký tự muốn tìm kiếm", {
                            type: "error",
                            timeout: 3000
                        });
                        return false;
                    }

                    let count = 0
                    ITEMS.forEach(o => {
                        if (o.name.includes(find)) {
                            o.name = o.name.replaceAll(find, replace)
                            o.target.find('[role="name"] > a').html(o.name)
                            count++
                        }
                    })

                    saveItems();

                    $.toastShow(`Có ${count} chapter chịu ảnh hưởng`, {
                        type: "success",
                        timeout: 3000
                    });
                    return true;
                },
            });
        })

        $(document).on('click', '[role="btn-edit-chapter"]', function() {
            const item = ITEMS.find(o => o.target[0] == $(this).parents('tr')[0])
            if (!item) {
                return
            }

            const form = $(`
            <div>
                <div class="dialog-label">Tên chapter:</div>
                <div class="form-group">
                    <div class="form-control">
                        <input class="form-input" placeholder="Tên chapter" value="${item.name}">
                    </div>
                </div>
                <div class="dialog-label mt-3">Link ảnh:</div>
                <div class="form-group">
                    <div class="form-control">
                        <textarea class="form-textarea" rows="10" wrap="off" placeholder="Mỗi ảnh cách nhau bởi dấu xuống dòng">${item?.images?.map(i => i.link)?.join("\n") || ''}</textarea>
                    </div>
                </div>
            </div>`);

            $.dialogShow({
                title: "Chỉnh sửa chapter",
                content: form,
                button: {
                    confirm: "Lưu lại",
                    cancel: "Huỷ",
                },
                bgHide: false,
                hideButton: false,
                isCenter: true,
                minWidth: 1000,
                onBeforeConfirm: async function () {
                    const input = $.trim(form.find('input').val())
                    const textarea = $.trim(form.find('textarea').val())

                    if (input == '') {
                        $.toastShow("Tên chapter không được bỏ trống", {
                            type: "error",
                            timeout: 3000
                        });
                        return false;
                    }
                    else if (ITEMS.some(o => o != item && o.name == input)) {
                        $.toastShow("Tên chapter đã tồn tại trong danh sách", {
                            type: "error",
                            timeout: 3000
                        });
                        return false;
                    }
                    else if (textarea == '') {
                        $.toastShow("Link ảnh không được bỏ trống", {
                            type: "error",
                            timeout: 3000
                        });
                        return false;
                    }
                    else if (textarea.split("\n").some(o => /^https?:\/\//i.test(o.link))) {
                        $.toastShow("Link ảnh có chứa đường dẫn không hợp lệ", {
                            type: "error",
                            timeout: 3000
                        });
                        return false;
                    }

                    item.name = input

                    const images = []
                    textarea.split("\n").forEach((o, i) => {
                        const current_image = item.images.find(img => img.link == o);
                        images.push({index: i, link: o, status: current_image?.status || STATUS_PENDING})
                    })
                    item.images = images
                    item.target.find('[role="name"] > a').html(item.name)
                    item.target.find('[role="process"]').html(`${images.filter(o => o.status == STATUS_COMPLETE).length}/${item.images.length}`)
                    
                    saveItems()
                    render_status_chapter(item)
                    if (ITEMS.some(o => o?.images?.some(i => i.status == STATUS_UPLOADING))) {
                        start_reupload()
                    }
                    return true;
                },
            });
        })

        $(document).on('click', '[role="btn-delete-chapter"]', function() {
            const item = ITEMS.find(o => o.target[0] == $(this).parents('tr')[0])
            if (!item) {
                return
            }
            item.target.remove();

            ITEMS = ITEMS.filter(o => o.link != item.link)
            if (ITEMS.every(item => item?.images?.every(o => o.status == STATUS_PENDING))) {
                btnRetry.addClass('disabled')
                btnReupload.removeClass('disabled')
            }
            else if (ITEMS.every(item => item?.images?.every(o => o.status == STATUS_COMPLETE))) {
                btnRetry.addClass('disabled')
                btnReupload.addClass('disabled')
                btnStop.addClass('disabled')
                btnSave.removeClass('disabled')
            }
            else {
                btnRetry.addClass('disabled')
                btnReupload.removeClass('disabled')
                btnStop.addClass('disabled')
                btnSave.addClass('disabled')
            }
            render_status_chapter(item)
        })

        btnReupload.on("click", () => {
            stopReupload = false
            ITEMS.forEach(o => {
                o.status = STATUS_PENDING
            })
            start_reupload();
        });

        btnStop.on("click", () => {
            stopReupload = true
            ITEMS.forEach((chapter) => {
                if (chapter.status != STATUS_COMPLETE) {
                    chapter.status = STATUS_ERROR
                    chapter.images.forEach(o => {
                        if (o?.xhr) {
                           o.xhr.abort === 'function' && o.xhr.abort();
                        }  
                        if (o.status != STATUS_COMPLETE) {
                            o.status = STATUS_ERROR;
                        }          
                    })
                    render_status_chapter(chapter)             
                }
            });

            $('[role="btn-edit-chapter"]').removeClass('disabled')
            btnGetListChapter.removeClass("disabled");
            btnStop.addClass("disabled");
            btnReupload.addClass("disabled");
            btnRetry.removeClass("disabled");
            btnRenameAll.removeClass("disabled");
            btnSave.addClass("disabled");
        });

        btnRetry.on("click", () => {
            if (ITEMS.some(o => !o?.images?.length)) {
                ITEMS.forEach(o => {
                    if (o.status == STATUS_ERROR) {
                        o.status = STATUS_PENDING;
                    }
                })
                return get_link_chapter()
            }

            const lst_errors = ITEMS.filter((o) => o.status != STATUS_COMPLETE || o?.images?.some(i => i.status != STATUS_COMPLETE));
            lst_errors.forEach((item) => {
                item.status = STATUS_PENDING;
                item.images.forEach(o => {
                    if (o.status == STATUS_ERROR) {
                        o.status = STATUS_PENDING;
                    }
                }) 
            });
            stopReupload = false
            render_list_reupload_chapter()
            start_reupload();
        });

        btnSave.on('click', function() {
            btnRenameAll.addClass('disabled')
            btnSave.addClass('disabled')
            $('[role="btn-edit-chapter"]').addClass('disabled')
            ITEMS = ITEMS.map(o => ({...o, status: STATUS_PENDING}))
            total_add = 0
            total_update = 0
            save_chapter()
        })
	})
</script>