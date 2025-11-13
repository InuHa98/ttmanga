/*!
 * toast-dialog.js - v1.0.0
 * https://github.com/ikuru001/toast-dialog
 * @author inuHa <ttmanga.com>
 */
(function ($) {
  const role = {
    close: "dialog_close",
    cancel: "dialog_cancel",
    confirm: "dialog_confirm",
    background: "dialog_background",
    section: "dialog_section",
    toast_wrapper: "toast_wrapper",
    toast_section: "toast_section",
    toast_close: "toast_close",
  };

  const setRole = function (role) {
    return 'role="' + role + '"';
  };
  const getRole = function (role) {
    return "[role=" + role + "]";
  };

  const fadeDialog = 150,
    fadeToast = 750;

  var minWidth = 0;

  var settings = {
    class_toast: {
      toast_wrapper: "dialog-toast-wrapper",
      toast_message: "dialog-toast-message",
      toast_icon: "dialog-toast-icon",
      toast_close: "dialog-toast-close",
      toast_default: "dialog-toast-default",
      toast_success: "dialog-toast-success",
      toast_warning: "dialog-toast-warning",
      toast_error: "dialog-toast-error",
      toast_info: "dialog-toast-info",
      toast_effect_enter: "dialog-toast-enter",
      toast_effect_leave: "dialog-toast-leave",
      toast_timeout: "dialog-toast-timeout",
    },
  };

  var mutationObserver;

  var Dialog = {
    settings: {
      id: "Dialog-section",
      title: null,
      content: null,
      desc: null,
      close: " × ",
      isCenter: false,
      hideHeader: false, // ẩn header
      hideClose: false, // ẩn button close
      minWidth: 500,
      maxWidth: null,
      hideButton: false, // ẩn button
      noconfirm: false, // disable confirm
      bgHide: true, //ẩn dialog khi bấm vào background
      style: {},
      class: {
        header: "dialog-header",
        body: "dialog-body",
        content: "dialog-content",
        desc: "dialog-desc",
        footer: "dialog-footer",
        close: "dialog-close",
        confirm: "dialog-confirm",
        cancel: "dialog-cancel",
        background: "dialog-background",
        section: "dialog-section",
        disable_scroll: "disable-scroll",
      },
      button: {
        confirm: "Ok",
        cancel: "Cancel",
      },
      onInit: function () {},
      onBeforeClose: function () {},
      onBeforeConfirm: function () {},
      onBeforeCancel: function () {},
    },
    show: function (opts) {
      settings = $.extend(settings, Dialog.settings, opts || {});
      var background = $(getRole(role.background));

      var btnConfirm = "",
        btnCancel = "",
        style_content = "",
        style_section = "";

      if (settings.hideButton === false) {
        if (settings.button.confirm) {
          btnConfirm =
            '<button class="' +
            settings.class.confirm +
            '" ' +
            setRole(role.confirm) +
            ">" +
            settings.button.confirm +
            "</button>";
        }

        if (settings.button.cancel) {
          btnCancel =
            '<button class="' +
            settings.class.cancel +
            '" ' +
            setRole(role.cancel) +
            ">" +
            settings.button.cancel +
            "</button>";
        }
      }

      if (typeof settings.style === "object") {
        for (const css in settings.style) {
          style_content += css + ": " + settings.style[css] + ";";
        }
      }

      if (settings.minWidth !== null) {
        style_section +=
          "min-width:" +
          settings.minWidth +
          (typeof settings.minWidth === "string" ? "" : "px") +
          ";";
      }

      if (settings.maxWidth !== null) {
        style_section +=
          "max-width:" +
          settings.maxWidth +
          (typeof settings.maxWidth === "string" ? "" : "px") +
          ";";
      }

      var content = $('<div class="' + settings.class.content + '"></div>');
      content.append(settings.content);

      if (settings.desc) {
        content.append(
          '<div class="' + settings.class.desc + '">' + settings.desc + "</div>"
        );
      }

      var html_background =
        '<div class="' +
        settings.class.background +
        '" ' +
        setRole(role.background) +
        "></div>";
      var html_section = $(
        "\
				<div " +
          setRole(role.section) +
          ' class="' +
          settings.class.section +
          '" ' +
          (settings.id ? ' id="' + settings.id + '"' : "") +
          ' style="' +
          style_section +
          '">\
					' +
          (settings.hideHeader
            ? ""
            : '<div class="' +
              settings.class.header +
              '">' +
              settings.title +
              "</div>") +
          '\
					<div class="' +
          settings.class.body +
          '" style="' +
          style_content +
          '"></div>\
					<div ' +
          setRole(role.close) +
          ' class="' +
          settings.class.close +
          '" ' +
          (settings.hideClose ? ' style="display: none;"' : "") +
          ">" +
          settings.close +
          "</div>\
				</div>"
      );

      html_section.find("." + settings.class.body).html(content);
      html_section.append(
        '<div class="' +
          settings.class.footer +
          '">' +
          btnConfirm +
          btnCancel +
          "</div>"
      );

      if (background.length) {
        background.stop().fadeIn();
        $(getRole(role.section)).remove();
      } else {
        $("body").append(html_background);
      }

      $("body").addClass(settings.class.disable_scroll);
      $("body").append(html_section);
      $(getRole(role.background)).fadeIn();
      $(getRole(role.section)).show();
      settings.onInit && settings.onInit();

      minWidth = parseInt($(getRole(role.section)).css("min-width"), 10);

      Dialog.resize(true);
      $(window)
        .off("resize.toast_dialog")
        .on("resize.toast_dialog", function () {
          Dialog.resize(false);
        });

      mutationObserver = new MutationObserver(function (mutations) {
        Dialog.resize(false);
      });

      mutationObserver.observe(
        $(getRole(role.section)).find("." + settings.class.content)[0],
        {
          attributes: true,
          childList: true,
        }
      );
    },
    hide: function (callback) {
      var background = $(getRole(role.background));
      var section = $(getRole(role.section));

      var offset = section[0].getBoundingClientRect();

      section.animate({
        top: offset.top + fadeDialog + "px",
        opacity: 0,
      });

      background.fadeOut(function () {
        $("body").removeClass(settings.class.disable_scroll);
        $(window).off("resize.toast_dialog");
        $(getRole(role.section)).off("DOMSubtreeModified.toast_dialog");
        background.remove();
        section.remove();
        callback && callback();
        mutationObserver.disconnect();
      });
    },
    resize: function (fadeIn = false) {
      var section = $(getRole(role.section));
      var margin = parseInt(section.css("margin"), 10) * 2;

      if (minWidth > $(window).width()) {
        section.css("min-width", $(window).width() - margin - 10 + "px");
      } else {
        section.css("min-width", minWidth + "px");
      }

      let position = "fixed",
        left = ($(window).width() - section.outerWidth(true)) / 2,
        top =
          settings.isCenter === true
            ? ($(window).height() - section.outerHeight(true)) / 2
            : fadeDialog;

      $("#" + settings.id)
        .find("." + settings.class.content)
        .css("max-height", $(window).height() - 100);

      if ($(window).width() <= section.outerWidth(true)) {
        left = 0;
      }

      if ($(window).height() <= section.outerHeight(true)) {
        top = 0;
      }

      if (fadeIn === true) {
        section.css({
          position: position,
          top: top + fadeDialog + "px",
          left: left + "px",
        });
        section.animate({
          top: top + "px",
          opacity: 1,
        });
      } else {
        section.css({
          position: position,
          left: left + "px",
        });
        section.animate(
          {
            top: top + "px",
            opacity: 1,
          },
          {
            queue: false,
          }
        );
      }
    },
  };

  var Toast = {
    show: function (msg, options) {
      var type = ["default", "success", "warning", "error", "info"];

      options = $.extend(
        true,
        {
          type: type[0],
          icon: null,
          close: null,
          class: null,
          style: {},
          timeout: null,
        },
        options
      );

      if ($.inArray(options.type, type) < 0) {
        options.type = type[0];
      }

      var class_type = settings.class_toast.toast_default;

      if (options.type === type[1]) {
        class_type = settings.class_toast.toast_success;
      } else if (options.type === type[2]) {
        class_type = settings.class_toast.toast_warning;
      } else if (options.type === type[3]) {
        class_type = settings.class_toast.toast_error;
      } else if (options.type === type[4]) {
        class_type = settings.class_toast.toast_info;
      }

      var timeout =
        options.timeout !== null && options.timeout > 0
          ? options.timeout
          : false;
      var style = "";

      if (typeof options.style === "object") {
        for (const css in options.style) {
          style += css + ": " + options.style[css] + ";";
        }
      }

      var content =
        '<div class="' +
        class_type +
        " " +
        (options.class ? options.class : "") +
        '" ' +
        setRole(role.toast_section) +
        ' style="' +
        style +
        '">';

      if (options.icon !== null) {
        content +=
          '<div class="' +
          settings.class_toast.toast_icon +
          '">' +
          options.icon +
          "</div>";
      }

      content +=
        '<div class="' +
        settings.class_toast.toast_message +
        '">' +
        msg +
        '</div><div class="' +
        settings.class_toast.toast_close +
        '" ' +
        setRole(role.toast_close) +
        "> " +
        (options.close ? options.close : '<i class="fas fa-times"></i>') +
        "</div></div>";

      if ($(getRole(role.toast_wrapper)).length <= 0) {
        $("body").append(
          '<div class="' +
            settings.class_toast.toast_wrapper +
            '" ' +
            setRole(role.toast_wrapper) +
            "></div>"
        );
      }
      var toast = $(content);
      if (timeout) {
        toast.append(
          '<div class="' +
            settings.class_toast.toast_timeout +
            '" style="-webkit-animation-duration: ' +
            timeout +
            "ms;animation-duration: " +
            timeout +
            'ms;"></div>'
        );
      }

      toast.addClass(settings.class_toast.toast_effect_enter);
      $(getRole(role.toast_wrapper)).prepend(toast);

      if (timeout) {
        setTimeout(function () {
          Toast.hide(toast);
        }, options.timeout);
      }
    },
    hide: function (toast) {
      if (!toast) {
        return;
      }

      toast.removeClass(settings.class_toast.toast_effect_enter);
      toast.addClass(settings.class_toast.toast_effect_leave);

      toast.animate(
        {
          opacity: 0,
        },
        fadeToast,
        function () {
          toast.remove();
          if (
            $(getRole(role.toast_wrapper)).find(getRole(role.toast_section))
              .length <= 0
          ) {
            $(getRole(role.toast_wrapper)).remove();
          }
        }
      );
    },
    clear: function () {
      let toasts = $(getRole(role.toast_wrapper)).find(
        getRole(role.toast_section)
      );
      if (toasts.length > 0) {
        toasts.each(function (i) {
          Toast.hide($(this));
        });
      }
    },
  };

  $.extend({
    dialogClose: function () {
      $(getRole(role.close)).trigger("click");
    },

    dialogShow: function (options) {
      Dialog.show(options);
    },

    dialogResize: function () {
      Dialog.resize();
    },

    toastClose: function (toast) {
      toast = typeof toast !== "string" ? $(toast) : toast;
      Toast.hide(toast);
    },

    toastClear: function () {
      Toast.clear();
    },

    toastShow: function (msg, options) {
      Toast.show(msg, options);
    },
  });

  (function () {
    $(document).on("click", getRole(role.toast_close), function () {
      var toast = $(this).parents(getRole(role.toast_section));
      Toast.hide(toast);
    });

    $(document)
      .on("click", getRole(role.close), function () {
        var _self = $(this),
          onBeforeClose;

        typeof settings.onBeforeClose === "function" &&
          (onBeforeClose = settings.onBeforeClose(_self));
        if (onBeforeClose === false) {
          return;
        }

        Dialog.hide(function () {
          if (typeof settings.onClose === "function") {
            settings.onClose(_self, onBeforeClose);
          }
        });
      })
      .on("click", getRole(role.confirm), async function () {
        var _self = $(this),
          onBeforeConfirm;

        if (settings.noconfirm === true) {
          return;
        }

        typeof settings.onBeforeConfirm === "function" &&
          (onBeforeConfirm = await settings.onBeforeConfirm(_self));
        if (onBeforeConfirm === false) {
          return;
        }

        Dialog.hide(function () {
          if (typeof settings.onConfirm === "function") {
            settings.onConfirm(_self, onBeforeConfirm);
          }
        });
      })
      .on("click", getRole(role.cancel), function () {
        var _self = $(this),
          onBeforeCancel;

        typeof settings.onBeforeCancel === "function" &&
          (onBeforeCancel = settings.onBeforeCancel(_self));
        if (onBeforeCancel === false) {
          return;
        }

        Dialog.hide(function () {
          if (typeof settings.onCancel === "function") {
            settings.onCancel(_self, onBeforeCancel);
          }
        });
      })
      .on("click", getRole(role.background), function () {
        if (settings.bgHide !== false) {
          $(getRole(role.close)).trigger("click");
        }
      })
      .on("keyup", function (e) {
        if (e.keyCode === 27) {
          $(getRole(role.close)).trigger("click");
        }
      });
  })();
})(jQuery);
