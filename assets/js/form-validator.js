function Validator(options) {
  if (!options) {
    return;
  }
  var form = options.form || null;
  var class_error = options.class_error || "validate-invalid";
  var class_message = options.class_message || "validate-error-message";
  var selector = options.selector || null;
  var selectorRules = options.rules || {};

  if (!form) {
    return;
  }

  var forms =
    typeof form == "object"
      ? [].slice.call(form)
      : document.querySelectorAll(form);

  forms.forEach(function (formElement) {
    function getParent(element) {
      if (!selector) {
        return element.parentElement;
      }

      while (element.parentElement) {
        if (element.parentElement.matches(selector)) {
          return element.parentElement;
        }
        element = element.parentElement;
      }
    }

    function add_error(parent, elementError) {
      remove_error(parent);
      parent.appendChild(elementError);
      parent.classList.add(class_error);
    }

    function remove_error(parent) {
      var errorElements = parent.querySelectorAll("." + class_message);

      parent.classList.remove(class_error);
      Array.from(errorElements).forEach(function (element) {
        element.remove();
      });
    }

    function check_rule(element, rules) {
      var parent = getParent(element);

      var errorElement = document.createElement("div");
      errorElement.classList.add(class_message);
      var errorMessage, value;

      switch (element.type) {
        case "radio":
        case "checkbox":
          value = formElement.querySelector(
            '[name="' + element.name + '"]:checked'
          );
          break;
        case "hidden":
          return true;
        default:
          value = element.value;
      }

      if (!Array.isArray(rules)) {
        rules = [rules];
      }

      for (var rule of rules) {
        errorMessage = rule(value);
        if (errorMessage) {
          errorElement.innerText = errorMessage;
          break;
        }
      }

      if (errorMessage) {
        add_error(parent, errorElement);
      } else {
        remove_error(parent);
      }

      return !errorMessage;
    }

    function validate(callback) {
      for (var selectorElement in selectorRules) {
        var inputElements = formElement.querySelectorAll(selectorElement);
        if (!inputElements.length) {
          return;
        }

        Array.from(inputElements).forEach(function (inputElement) {
          (function (inputElement, selectorElement) {
            if (typeof callback == "function") {
              callback(inputElement, selectorRules[selectorElement]);
            } else {
              inputElement.addEventListener("change", function () {
                check_rule(this, selectorRules[selectorElement]);
              });

              inputElement.addEventListener("blur", function () {
                check_rule(this, selectorRules[selectorElement]);
              });

              inputElement.addEventListener("input", function () {
                remove_error(getParent(this));
              });
            }
          })(inputElement, selectorElement);
        });
      }
    }

    function get_data_form() {
      return Array.from(formElement.querySelectorAll("[name]")).reduce(
        function (values, input) {
          switch (input.type) {
            case "radio":
              values[input.name] = formElement.querySelector(
                'input[name="' + input.name + '"]:checked'
              ).value;
              break;
            case "checkbox":
              var checked = formElement.querySelectorAll(
                'input[name="' + input.name + '"]:checked'
              );
              if (checked.length < 1) {
                values[input.name] = "";
                return values;
              }
              if (!Array.isArray(values[input.name])) {
                values[input.name] = [];
              }
              if (input.matches(":checked")) {
                values[input.name].push(input.value);
              }

              break;
            case "select-multiple":
              values[input.name] = Array.from(
                formElement.querySelectorAll(
                  'select[name="' + input.name + '"] option:checked'
                )
              ).map(function (el) {
                return el.value;
              });
              break;
            case "file":
              values[input.name] = input.files;
              break;
            default:
              values[input.name] = input.value;
          }

          return values;
        },
        {}
      );
    }

    if (formElement) {
      formElement.onsubmit = function (e) {
        e.preventDefault();
        var isValidated = true;
        validate(function (element, rules) {
          if (check_rule(element, rules) !== true) {
            isValidated = false;
          }
        });

        if (isValidated) {
          if (typeof options.onSubmit === "function") {
            options.onSubmit(get_data_form());
          } else {
            formElement.submit();
          }
        }
      };

      validate();
    }

    ((form) => {
      const observer = new MutationObserver((mutationsList, observer) => {
        for (const mutation of mutationsList) {
          for (const node of mutation.addedNodes) {
            if (!(node instanceof Element)) {
              return;
            }

            if (node.tagName === "INPUT" || node.tagName === "SELECT") {
              validate();
            } else {
              const inputsAndSelects = node.querySelectorAll("input, select");
              if (inputsAndSelects.length > 0) {
                validate();
              }
            }
          }
        }
      });

      observer.observe(form, {
        childList: true,
        subtree: true,
        attributes: false,
        characterData: false,
      });
    })(formElement);
  });
}

Validator.isRequired = function (message) {
  return function (value) {
    return value ? undefined : message || "Please enter this field";
  };
};

Validator.isNumber = function (message) {
  return function (value) {
    var regex = /^(\d+)$/;
    return !value || regex.test(value)
      ? undefined
      : message || "This field must be a number";
  };
};

Validator.isUsername = function (message) {
  return function (value) {
    var regex = /^(?![_.])(?!.*[_.]{2})[a-zA-Z0-9._]+(?<![_.])$/;
    return !value || regex.test(value)
      ? undefined
      : message || "Username contains invalid characters";
  };
};

Validator.isEmail = function (message) {
  return function (value) {
    var regex =
      /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return !value || regex.test(value)
      ? undefined
      : message || "Invalid email format";
  };
};

Validator.minLength = function (min, message) {
  return function (value) {
    return value.length >= min
      ? undefined
      : message || "Please enter at least " + min + " characters";
  };
};

Validator.maxLength = function (max, message) {
  return function (value) {
    return value.length <= max
      ? undefined
      : message || "Please enter up to " + max + " characters";
  };
};

Validator.isConfirmed = function (confirmValue, message) {
  return function (value) {
    return value ===
      (typeof confirmValue === "object" ? confirmValue.value : confirmValue)
      ? undefined
      : message || "Incorrect value validation";
  };
};

Validator.isPhone = function (message) {
  return function (value) {
    var phoneRegex = /^[+]?[0-9]{1,3}?[0-9]{1,9}$/;
    return phoneRegex.test(value)
      ? undefined
      : message || "Invalid phone number";
  };
};
