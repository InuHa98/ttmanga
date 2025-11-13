(function ($) {
  const _sectionHeader = $("#section-header");
  const class_show_header = "show__header";
  const class_hide_header = "hide__header";

  const _sectionSubHeader = $("#section-sub-header");

  const _sectionMainSideNav = $(".side-nav-main");
  const _btnSideNav = $("#btn_sidenav-menu");
  const _btnSideNavGroup = $(".side-nav-menu__items-group__title");
  const class_show_sidenav = "show-side-nav";
  const class_show_notifi = "show-side-notifi";
  const class_show_sidenav_group = "show__group";

  const _btnShowSearch = $("#btn_mini-search");
  const _resultSearch = $("#result-search");
  const class_show_search = "show__search";

  const _btnShowNotification = $("#btn_notification");
  const class_show_notification = "show__notification";

  const _backToTop = $("#back-to-top");

  const _tabmenuHorizontal = $(".tabmenu-horizontal");
  const class_tabmenu_active = "active";

  (function () {
    _btnShowSearch.on("click", function () {
      _sectionHeader.removeClass(class_show_notification);
      _sectionHeader.toggleClass(class_show_search);
      _resultSearch.toggleClass("active");
    });

    _btnShowNotification.on("click", function () {
      _sectionHeader.removeClass(class_show_search);
      _sectionHeader.toggleClass(class_show_notification);
      $("body").toggleClass(class_show_notifi);
    });

    _btnSideNav.on("click", function (e) {
      e.stopPropagation();
      _sectionHeader.removeClass(class_show_search);
      _sectionHeader.removeClass(class_show_notification);
      $("body").toggleClass(class_show_sidenav);
    });

    _sectionMainSideNav.after().on("click", function () {
      $("body").removeClass(class_show_sidenav);
    });

    _btnSideNavGroup.on("click", function () {
      $(this).parent().toggleClass(class_show_sidenav_group);
    });
  })();

  (function () {
    if (_backToTop.length > 0) {
      _backToTop.on("click", function () {
        $("html, body").animate(
          {
            scrollTop: 0,
          },
          "slow",
          function () {
            _backToTop.css("display", "none");
          }
        );
      });
    }

    let show_header_style =
      _sectionSubHeader.length > 0
        ? _sectionSubHeader[0].offsetTop - _sectionHeader[0].offsetHeight
        : 0;
    if (window.pageYOffset > show_header_style) {
      _sectionHeader.addClass(class_show_header);
    }

    let autoHidenBackToTop = (function () {
      var timer = 0;
      return function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
          if (!_backToTop.is(":hover")) {
            _backToTop.css("display", "none");
          }
        }, 2000);
      };
    })();

    let curr_position_scroll = 0;
    $(window).on("scroll", function () {
      let offset_y = this.pageYOffset,
        diff = curr_position_scroll - offset_y;

      if (_backToTop.length > 0) {
        if (offset_y >= show_header_style) {
          _backToTop.css("display", "flex");
          autoHidenBackToTop();
        } else {
          _backToTop.css("display", "none");
        }
      }

      if (offset_y > show_header_style) {
        _sectionHeader.addClass(class_show_header);
      } else {
        _sectionHeader.removeClass(class_show_header);
      }

      if (
        offset_y <= _sectionHeader.height() * 2 ||
        (diff > 0 &&
          _sectionHeader.hasClass(class_hide_header) &&
          Math.abs(diff) >= 101)
      ) {
        _sectionHeader.removeClass(class_hide_header);
      } else if (
        diff < 0 &&
        !_sectionHeader.hasClass(class_show_notification) &&
        !_sectionHeader.hasClass(class_show_search)
      ) {
        _sectionHeader.addClass(class_hide_header);
      }
      curr_position_scroll = offset_y;
    });
  })();

  (function () {
    var container = _tabmenuHorizontal,
      scrollTo = container.find("." + class_tabmenu_active);
    if (scrollTo.length > 0) {
      container.scrollLeft(
        scrollTo.offset().left -
          container.offset().left +
          container.scrollLeft()
      );
    }
  })();
})(jQuery);

function modeView(cookie_name) {
  const _changeViewMode = $("[role=change-view-mode]");
  const _listView = $(".list-view");
  const class_viewMode_active = "active";
  const class_mode_table = "mode--table";
  const mode_table = "table";

  _changeViewMode.on("click", function () {
    let viewMode = $(this).data("mode");
    if (viewMode) {
      _changeViewMode.removeClass(class_viewMode_active);
      $(this).addClass(class_viewMode_active);
      if (viewMode === mode_table) {
        _listView.removeClass(class_mode_table).addClass(class_mode_table);
      } else {
        _listView.removeClass(class_mode_table);
      }
      var time = new Date();
      time.setFullYear(time.getFullYear() + 1);
      document.cookie =
        cookie_name +
        "=" +
        viewMode +
        "; expires=" +
        time.toGMTString() +
        "; path=/";
    }
  });
}

