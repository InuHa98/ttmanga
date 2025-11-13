function Comment(options = {}) {
  this.config = {
    manga_id: options.manga_id || null,
    chapter_id: options.chapter_id || null,
    comment_id: options.comment_id || null,

    total_comment: options.total_comment || ".total-comment",
    comment_editor: options.comment_editor || ".comment-editor",
    comment_submit: options.comment_submit || ".comment-submit",
    comment_loading: options.comment_loading || ".comment-loading",
    comment_container: options.comment_container || ".comment-container",
    comment_pagination: options.comment_pagination || ".comment-pagination",

    validate_selector: options.validate_selector || ".form-control",
    validate_class_error: options.validate_class_error || "error",
    validate_class_message:
      options.validate_class_message || "validate-error-message",
    validate_message_error:
      options.validate_message_error || "Vui lòng nhập nội dung bình luận",

    class_empty_comment: options.class_empty_comment || "empty-comment",
    class_error_comment: options.class_error_comment || "error-comment",

    text_last_edit: options.text_last_edit || "Đã chỉnh sửa",
    text_loading_comment:
      options.text_loading_comment || "Đang tải bình luận...",
    text_error_load:
      options.text_error_load ||
      "Không thể tải bình luận. Vui lòng tải lại trang!!!",
    text_error_delete: options.text_error_delete || "Không thể xoá bình luận",
    text_error_save: options.text_error_save || "Không thể lưu chỉnh sửa",
    text_reply: options.text_reply || "Trả lời",
    text_view_more: options.text_view_more || "Xem thêm",
    text_show_reply: options.text_show_reply || "phản hồi",
    text_hide_reply: options.text_show_reply || "Ẩn phản hồi",
    text_edit: options.text_edit || "Sửa bình luận",
    text_delete: options.text_delete || "Xoá bình luận",
    text_delete_dialog:
      options.text_delete_dialog || "Bạn thực sự muốn xoá bình luận này?",
    text_cancel: options.text_cancel || "Cancel",
    text_submit: options.text_submit || "Continue",
    text_save: options.text_save || "Save",

    meme_sources: options.meme_sources || [],

    editor_theme: options.editor_theme || null,
    ajax_url: options.ajax_url || null,
    onInit: options.onInit || function () {},
    onSubmit: options.onSubmit || function () {},
    onDelete: options.onDelete || function () {},
    onEdit: options.onEdit || function () {},
  };

  var _self = this;
  var _config = this.config;

  typeof _config.onInit === "function" && _config.onInit(_self);

  var total_comment = $(_config.total_comment);
  var comment_editor = $(_config.comment_editor);
  var comment_loading = $(_config.comment_loading);
  var comment_container = $(_config.comment_container);
  var comment_pagination = $(_config.comment_pagination);

  var reply_editor = comment_editor.clone();

  var textarea_comment = comment_editor.find("textarea");
  if (textarea_comment[0]) {
    textarea_comment[0].id = "textarea_comment";
  }

  if (comment_editor[0] && !comment_editor[0].id) {
    comment_editor[0].id = "form_comment";
  }

  if (reply_editor[0] && !reply_editor[0].id) {
    reply_editor[0].id = "form_reply";
  }

  var id_textarea = 0;
  var editor_id;

  var isLoading = false;
  var current_page = 1;

  var class_comment_item = "comment-item",
    class_comment_wrapper = "comment-wrapper",
    class_comment_wrapper_body = "comment-wrapper__body",
    class_pagination_active = "active",
    class_reply = "reply",
    class_show_reply = "reply-show-more",
    class_hide_reply = "reply-hide-more",
    class_replies_container = "replies-container",
    class_reverse_replies = "reverse",
    class_replies_loading = "replies-loading",
    class_reply_editor = "comment-wrapper__editor",
    class_reply_focus = "focus-reply",
    class_submiting = "submiting",
    class_new_comment = "new-comment",
    class_background = "comment-background",
    class_comment_dialog = "comment-dialog";

  var role_edit = "comment-edit",
    role_delete = "comment-delete";

  var duration_background = 300;
  var comment_section = comment_container.parent();

  var id_form_edit = "form_edit";

  function setup_editor_comment() {
    textarea_editor({
      id: "#" + textarea_comment[0].id,
      theme: _config.editor_theme,
      meme_sources: _config.meme_sources,
      events: {
        input: function (e) {
          $("#" + this.id)
            .parent()
            .find("." + _config.validate_class_message)
            .remove();
        },
      },
    });

    Validator({
      form: "#" + comment_editor[0].id,
      selector: _config.validate_selector,
      class_error: _config.validate_class_error,
      class_message: _config.validate_class_message,
      rules: {
        ["textarea"]: Validator.isRequired(_config.validate_message_error),
      },
      onSubmit: function (data) {
        data.manga_id = _config.manga_id;
        data.chapter_id = _config.chapter_id;
        send_comment(data);
      },
    });
  }

  function setup_editor_reply(refid, chapter_id) {
    textarea_editor({
      id: "#" + editor_id,
      theme: _config.editor_theme,
      meme_sources: _config.meme_sources,
      events: {
        input: function (e) {
          $("#" + this.id)
            .parent()
            .find("." + _config.validate_class_message)
            .remove();
        },
      },
    });

    Validator({
      form: "#" + reply_editor[0].id,
      selector: _config.validate_selector,
      class_error: _config.validate_class_error,
      class_message: _config.validate_class_message,
      rules: {
        ["textarea"]: Validator.isRequired(_config.validate_message_error),
      },
      onSubmit: function (data) {
        data.refid = refid;
        data.manga_id = _config.manga_id;
        data.chapter_id = _config.chapter_id || chapter_id || null;
        send_reply(refid, data);
      },
    });
  }

  function setup_editor_edit(id) {
    textarea_editor({
      id: "#" + editor_id,
      theme: _config.editor_theme,
      meme_sources: _config.meme_sources,
      events: {
        input: function (e) {
          $("#" + this.id)
            .parent()
            .find("." + _config.validate_class_message)
            .remove();
        },
        change: function (e) {
          $("#" + this.id).val(this.getContent());
        },
      },
    });

    Validator({
      form: "#" + id_form_edit,
      selector: _config.validate_selector,
      class_error: _config.validate_class_error,
      class_message: _config.validate_class_message,
      rules: {
        ["textarea"]: Validator.isRequired(_config.validate_message_error),
      },
      onSubmit: function (data) {
        data.id = id;
        send_edit(data);
      },
    });
  }

  function render_html_pagination(data = null) {
    if (data["total_page"] <= 1) {
      return null;
    }

    var html = "";

    html +=
      '<span class="' +
      (data["previous"] == false ? "disabled" : "") +
      '" data-page="' +
      data["previous"] +
      '">‹ Trước</span>';

    data["pages"].forEach(function (page) {
      if (page == data["current_page"]) {
        html += '<span class="active">' + page + "</span>";
      } else {
        html +=
          '<span class="' +
          (isNaN(page) ? "disabled" : "") +
          '" data-page="' +
          page +
          '">' +
          page +
          "</span>";
      }
    });

    html +=
      '<span class="' +
      (data["next"] == false ? "disabled" : "") +
      '" data-page="' +
      data["next"] +
      '">Sau ›</span>';

    return html;
  }

  function render_html_comment(comments, is_reply = false) {
    var html_comment = "";
    comments.forEach(function (comment) {
      var isReply = comment.reply > 0;
      html_comment +=
        '\
            <div class="' +
        class_comment_item +
        '" data-id="' +
        comment.id +
        '" data-chapter="' +
        (comment.chapter && comment.chapter.id) +
        '">\
                <div class="comment-avatar user-avatar" data-text="' +
        comment.first_name +
        '" style="--bg-avatar: ' +
        comment.bg_avatar +
        '">\
                    <img src="' +
        comment.avatar +
        '" />\
                </div>\
                <div class="' +
        class_comment_wrapper +
        " " +
        (isReply ? "has-reply" : "") +
        '">\
                    <div class="comment-wrapper__body">\
                        <div class="comment-wrapper__body-text">\
                            <div class="username">\
                                <a target="_blank" href="' +
        comment.profile +
        '" style="color: ' +
        comment.color +
        '">' +
        comment.username +
        '</a>\
                            </div>\
                            <div class="text">' +
        comment.text +
        "</div>\
                        </div>";

      if (comment.is_edit == true || comment.is_delete == true) {
        html_comment +=
          '\
                <div class="comment-wrapper__body-action">\
                    <div class="drop-menu">\
                        <button class="drop-menu__button">\
                            <i class="fa fa-ellipsis-v"></i>\
                        </button>\
                        <ul class="drop-menu__content">' +
          (comment.is_edit == true
            ? '<li role="' +
              role_edit +
              '"><i class="fas fa-edit"></i> ' +
              _config.text_edit +
              "</li>"
            : "") +
          (comment.is_delete == true
            ? '<li role="' +
              role_delete +
              '" class="text-danger" data-reason="' +
              comment.reason_delete +
              '"><i class="fa fa-trash"></i> ' +
              _config.text_delete +
              "</li>"
            : "") +
          "</ul>\
                    </div>\
                </div>";
      }

      html_comment +=
        '\
            </div>\
            <div class="comment-wrapper__footer">\
                ' +
        (comment.is_reply
          ? '<div class="' + class_reply + '"> ' + _config.text_reply + "</div>"
          : "") +
        '\
                <div class="time">' +
        (comment.edit
          ? _config.text_last_edit + ": " + comment.edit
          : comment.time) +
        "</div>\
                " +
        (!is_reply && !_config.chapter_id && comment.chapter
          ? '<span class="chapter"><a target="_blank" href="' +
            comment.chapter["link"] +
            '">' +
            htmlEntities(comment.chapter["name"]) +
            "</a></span>"
          : "") +
        '\
            </div>\
            <div class="comment-wrapper__reply">';

      if (is_reply != true) {
        html_comment +=
          '\
                <div class="' +
          class_hide_reply +
          '" style="display: none">\
                    <i class="fas fa-chevron-up"></i>\
                    <span>' +
          _config.text_hide_reply +
          "</span>\
                </div>";

        html_comment += '<div class="' + class_replies_container + '"></div>';

        html_comment +=
          '\
                <div class="' +
          class_show_reply +
          '" style="display: ' +
          (isReply ? "block" : "none") +
          '" data-replies="' +
          comment.reply +
          '">\
                    <i class="fas fa-level-up"></i>\
                    <span>' +
          comment.reply +
          " " +
          _config.text_show_reply +
          "</span>\
                </div>";

        html_comment +=
          '\
                <div class="replies-loading hide">\
                    <div class="animation-spinner">\
                        <div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>\
                    </div>\
                    <span>' +
          _config.text_loading_comment +
          "</span>\
                </div>";
      }

      html_comment += "</div>";

      if (is_reply != true) {
        html_comment += '<div class="' + class_reply_editor + '"></div>';
      }

      html_comment +=
        "\
                </div>\
            </div>";
    });
    return html_comment;
  }

  function loading_comments(callback) {
    isLoading = true;
    comment_loading.show();
    comment_container.html("");
    add_background();

    $.ajax({
      type: "GET",
      url: _config.ajax_url,
      data: {
        manga_id: _config.manga_id,
        chapter_id: _config.chapter_id,
        comment_id: _config.comment_id,
        page: current_page,
      },
      dataType: "json",
      cache: false,
      success: function (response) {
        if (response.code == 404) {
          comment_container.html(
            '<div class="' +
              _config.class_empty_comment +
              '">' +
              response.message +
              "</div>"
          );
          total_comment.html("0");
          comment_pagination.hide();
        } else if (response.code == 200) {
          total_comment.html(response.data.all_comment);
          comment_pagination
            .html(render_html_pagination(response.data.pagination))
            .show();
          comment_container.html(render_html_comment(response.data.items));
          typeof callback === "function" && callback(response);
        }
      },
      error: function () {
        comment_container.html(
          '<div class="' +
            _config.class_error_comment +
            '">' +
            _config.text_error_load +
            "</div>"
        );
      },
      complete: function () {
        isLoading = false;
        comment_loading.hide();
        remove_background();
      },
    });
  }

  function loading_replies(refid, container, callback) {
    isLoading = true;

    var loading = container.siblings("." + class_replies_loading);
    var show_more = container.siblings("." + class_show_reply);
    var hide_replies = container.siblings("." + class_hide_reply);

    loading.removeClass("hide");
    show_more.hide();
    add_background();

    var page = container.attr("data-page") || 0;
    var isReverse = container.parent().hasClass(class_reverse_replies);

    $.ajax({
      type: "GET",
      url: _config.ajax_url,
      data: {
        refid: refid,
        reverse: isReverse,
        page: parseInt(page) + 1,
      },
      dataType: "json",
      cache: false,
      success: function (response) {
        if (response.code == 404) {
          container.html(
            '<div class="' +
              _config.class_empty_comment +
              '">' +
              response.message +
              "</div>"
          );
          total_comment.html("0");
        } else if (response.code == 200) {
          var html = render_html_comment(response.data.items, true);

          page = response.data.pagination.current_page;
          container.attr("data-page", page);

          if (isReverse) {
            container.prepend(html);
          } else {
            container.append(html);
          }
          total_comment.html(response.data.all_comment);
          hide_replies.show();

          show_more.data("replies", response.data.total);

          if (page < response.data.pagination.total_page) {
            var more_items =
              response.data.total -
              container.find("." + class_comment_item).length;
            show_more.html(
              "<span>... " +
                _config.text_view_more +
                " " +
                more_items +
                " " +
                _config.text_show_reply +
                "</span>"
            );
            show_more.show();
          }

          typeof callback === "function" && callback(response);
        }
      },
      error: function () {
        container.html(
          '<div class="' +
            _config.class_error_comment +
            '">' +
            _config.text_error_load +
            "</div>"
        );
        show_more.show();
      },
      complete: function () {
        isLoading = false;
        loading.addClass("hide");
        remove_background();
      },
    });
  }

  function send_comment(data) {
    if (isLoading == true) {
      return;
    }
    isLoading = true;

    var submit = comment_editor.find(_config.comment_submit);
    submit.removeClass(class_submiting).addClass(class_submiting);
    add_background();

    $.ajax({
      type: "POST",
      url: _config.ajax_url,
      data: data,
      dataType: "json",
      cache: false,
      success: function (response) {
        if (response.code == 200) {
          tinyMCE.activeEditor.setContent("");

          current_page = 1;

          loading_comments(function (response) {
            var first_item = $("." + class_comment_item + ":first-child");
            first_item.addClass(class_new_comment);
          });

          return;
        }
        $.toastShow(response.message, {
          type: "error",
          timeout: 3000,
        });
      },
      error: function () {
        $.toastShow(_config.text_error_load, {
          type: "error",
          timeout: 3000,
        });
      },
      complete: function () {
        submit.removeClass(class_submiting);
        isLoading = false;
        remove_background();
      },
    });
  }

  function send_reply(refid, data) {
    if (isLoading == true) {
      return;
    }
    isLoading = true;

    var submit = reply_editor.find(_config.comment_submit);
    submit.removeClass(class_submiting).addClass(class_submiting);
    add_background();
    var container = $(
      "." + class_comment_item + '[data-id="' + refid + '"]'
    ).find("." + class_replies_container);

    $.ajax({
      type: "POST",
      url: _config.ajax_url,
      data: data,
      dataType: "json",
      cache: false,
      success: function (response) {
        if (response.code == 200) {
          tinyMCE.activeEditor.setContent("");

          container.attr("data-page", 0);
          container.html("");
          container
            .parent()
            .removeClass(class_reverse_replies)
            .addClass(class_reverse_replies);

          loading_replies(refid, container);

          return;
        }
        $.toastShow(response.message, {
          type: "error",
          timeout: 3000,
        });
      },
      error: function () {
        $.toastShow(_config.text_error_load, {
          type: "error",
          timeout: 3000,
        });
      },
      complete: function () {
        submit.removeClass(class_submiting);
        isLoading = false;
        remove_background();
      },
    });
  }

  function send_edit(data) {
    if (isLoading == true) {
      return;
    }
    isLoading = true;

    var comment_dialog = $("." + class_comment_dialog);
    comment_dialog.removeClass(class_submiting).addClass(class_submiting);

    $.ajax({
      type: "PUT",
      url: _config.ajax_url,
      data: data,
      dataType: "json",
      cache: false,
      success: function (response) {
        if (response.code == 200) {
          loading_comments(function () {
            $.toastShow(response.message, {
              type: "success",
              timeout: 3000,
            });
          });
          remove_background();
          return;
        }
        $.toastShow(response.message, {
          type: "error",
          timeout: 3000,
        });
      },
      error: function () {
        $.toastShow(_config.text_error_save, {
          type: "error",
          timeout: 3000,
        });
      },
      complete: function () {
        isLoading = false;
        comment_dialog.removeClass(class_submiting);
      },
    });
  }

  function add_background(callback, duration_time) {
    var duration = duration_time || duration_background;

    var selector_background = $('<div class="' + class_background + '"></div>');
    comment_section.append(selector_background);
    selector_background.fadeIn(duration, function () {
      $(this).css("opacity", 1);
      typeof callback === "function" && callback($(this));
    });
  }

  function remove_background(callback) {
    var selector_background = comment_section.find("." + class_background);
    selector_background.css("opacity", 0);
    typeof callback === "function" && callback(selector_background);
    selector_background.remove();
  }

  function dialog(options) {
    if (!options) {
      return;
    }

    var title = options.title || "Dialog";
    var body = options.body || null;
    var class_dialog = options.class || "";
    var with_ = options.with || null;
    var cancel = options.cancel || "Cancel";
    var submit = options.submit || "Submit";
    var onInit = options.onInit || function () {};
    var onBeforeSubmit = options.onBeforeSubmit || function () {};
    var onBeforeCancel = options.onBeforeCancel || function () {};
    var onSubmit = options.onSubmit || function () {};
    var onCancel = options.onCancel || function () {};

    if (!isNaN(with_)) {
      with_ = with_ + "px";
    }

    var dialog_html = $(
      '\
        <div class="' +
        class_comment_dialog +
        " " +
        class_dialog +
        '" style="' +
        (with_ ? "width: " + with_ : "") +
        '">\
            <div class="comment-dialog__title">' +
        title +
        '</div>\
            <div class="comment-dialog__body"></div>\
            <div class="comment-dialog__footer">\
                <div class="dialog-loading animation-spinner">\
                    <div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>\
                </div>\
                <div role="dialog-cancel" class="cancel">' +
        cancel +
        '</div>\
                <div role="dialog-submit" class="submit">' +
        submit +
        "</div>\
            </div>\
        </div>"
    );
    dialog_html.find(".comment-dialog__body").html(body);

    var delay = (function () {
      var timer = 0;
      return function (callback, ms) {
        clearTimeout(timer);
        timer = setTimeout(callback, ms);
      };
    })();

    var scroll_dialog = function () {
      var offset_top_section_comment =
          comment_section[0].getBoundingClientRect().top,
        window_half_height = $(window).outerHeight() / 2;

      var dialog_offset_top = 0,
        dialog_height = dialog_html.outerHeight(true);

      if (offset_top_section_comment <= window_half_height / 2) {
        dialog_offset_top =
          window_half_height - offset_top_section_comment - dialog_height / 2;
      }

      if (dialog_offset_top < 0) {
        dialog_offset_top = 0;
      }

      if (
        dialog_offset_top + dialog_html.outerHeight(true) >=
        comment_section.outerHeight()
      ) {
        dialog_offset_top =
          comment_section.outerHeight() - dialog_html.outerHeight(true) - 100;
      }

      dialog_html.css({
        top: dialog_offset_top,
      });
    };

    add_background(function (background) {
      background.html(dialog_html);
      typeof onInit === "function" && onInit(background);
      //scroll_dialog();
    });

    $(document)
      .off("scroll.dialog_comment")
      .on("scroll.dialog_comment", function () {
        delay(function () {
          //scroll_dialog();
        }, duration_background);
      });

    $(document)
      .off("click.dialog_delete")
      .on("click.dialog_delete", '[role="dialog-cancel"]', function () {
        var beforeCallback;
        typeof onBeforeCancel === "function" &&
          (beforeCallback = onBeforeCancel());
        if (beforeCallback === false) {
          return;
        }
        dialog_html.css("opacity", 0);
        remove_background(function () {
          typeof onCancel === "function" && onCancel();
        });
      });

    $(document)
      .off("click.dialog_submit")
      .on("click.dialog_submit", '[role="dialog-submit"]', function () {
        var beforeCallback;
        typeof onBeforeSubmit === "function" &&
          (beforeCallback = onBeforeSubmit());
        if (beforeCallback === false) {
          return;
        }
        dialog_html.css("opacity", 0);
        remove_background(function () {
          typeof onSubmit === "function" && onSubmit();
        });
      });
  }

  function delete_comment(id, add_reason) {
    if (isLoading == true) {
      return;
    }

    isLoading = true;

    var reason_delete = add_reason == true || add_reason == "true" || false;

    var body = $("<div><p>" + _config.text_delete_dialog + "</p></div>");

    if (reason_delete) {
      body.append(
        '\
            <div class="dialog-label">Lý do xoá:</div>\
            <div class="form-group">\
                <div class="form-control">\
                    <textarea class="form-textarea" role="reason-delete" placeholder="Nêu rõ lý do xoá bình luận này"></textarea>\
                </div>\
            </div>'
      );
    }

    dialog({
      title: _config.text_delete,
      body: body,
      class: "delete-comment",
      cancel: _config.text_cancel,
      submit: _config.text_submit,
      onCancel: function () {
        isLoading = false;
      },
      onBeforeSubmit: function () {
        var reason = body.find('[role="reason-delete"]').val();
        reason && reason.trim();
        if (reason == "") {
          $.toastShow("Lý do không được bỏ trống", {
            type: "error",
            timeout: 3000,
          });
          return false;
        }
      },
      onSubmit: function () {
        var reason = body.find('[role="reason-delete"]').val();
        reason && reason.trim();

        add_background();
        $.ajax({
          type: "DELETE",
          url: _config.ajax_url,
          data: {
            id: id,
            reason: reason,
          },
          dataType: "json",
          cache: false,
          success: function (response) {
            if (response.code == 200) {
              loading_comments(function () {
                $.toastShow(response.message, {
                  type: "success",
                  timeout: 3000,
                });
              });
              return;
            }
            $.toastShow(response.message, {
              type: "error",
              timeout: 3000,
            });
          },
          error: function () {
            $.toastShow(_config.text_error_delete, {
              type: "error",
              timeout: 3000,
            });
          },
          complete: function () {
            isLoading = false;
            remove_background();
          },
        });
      },
    });
  }

  function edit_comment(id, current_text, callback) {
    editor_id = "textarea_reply_" + id_textarea++;

    var body_dialog =
      '\
        <form id="' +
      id_form_edit +
      '">\
            <div class="form-group">\
                <div class="form-control">\
                    <textarea id="' +
      editor_id +
      '" class="form-textarea" name="text" placeholder="Nhập bình luận..." rows="1" style="height: 50px">' +
      current_text.htmlToBbcode() +
      "</textarea>\
                </div>\
            </div>\
        </form>";

    $(document).ready(function () {
      dialog({
        title: _config.text_edit,
        body: body_dialog,
        cancel: _config.text_cancel,
        with: "94%",
        submit: _config.text_save,
        onInit: function () {
          setup_editor_edit(id);
        },
        onCancel: function () {
          isLoading = false;
        },
        onBeforeSubmit: function () {
          $("#" + id_form_edit).submit();
          return false;
        },
      });
    });
  }

  $(document).ready(function () {
    if (textarea_comment[0]) {
      setup_editor_comment();
    }

    loading_comments();

    comment_container.on("click", "." + class_reply, function () {
      var comment_item = $(this).parents("." + class_comment_item);
      var reply_box = comment_item.find("." + class_reply_editor);
      var manga_id = comment_item.data("id");
      var chapter_id = comment_item.data("chapter");

      var isActive = comment_item.find("form").length > 0;

      $("." + class_comment_item)
        .find("form")
        .remove();
      $("." + class_comment_wrapper).removeClass(class_reply_focus);

      if (!isActive) {
        editor_id = "textarea_reply_" + id_textarea++;
        reply_editor.find("textarea")[0].id = editor_id;

        comment_item.removeClass(class_new_comment);
        $(this)
          .parents("." + class_comment_wrapper)
          .toggleClass(class_reply_focus);
        reply_box.html(reply_editor[0].outerHTML);
        setup_editor_reply(manga_id, chapter_id);
        reply_box[0].scrollIntoView(false);
      }
    });

    comment_pagination.on("click", "span", function () {
      var page = $(this).data("page");
      if (page) {
        $(this)
          .parent()
          .find("." + class_pagination_active)
          .removeClass(class_pagination_active);
        $(this).addClass(class_pagination_active);
        current_page = page;
        loading_comments(function () {
          comment_pagination[0].scrollIntoView(false);
        });
      }
    });

    comment_container.on("click", "." + class_show_reply, function () {
      var id = $(this)
        .parents("." + class_comment_item)
        .data("id");
      var container = $(this).siblings("." + class_replies_container);

      if (id) {
        loading_replies(id, container);
      }
    });

    comment_container.on("click", "." + class_hide_reply, function () {
      var container = $(this).siblings("." + class_replies_container);
      var show_replies = $(this).siblings("." + class_show_reply);
      show_replies.html(
        '<i class="fas fa-level-up"></i><span>' +
          show_replies.data("replies") +
          " " +
          _config.text_show_reply +
          "</span>"
      );
      container.attr("data-page", 0);
      container.html("");
      $(this).parent().removeClass(class_reverse_replies);
      $(this).hide();
      show_replies.show();
    });

    comment_container.on("click", '[role="' + role_edit + '"]', function () {
      var id = $(this)
        .parents("." + class_comment_item)
        .data("id");
      var current_text = $(this)
        .parents("." + class_comment_wrapper_body)
        .find(".text")
        .html();
      edit_comment(id, current_text);
    });

    comment_container.on("click", '[role="' + role_delete + '"]', function () {
      var id = $(this)
        .parents("." + class_comment_item)
        .data("id");
      var reason = $(this).data("reason");

      delete_comment(id, reason);
    });
  });
}
