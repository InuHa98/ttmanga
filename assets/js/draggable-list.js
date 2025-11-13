(function (factory) {
  "use strict";

  try {
    if (typeof define === "function" && define.amd) {
      define(["jquery"], factory);
    } else if (typeof exports == "object" && typeof module == "object") {
      module.exports = factory(require("jquery"));
    } else {
      factory(jQuery);
    }
  } catch (error) {
    factory();
  }
})(function ($) {
  "use strict";

  const defaultOptions = {
    grid: false,
    handle: false,
    auto_index: false,
    index_start: 1,
    class: {
      index: "DraggableListJS-index",
      handle: "DraggableListJS-handle",
      draggable: "DraggableListJS-draggable",
      dragging: "DraggableListJS-dragging",
      placaholder: "DraggableListJS-placeholder",
    },
    exclude_element: ["INPUT", "SELECT", "TEXTAREA", "IMG"],
    onInit: function (element_items, container_draggable, options) {},
    onDragStart: function (event, container_draggable, placaholder, options) {},
    onDragMove: function (event, container_draggable, placaholder, options) {},
    onDragEnd: function (event, container_draggable, placaholder, options) {},
  };

  let container_draggable,
    target_dragging,
    placeholder,
    isDragging = false,
    current_width,
    current_height,
    current_x,
    current_y;

  const moveNode = function (itemToMove, itemTarget) {
    itemTarget =
      itemToMove.nextSibling === itemTarget
        ? itemTarget.nextSibling
        : itemTarget;
    container_draggable.insertBefore(itemToMove, itemTarget);
  };

  const isAbove = function (nodeA, nodeB) {
    const rectA = nodeA.getBoundingClientRect();
    const rectB = nodeB.getBoundingClientRect();
    return rectA.top + rectA.height / 2 < rectB.top + rectB.height / 2;
  };

  const init = function (element_list, opt) {
    if (typeof element_list == "string") {
      element_list = document.querySelectorAll(element_list);
      if (element_list.length > 0) {
        [].slice.call(element_list).forEach((list) => init(list, opt));
        return;
      }
    }

    if (element_list.length < 1) {
      return;
    }

    element_list.DraggableListOptions = Object.assign(
      {},
      element_list.DraggableListOptions || defaultOptions,
      opt || {}
    );
    const options = element_list.DraggableListOptions;
    const element_items = element_list.children;

    if (element_items.length < 1) {
      return;
    }

    const auto_index = function () {
      if (!options.auto_index) {
        return;
      }
      let i = parseInt(options.index_start) || 0;
      [].slice.call(element_items).forEach((element_item) => {
        const index = element_item.querySelector("." + options.class.index);
        index && (index.innerHTML = i);
        i++;
      });
    };

    const eventDownHandler = function (e) {
      target_dragging = options.handle
        ? e.currentTarget.closest("." + options.class.draggable)
        : e.currentTarget;

      if (
        !target_dragging ||
        options.exclude_element.includes(e.target.tagName)
      ) {
        return;
      }

      container_draggable = target_dragging.parentNode;

      const draggingRect = target_dragging.getBoundingClientRect();
      current_x =
        (e.pageX || (e.touches && e.touches[0].pageX)) - draggingRect.left;
      current_y =
        (e.pageY || (e.touches && e.touches[0].pageY)) - draggingRect.top;
      current_width = draggingRect.width;
      current_height = draggingRect.height;

      placeholder = document.createElement("div");
      placeholder.classList.add(options.class.placaholder);
      placeholder.style.width = draggingRect.width + "px";
      placeholder.style.height = draggingRect.height + "px";

      typeof options.onDragStart === "function" &&
        options.onDragStart.call(
          this,
          e,
          container_draggable,
          placeholder,
          options
        );

      document.addEventListener("mousemove", eventMoveHandler, {
        passive: false,
      });
      document.addEventListener("mouseup", eventUpHandler, { passive: false });
      document.addEventListener("touchmove", eventMoveHandler, {
        passive: false,
      });
      document.addEventListener("touchend", eventUpHandler, { passive: false });
    };

    const eventMoveHandler = function (e) {
      console.log('aaaas')
      e.preventDefault();
      target_dragging.style.top =
        (e.pageY || (e.touches && e.touches[0].pageY)) - current_y + "px";
      target_dragging.style.left =
        (e.pageX || (e.touches && e.touches[0].pageX)) - current_x + "px";

      if (!isDragging) {
        isDragging = true;
        target_dragging.style.width = current_width + "px";
        target_dragging.style.height = current_height + "px";
        target_dragging.classList.add(options.class.dragging);
        container_draggable.insertBefore(
          placeholder,
          target_dragging.nextSibling
        );
      }

      let prevEle = target_dragging.previousElementSibling,
        nextEle = placeholder.nextElementSibling;

      let elementMove;

      if (!options.grid) {
        if (prevEle && isAbove(target_dragging, prevEle)) {
          elementMove = prevEle;
        }

        if (nextEle && isAbove(nextEle, target_dragging)) {
          elementMove = nextEle;
        }
      } else {
        const siblingEles = [].slice
          .call(container_draggable.children)
          .filter((item) => item != placeholder);

        const draggingRect = target_dragging.getBoundingClientRect(),
          cursorX = draggingRect.left + draggingRect.width / 2,
          cursorY = draggingRect.top + draggingRect.height / 2;

        elementMove = siblingEles.find(
          (item) =>
            item != target_dragging &&
            item.offsetLeft <= cursorX &&
            item.offsetLeft + item.offsetWidth >= cursorX &&
            item.offsetTop <= cursorY &&
            item.offsetTop + item.offsetHeight >= cursorY
        );
      }

      if (elementMove) {
        moveNode(placeholder, elementMove);
        moveNode(target_dragging, placeholder);
      }

      typeof options.onDragMove === "function" &&
        options.onDragMove.call(
          target_dragging,
          e,
          container_draggable,
          placeholder,
          options
        );
    };

    const eventUpHandler = function (e) {
      if (placeholder && placeholder.parentNode) {
        placeholder.parentNode.removeChild(placeholder);
      }

      target_dragging.classList.remove(options.class.dragging);
      target_dragging.style.removeProperty("top");
      target_dragging.style.removeProperty("left");
      target_dragging.style.removeProperty("width");
      target_dragging.style.removeProperty("height");

      auto_index();
      typeof options.onDragEnd === "function" &&
        options.onDragEnd.call(
          target_dragging,
          e,
          container_draggable,
          placeholder,
          options
        );

      current_x = null;
      current_y = null;
      current_width = null;
      current_height = null;
      target_dragging = null;
      container_draggable = null;
      placeholder = null;
      isDragging = false;

      document.removeEventListener("mousemove", eventMoveHandler);
      document.removeEventListener("mouseup", eventUpHandler);
      document.removeEventListener("touchmove", eventMoveHandler);
      document.removeEventListener("touchend", eventUpHandler);
    };

    typeof options.onInit === "function" &&
      options.onInit(element_items, element_list, options);

    [].slice.call(element_items).forEach((element_item) => {
      if (!element_item.classList.contains(options.class.draggable)) {
        const handle = element_item.querySelector("." + options.class.handle);

        element_item.classList.add(options.class.draggable);

        if (options.handle && handle) {
          element_item.style.removeProperty("cursor");
          element_item.removeEventListener("mousedown", eventDownHandler);
          element_item.removeEventListener("touchstart", eventDownHandler);
          handle.addEventListener("mousedown", eventDownHandler);
          handle.addEventListener("touchstart", eventDownHandler);
        } else {
          element_item.style.cursor = "move";
          element_item.addEventListener("mousedown", eventDownHandler);
          element_item.addEventListener("touchstart", eventDownHandler);
        }
      }
    });

    auto_index();
  };

  Element.prototype.DraggableListJS = function (options) {
    init(this, options);
  };

  NodeList.prototype.DraggableListJS = function (options) {
    this.forEach((node) => init(node, options));
  };

  window.DraggableListJS = function (element_list, options) {
    init(element_list, options);
  };

  if (typeof $ != "undefined") {
    $.fn.DraggableListJS = function (options) {
      return this.each(function () {
        init(this, options);
      });
    };

    $.DraggableListJS = function (element_list, options) {
      init(element_list, options);
    };
  }
});