function tooltip(options = {}) {
  var target = options.target || ".tooltip-target",
    exclude_element = options.exclude_element || [],
    data_target = options.data_target || ".tooltip-data",
    class_tooltip = "tooltip__body";

  var body_tooltip = null;
  var data = {
    image: null,
    title: null,
    desc: null,
  };
  var isHover = false,
    cursorX = 0,
    cursorY = 0;

  var updatePosition = function () {
    var tooltip_width = body_tooltip.outerWidth(true),
      tooltip_height = body_tooltip.outerHeight(true);

    if (cursorX + tooltip_width >= $(window).width()) {
      cursorX = cursorX - (cursorX + tooltip_width - $(window).width());
    }

    if (cursorY + tooltip_height >= $(window).height()) {
      cursorY = cursorY - tooltip_height;
    }

    if (tooltip_width >= $(window).width()) {
      body_tooltip.css({
        width: "auto",
      });
    }

    if (cursorX < 0) {
      cursorX = 0;
    }

    if (cursorY < 0) {
      cursorY = 0;
    }
  };

  var showTooltip = function () {
    body_tooltip = $(
      '\
			<div class="' +
        class_tooltip +
        '">\
				<div class="tooltip-image">\
					<img src="' +
        data.image +
        '" />\
				</div>\
				<div class="tooltip-info">\
					<div class="tooltip-name">' +
        data.title +
        '</div>\
					<div class="tooltip-text">' +
        data.desc +
        "</div>\
				</div>\
			</div>\
		"
    );

    body_tooltip.css({
      top: cursorY + "px",
      left: cursorX + "px",
    });

    $("body").append(body_tooltip);

    body_tooltip.animate(
      {
        opacity: 1,
      },
      {
        queue: false,
      }
    );

    isHover = true;
  };

  var hideTooltip = function () {
    $("body")
      .find("." + class_tooltip)
      .stop()
      .remove();
    body_tooltip = null;
  };

  $(document)
    .on("mouseenter", target, function (e) {
      cursorX = e.clientX;
      cursorY = e.clientY;

      var data_element = $(this).parents(data_target);
      (data.image = data_element.find("[data-tooltip=image]").attr("src")),
        (data.title = data_element.find("[data-tooltip=title]").text()),
        (data.desc = data_element.find("[data-tooltip=desc]").text());

      showTooltip();
    })
    .on("mouseleave", target, function () {
      if (isHover) {
        $(this).stop();
        hideTooltip();
      }
      isHover = false;
      cursorX = null;
      cursorY = null;
    })
    .on("mousemove", target, function (e) {
      if (!isHover) {
        return;
      }

      if (exclude_element.includes(e.target.classList[0])) {
        return body_tooltip.hide();
      }

      body_tooltip.show();

      cursorX = e.clientX;
      cursorY = e.clientY;

      updatePosition();
      body_tooltip.css({
        top: cursorY,
        left: cursorX,
      });
    });
}

function comfirm_dialog(title, text) {
  return new Promise(function (resolve, reject) {
    $.dialogShow({
      title: title,
      content: '<div class="dialog-message">' + text + "</div>",
      button: {
        confirm: "Continue",
        cancel: "Cancel",
      },
      bgHide: false,
      onConfirm: function () {
        resolve(true);
      },
      onCancel: function () {
        resolve(false);
      },
    });
  });
}

function role_event(event, name, callback) {
  $(document).on(event, '[role="' + name + '"]', function (event) {
    callback.bind(this)(event);
  });
}

function role_click(name, callback) {
  $(document).on("click", '[role="' + name + '"]', function (event) {
    callback($(this), event);
  });
}

$(".only-number")
  .on("input", function (event) {
    var value = event.target.value;
    var validValue = value.replace(/\D/g, "");

    if (
      validValue.startsWith("0") &&
      validValue !== "0" &&
      validValue !== "0."
    ) {
      validValue = validValue.substring(1);
    }

    event.target.value = validValue;
  })
  .on("change", function (event) {
    if (event.target.value === "") {
      event.target.value = 0;
      $(this).change();
    }
  });

$('[role="btn-plus"]').on("click", function () {
  const input = $(this).parents(".input-group").find("input");
  input.val(parseInt(input.val()) + 1).change();
});

$('[role="btn-minus"]').on("click", function () {
  const input = $(this).parents(".input-group").find("input");
  const limit_page = parseInt(input.val()) - 1;
  input.val(limit_page > 0 ? limit_page : 0).change();
});

$(".accordion__header").on("click", function () {
  $(this).parent().toggleClass("show");
});
