if (!String.prototype.escapeHtml) {
  String.prototype.htmlEntities = function () {
    return this.replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  };
}

String.prototype.htmlToBbcode = function () {
  var _self = this;
  var rep = function (re, str) {
    _self = _self.replace(re, str);
  };
  rep(/<a.*?href=\"(.*?)\".*?>(.*?)<\/a>/gi, "[url=$1]$2[/url]");
  rep(/<font.*?color=\"(.*?)\".*?>(.*?)<\/font>/gi, "[color=$1]$2[/color]");
  rep(/<font>(.*?)<\/font>/gi, "$1");
  rep(
    /<img.*?class=\"comment-smiley-icon\".*?src=\"(.*?)\".*?\/?>/gi,
    "[smiley]$1[/smiley]"
  );
  rep(/<img.*?src=\"(.*?)\".*?\/?>/gi, "[img]$1[/img]");
  rep(/<\/(strong|b)>/gi, "[/b]");
  rep(/<(strong|b)>/gi, "[b]");
  rep(/<\/(em|i)>/gi, "[/i]");
  rep(/<(em|i)>/gi, "[i]");
  rep(/<\/u>/gi, "[/u]");
  rep(/<u>/gi, "[u]");

  rep(/<br \/>/gi, "\n");
  rep(/<br\/>/gi, "\n");
  rep(/<br>/gi, "\n");
  return _self;
};

String.prototype.bbcodeToHtml = function () {
  var _self = this;
  var rep = function (re, str) {
    _self = _self.replace(re, str);
  };

  rep(/\n/gi, "<br />");
  rep(/\[b\]/gi, "<strong>");
  rep(/\[\/b\]/gi, "</strong>");
  rep(/\[i\]/gi, "<em>");
  rep(/\[\/i\]/gi, "</em>");
  rep(/\[u\]/gi, "<u>");
  rep(/\[\/u\]/gi, "</u>");
  rep(/\[url=([^\]]+)\](.*?)\[\/url\]/gi, '<a href="$1">$2</a>');
  rep(/\[url\](.*?)\[\/url\]/gi, '<a href="$1">$1</a>');
  rep(/\[img\](.*?)\[\/img\]/gi, '<img src="$1" />');
  rep(/\[color=(.*?)\](.*?)\[\/color\]/gi, '<font color="$1">$2</font>');

  return _self;
};

function htmlEntities(str, bbcode = null) {
  var str = String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
  if (bbcode === true) {
    str = str.bbcodeToHtml();
  }

  if (bbcode === false) {
    str = str.htmlToBbcode();
  }
  return str;
}

(function () {
  $(document).ready(function () {
    const tables = $("table.table-sort");

    if (!tables.length) {
      return false;
    }

    jQuery.fn.sortElements = (function () {
      var sort = [].sort;

      return function (comparator, getSortable) {
        getSortable =
          getSortable ||
          function () {
            return this;
          };

        var placements = this.map(function () {
          var sortElement = getSortable.call(this),
            parentNode = sortElement.parentNode,
            nextSibling = parentNode.insertBefore(
              document.createTextNode(""),
              sortElement.nextSibling
            );

          return function () {
            if (parentNode === this) {
              throw new Error(
                "You can't sort elements if any one is a descendant of another."
              );
            }

            parentNode.insertBefore(this, nextSibling);
            parentNode.removeChild(nextSibling);
          };
        });

        return sort.call(this, comparator).each(function (i) {
          placements[i].call(getSortable.call(this));
        });
      };
    })();

    tables.each(function () {
      const table = $(this);
      [].slice.call(table.find("th.sort-btn")).forEach(function (th_sort) {
        let th = $(th_sort),
          thIndex = th.index(),
          inverse = false;

        th.on("click", function () {
          var current_index = $(this).index();
          table.find("th.sort-btn").each(function () {
            if ($(this).index() != current_index) {
              $(this).removeClass("sort-asc").removeClass("sort-desc");
            }
          });

          if (th.hasClass("sort-desc")) {
            th.removeClass("sort-desc").addClass("sort-asc");
          } else {
            th.removeClass("sort-asc").addClass("sort-desc");
          }

          const tbody = table.find("tbody");
          if (!tbody.length) {
            return;
          }

          tbody
            .find("td")
            .filter(function () {
              return $(this).index() === thIndex;
            })
            .sortElements(
              function (a, b) {
                a = a.textContent.trim();
                b = b.textContent.trim();

                if (inverse) {
                  let c = null;
                  c = a;
                  a = b;
                  b = c;
                }
                return a
                  .replace(".", "")
                  .localeCompare(b.replace(".", ""), undefined, {
                    numeric: true,
                    sensitivity: "base",
                  });
              },
              function () {
                return this.parentNode;
              }
            );

          inverse = !inverse;
        });
      });
    });
  });
})();

function multiple_selected(options) {
  const noop = function () {};

  var config = $.extend(
    {},
    {
      role_select_all: "multiple_selected_all",
      role_select: "[role=multiple_selected]",
      onSelected: noop,
      onNoSelected: noop,
    },
    options || {}
  );

  $(config.role_select_all).on("click", function () {
    $(config.role_select)
      .parents(".form-check")
      .not(".disabled")
      .find("input")
      .prop("checked", this.checked)
      .change();
  });

  $(document).on("change", config.role_select, function () {
    let total_selected = $(config.role_select + ":checked").length;
    let total_role_select = $(config.role_select).length;

    $(config.role_select_all).prop(
      "checked",
      total_selected >= total_role_select ? true : false
    );

    if (total_selected > 0) {
      config.onSelected(total_selected, config);
    } else {
      config.onNoSelected(total_selected, config);
    }
  });
}

(function () {
  let difference_rate = 10;
  let $parent = ".drop-menu";
  let content = ".drop-menu__content";
  let class_hide = "drop-menu__bg-hide";

  $(document).on("click", $parent, function (e) {
    e.stopPropagation();
    //e.preventDefault();

    let ele_menu = $(this).children(content);
    let preference = $(this).data("preference");

    if (ele_menu.length < 1) {
      return;
    }

    let bgHide = $("<div class='" + class_hide + "'></div>");
    if ($(this).hasClass("show")) {
      $(this).removeClass("show");
      bgHide.remove();
    } else {
      let offset = this.getBoundingClientRect();
      let padding = difference_rate * 2;

      let top = offset.top - ele_menu.outerHeight() / 2, // mid y
        left = offset.left - ele_menu.outerWidth() / 2, // mid x
        __x_origin = "left",
        __y_origin = "top";

      if ($(window).height() - offset.top > ele_menu.outerHeight()) {
        // bottom
        top = offset.top + difference_rate;
      } else if (offset.top > ele_menu.outerHeight()) {
        // top
        __y_origin = "bottom";
        top = offset.top - ele_menu.outerHeight() + difference_rate * 2;
      }

      if (top + ele_menu.outerHeight() >= $(window).height()) {
        if (offset.top > $(window).height() / 2) {
          __y_origin = "bottom";
        } else {
          __y_origin = "top";
        }
        top = $(window).height() - ele_menu.outerHeight() - difference_rate / 2;
      }

      if (top <= 0) {
        top = difference_rate / 2;
      }

      if (
        preference == "right" ||
        offset.left > $(window).width() / 2 + ele_menu.outerWidth()
      ) {
        if (offset.left > ele_menu.outerWidth() + padding) {
          // left
          __x_origin = "right";
          left = offset.left - ele_menu.outerWidth() + $(this).width(); //+ (difference_rate * 2)
        } else if (
          $(window).width() - offset.left >
          ele_menu.outerWidth() + padding
        ) {
          // right
          left = offset.left;
        }
      } else {
        if ($(window).width() - offset.left > ele_menu.outerWidth() + padding) {
          // right
          left = offset.left;
        } else if (offset.left > ele_menu.outerWidth() + padding) {
          // left
          __x_origin = "right";
          left = offset.left - ele_menu.outerWidth() + $(this).width(); //+ (difference_rate * 2)
        }
      }

      if (left + ele_menu.outerWidth() >= $(window).width()) {
        if (offset.left > $(window).width() / 2) {
          __x_origin = "right";
        } else {
          __x_origin = "left";
        }
        left = $(window).width() - ele_menu.outerWidth() - difference_rate / 2;
      }

      if (left <= 0) {
        left = difference_rate / 2;
      }

      ele_menu.css({
        "transform-origin": __x_origin + " " + __y_origin,
        top: top + "px",
        left: left + "px",
      });

      $(document).find($parent).removeClass("show");
      $(this).addClass("show");
      $(this).prepend(bgHide);
    }

    bgHide.off("click.dropdownMenu");
    bgHide.on("click.dropdownMenu", function (e) {
      $($parent + ".show").removeClass("show");
      bgHide.remove();
    });
    ele_menu.find("li").on("click.dropdownMenu", function (e) {
      bgHide.remove();
    });
    $(window).off("resize.dropdownMenu");
    $(window).on("resize.dropdownMenu", function () {
      $($parent + ".show").removeClass("show");
      bgHide.remove();
    });
  });
})();

function textarea_editor(options = {}) {
  var idEditor = options.id || null;
  var events = options.events || {};

  if (!idEditor) {
    return;
  }

  var settings = {
    selector: idEditor,
    smart_paste: false,
    entity_encoding: "raw",
    forced_root_block: "",
    paste_as_text: true,
    menubar: false,

    meme: {
      size_image: 50,
      sources: options.meme_sources || [],
    },

    plugins: options.plugins || ["bbcode paste image meme"],
    toolbar: "bold underline italic image meme",
    paste_auto_cleanup_on_paste: true,
    branding: false,
    object_resizing: false,
    skin: options.theme || null,
    elementpath: false,
    setup: function (editor) {
      editor.on("init", function () {
        var textarea = document.querySelector("#" + this.id);
        var current_value = textarea.value;

        if (current_value.indexOf('br data-mce-bogus="1"') >= 0) {
          current_value = "";
        }

        this.setContent(
          current_value
            .replace(/&/g, "&amp;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;"),
          { format: "bbcode" }
        );

        textarea.value = this.getContent();
      });
    },
  };

  if (Object.keys(events).length > 0) {
    settings.init_instance_callback = function (editor) {
      for (var event in events) {
        editor.on(event, events[event]);
      }
    };
  }

  tinymce.init(settings);
}
