(function () {
  var cropper, canvas, config;
  const upload_image = $("#input-upload-image");
  const role_change_avatar = $("[role=btn-change-avatar]");
  const role_change_cover = $("[role=btn-change-cover]");
  const btn_save = $("[role=btn-save-upload]");
  const element_preview_avatar = "#preview_avatar";
  const element_preview_cover = "#preview_cover";
  const input_data_upload = "#data-upload";
  const class_save = ".save-upload";

  $(document).ready(function () {
    btn_save.on("click", function () {
      $(this).addClass("disabled");
    });

    role_change_avatar.on("click", function () {
      config = {
        type: "avatar",
        title: "Upload Avatar",
        aspectRatio: 1 / 1,
        preview: element_preview_avatar,
        data: input_data_upload,
        width: 336,
        height: 336,
      };

      upload_image.click();
    });

    role_change_cover.on("click", function () {
      config = {
        type: "cover",
        title: "Upload Cover",
        aspectRatio: 16 / 10,
        preview: element_preview_cover,
        data: input_data_upload,
        width: 1920,
        height: 1200,
      };
      upload_image.click();
    });

    $(document).on("click", "[role=cropper-rotate-]", function () {
      cropper.rotate(-45);
    });

    $(document).on("click", "[role=cropper-rotate]", function () {
      cropper.rotate(45);
    });

    $(document).on("click", "[role=cropper-scaleX]", function () {
      let data = $(this).data("scale");
      cropper.scaleX(data);
      $(this).data("scale", data > 0 ? -1 : 1);
    });

    $(document).on("click", "[role=cropper-scaleY]", function () {
      let data = $(this).data("scale");
      cropper.scaleY(data);
      $(this).data("scale", data > 0 ? -1 : 1);
    });

    $(document).on("click", "[role=cropper-zoomOut]", function () {
      cropper.zoom(-0.1);
    });

    $(document).on("click", "[role=cropper-zoomIn]", function () {
      cropper.zoom(0.1);
    });

    $(document).on("click", "[role=cropper-reset]", function () {
      cropper.reset();
    });

    upload_image.change(function (event) {
      var files = event.target.files;

      if (files && files.length > 0) {
        var extension = ["image/jpg", "image/jpeg", "image/png", "image/gif"];
        var mime_type = files[0].type;

        if (extension.indexOf(mime_type) < 0) {
          $.toastShow("<strong>Error</strong>: Invalid image format.", {
            type: "error",
            timeout: 3000,
          });
          return false;
        }

        reader = new FileReader();
        reader.onload = function (event) {
          $.dialogShow({
            title: config.title,
            content:
              '<img class="cropper-responsive" id="crop_image" src="' +
              reader.result +
              '">',
            desc: '\
                        <div class="cropper-tools">\
                            <div class="btn-group">\
                                <span role="cropper-rotate-" class="btn btn-gray"><i class="fa fa-undo-alt"></i></span>\
                                <span role="cropper-rotate" class="btn btn-gray"><i class="fa fa-redo-alt"></i></span>\
                            </div>\
                            <div class="btn-group">\
                                <span role="cropper-scaleX" data-scale="-1" class="btn btn-gray"><i class="fa fa-arrows-alt-h"></i></span>\
                                <span role="cropper-scaleY" data-scale="-1" class="btn btn-gray"><i class="fa fa-arrows-alt-v"></i></span>\
                            </div>\
                            <div class="btn-group">\
                                <span role="cropper-zoomOut" class="btn btn-gray"><i class="fas fa-search-minus"></i></span>\
                                <span role="cropper-zoomIn" class="btn btn-gray"><i class="fas fa-search-plus"></i></span>\
                                <span role="cropper-reset" class="btn btn-gray"><i class="fas fa-sync-alt"></i></span>\
                            </div>\
                        </div>',
            button: {
              confirm: "Tiếp tục",
              cancel: "Huỷ",
            },
            bgHide: false,
            isCenter: true,
            onInit: function () {
              canvas = document.getElementById("crop_image");
              cropper = new Cropper(canvas, {
                aspectRatio: config.aspectRatio,
                viewMode: 2,
                autoCropArea: 1,
                dragMode: "move",
              });
              //$('#data-name').val(Date.now());
            },
            onConfirm: function () {
              cropper
                .getCroppedCanvas({
                  width: config.width,
                  height: config.height,
                })
                .toBlob(function (blob) {
                  url = URL.createObjectURL(blob);
                  var reader = new FileReader();
                  reader.readAsDataURL(blob);
                  reader.onloadend = function () {
                    let previewImage = $(config.preview);

                    if (config.type === "avatar") {
                      if (previewImage.prop("tagName") !== "IMG") {
                        previewImage = previewImage.find("img");
                      }
                      previewImage.attr("src", reader.result);
                      previewImage.show();
                    } else if (config.type === "cover") {
                      previewImage.css(
                        "background-image",
                        "url(" + reader.result + ")"
                      );
                    }

                    btn_save.val("Lưu lại");
                    btn_save.attr("name", "save-" + config.type);
                    $(config.data).val(reader.result);
                    btn_save.show();
                    $(class_save).show();

                    upload_image.val("");
                    cropper.destroy();
                    cropper = null;
                  };
                });
            },
            onCancel: function () {
              upload_image.val("");
              cropper.destroy();
              cropper = null;
            },
            onClose: function () {
              upload_image.val("");
              cropper.destroy();
              cropper = null;
            },
          });
        };
        reader.readAsDataURL(files[0]);
      }
    });
  });
})();
