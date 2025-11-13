$(document).ready(() => {
  $("#tool-leech").on("click", function (e) {
    e.preventDefault();

    const form = $(`
    <div>
        <div class="dialog-label">Nhập link chapter:</div>
        <div class="form-group">
            <div class="form-control">
                <input class="form-input" placeholder="https://">
                <button type="button" class="btn btn--info" role="btn-get-link">Get link</button>
                <div class="label-desc">Hiện tại chỉ hỗ trợ: foxtruyen, truyenqq, mangadex, mto, hangtruyen, minotruyen</div>
            </div>
        </div>
        <div class="dialog-label mt-3">Link ảnh:</div>
        <div class="form-group">
            <div class="form-control">
                <textarea class="form-textarea" rows="10" wrap="off" placeholder="Mỗi ảnh cách nhau bởi dấu xuống dòng"></textarea>
            </div>
        </div>
        <div class="d-flex justify-content-start align-items-center flex-wrap gap-1 mt-3">
            <button type="button" class="btn btn--small btn--success disabled" role="btn-reupload"><i class="fas fa-play"></i> Reupload</button>
            <button type="button" class="btn btn--small btn--danger disabled" role="btn-stop"><i class="fas fa-stop"></i> Stop</button>
            <button type="button" class="btn btn--small btn--warning disabled" role="btn-retry"><i class="fas fa-sync-alt"></i> Retry</button>
        </div>
        <div class="mt-3">
            <div class="dialog-label">Tiến trình: <span role="progress-title">--/--</span></div>
            <div role="progress-log" style="overflow: auto;max-height: 200px;white-space: nowrap;"></div>
        </div>
    </div>`);

    const btnGetLink = form.find('button[role="btn-get-link"]');
    const btnReupload = form.find('button[role="btn-reupload"]');
    const btnStop = form.find('button[role="btn-stop"]');
    const btnRetry = form.find('button[role="btn-retry"]');
    const inputLink = form.find("input");
    const inputTextarea = form.find("textarea");
    const label = form.find('[role="progress-title"]');
    const progress = form.find('[role="progress-log"]');

    let REFERER;
    let REUPLOAD_ITEMS = [];
    let stopReupload = false;

    const render_status = () => {
      const total_pending = REUPLOAD_ITEMS.filter(
        (o) => o.status == "pending"
      ).length;
      const total_error = REUPLOAD_ITEMS.filter(
        (o) => o.status == "error"
      ).length;
      const total_complete = REUPLOAD_ITEMS.filter(
        (o) => o.status == "complete"
      ).length;

      if (total_pending > 0) {
        label
          .removeClass("text-success text-danger")
          .addClass("text-warning")
          .html(`${total_complete}/${REUPLOAD_ITEMS.length}`);
      } else {
        if (total_complete == REUPLOAD_ITEMS.length && total_complete > 0) {
          label
            .removeClass("text-warning text-danger")
            .addClass("text-success")
            .html("Complete!");
        } else if (total_error > 0) {
          label
            .removeClass("text-warning text-success")
            .addClass("text-danger")
            .html(`Error(${total_error})`);
        } else {
          label
            .removeClass("text-warning text-success text-danger")
            .html(`--/--`);
        }
      }

      progress.html(null);
      REUPLOAD_ITEMS.forEach((item) => {
        if (item.status == "pending") {
          progress.append(
            `<div><span class="text-warning">Pending:</span> ${item.link}</div>`
          );
        } else if (item.status == "uploading") {
          progress.append(
            `<div><span class="text-info">Uploading</span>: ${item.link}</div>`
          );
        } else if (item.status == "error") {
          progress.append(
            `<div><span class="text-danger">Error</span>: ${item.link}</div>`
          );
        } else {
          progress.append(
            `<div><span class="text-success">${item.link}</span></div>`
          );
        }
      });
      $.dialogResize();
    };

    const start_reupload = () => {
      const lst_links = REUPLOAD_ITEMS.filter((o) => o.status == "pending");
      if (lst_links.length < 1 || stopReupload) {
        if (REUPLOAD_ITEMS.some((o) => o.status == "error")) {
          btnRetry.removeClass("disabled");
        } else {
          btnRetry.addClass("disabled");
        }
        inputTextarea.removeClass("disabled");
        btnReupload.removeClass("disabled");
        btnGetLink.removeClass("disabled");
        return btnStop.addClass("disabled");
      }

      if (REUPLOAD_ITEMS.some((o) => o?.status == "uploading")) {
        return;
      }

      const item = lst_links.shift();

      if (item.status == "pending") {
        btnGetLink.addClass("disabled");
        btnRetry.addClass("disabled");
        btnReupload.addClass("disabled");
        btnStop.removeClass("disabled");
        inputTextarea.addClass("disabled");

        item.status = "uploading";
        render_status();
        item.xhr = $.ajax({
          url: URL_REUPLOAD_IMAGE,
          type: "POST",
          data: {
            link: item.link,
            name: REUPLOAD_ITEMS.indexOf(item),
            referer: REFERER,
          },
          dataType: "json",
          success: function (res) {
            if (res?.code === 200 && res?.data) {
              item.status = "complete";
              item.link = res.data;
            } else {
              item.status = "error";
            }
          },
          error: function (err) {
            item.status = "error";
          },
          complete: function () {
            render_status();
            start_reupload();
          },
        });
      }
    };

    btnGetLink.on("click", () => {
      if (!$.trim(inputLink.val())) {
        return $.toastShow("Vui lòng nhập link chapter muốn get link ảnh", {
          type: "error",
          timeout: 3000,
        });
      }

      REUPLOAD_ITEMS = [];
      render_status();

      btnGetLink.addClass("disabled").html("Getting...");
      inputTextarea.addClass("disabled");
      inputTextarea.val(null).change();

      $.ajax({
        url: URL_GET_CHAPTER,
        type: "POST",
        data: {
          link: inputLink.val(),
        },
        dataType: "json",
        success: function (res) {
          if (res?.code === 200 && res?.data?.length) {
            let images = res.data;
            inputTextarea.val(images.join("\n")).change();
            REFERER = res.message;
            render_status();
          } else {
            $.toastShow(res?.message || "Không thể getlink ảnh", {
              type: "error",
              timeout: 3000,
            });
          }
        },
        error: function (err) {
          $.toastShow("Có lỗi xảy ra. Vui lòng thử lại sau ít phút", {
            type: "error",
            timeout: 3000,
          });
        },
        complete: function () {
          btnGetLink.removeClass("disabled").html("Get link");
          inputTextarea.removeClass("disabled");
        },
      });
    });

    inputTextarea.on("change", function () {
      const value = $.trim($(this).val());
      btnRetry.addClass("disabled");
      btnStop.addClass("disabled");
      if (value == "") {
        btnReupload.addClass("disabled");
      } else {
        btnReupload.removeClass("disabled");
      }
      REFERER = null;
      const arr = $.trim(inputTextarea.val()).split("\n");
      REUPLOAD_ITEMS = REUPLOAD_ITEMS.filter((o) =>
        arr.some((a) => a == o.link)
      );
    });

    btnReupload.on("click", () => {
      REUPLOAD_ITEMS = [];
      $.trim(inputTextarea.val())
        .split("\n")
        .forEach((link) => {
          REUPLOAD_ITEMS.push({
            link,
            status: "pending",
          });
        });
      stopReupload = false;
      start_reupload();
    });

    btnStop.on("click", () => {
      stopReupload = true;
      REUPLOAD_ITEMS.forEach((o) => {
        if (o?.xhr) {
          o.xhr.abort === "function" && o.xhr.abort();
        }
        if (o.status != "complete") {
          o.status = "error";
        }
      });

      btnGetLink.removeClass("disabled");
      btnStop.addClass("disabled");
      btnReupload.removeClass("disabled");
      btnRetry.removeClass("disabled");
      inputTextarea.removeClass("disabled");
    });

    btnRetry.on("click", () => {
      const lst_errors = REUPLOAD_ITEMS.filter((o) => o.status == "error");
      lst_errors.forEach((item) => {
        item.status = "pending";
      });
      stopReupload = false;
      start_reupload();
    });

    $.dialogShow({
      title: "Tool leech",
      content: form,
      button: {
        confirm: "Nhập ảnh",
        cancel: "Huỷ",
      },
      bgHide: false,
      hideButton: false,
      isCenter: true,
      minWidth: 1000,
      onBeforeConfirm: async function () {
        btnStop.click();
        if (REUPLOAD_ITEMS.some((o) => o.status != "complete")) {
          $.toastShow("Vui lòng hoàn thành reupload ảnh trước", {
            type: "warning",
            timeout: 3000,
          });
          return false;
        }

        REUPLOAD_ITEMS.forEach((o) => {
          const item = { type: "image", image: o.link, link: o.link };
          render_item(item, ITEMS.length + 1);
          ITEMS.push(item);
        });
        return true;
      },
    });
  });
});
