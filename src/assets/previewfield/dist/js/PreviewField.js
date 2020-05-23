(function ($) {
  Craft.MatrixFieldPreview = Garnish.Base.extend({
    $matrixFields: null,
    matrixFields: {},
    previewsUrl: "matrix-field-preview/preview/get-previews",
    previews: {},
    // FIXME: I'm not sure why, but every time you insert a block
    // into the matrix field, either by the original buttons or by
    // our own modal overlay, our JavaScript code is being
    // reinitialised (not readded, just re-inited). It must be something
    // to do with the MatrixInput.addBlock method from Craft's
    // matrix field JavaScript, but I don't know why it's happening
    init: function (matrixFields) {
      this.defaultImageUrl = matrixFieldPreviewDefaultImage; // via $view->registerJsVar
      this.$matrixFields = $(matrixFields);
      if (this.$matrixFields.length > 0) {
        this.$matrixFields.each(
          function (i, matrixField) {
            var $matrixField = $(matrixField);
            var matrixFieldHandle = this.getMatrixFieldHandle($matrixField);
            if (!this.matrixFields.hasOwnProperty(matrixFieldHandle)) {
              console.info(
                "Initialising matrix-field-preview plugin on field '" +
                  matrixFieldHandle +
                  "'"
              );
              this.setupMatrixField($matrixField, matrixFieldHandle);
            } else {
              console.warn(
                "Matrix field '" +
                  matrixFieldHandle +
                  "' is already initialised"
              );
            }
          }.bind(this)
        );
      }
    },

    setupMatrixField: function ($matrixField, matrixFieldHandle) {
      $.get({
        url: Craft.getActionUrl(this.previewsUrl),
        data: {
          handle: matrixFieldHandle,
        },
        dataType: "json",
        success: function (response) {
          if (response["success"]) {
            console.info(
              "Received response from matrix field config endpoint: ",
              response
            );
            this.previews = response["previews"];

            var matrixInput = $matrixField.data("matrix");
            var $existingBlockTypes = $matrixField.find(".blocks .matrixblock");

            $existingBlockTypes.each(
              function (i, blockType) {
                var $blockType = $(blockType);
                var blockTypeHandle = $blockType.attr("data-type");
                if (this.previews.hasOwnProperty(blockTypeHandle)) {
                  // Insert block type previews for all the static block types
                  this.createBlockTypePreview(
                    $blockType,
                    this.previews[blockTypeHandle]
                  );
                }
              }.bind(this)
            );

            // Insert block type previews when a new block is added
            matrixInput.on(
              "blockAdded",
              function (ev) {
                var $blockType = ev["$block"];
                var blockTypeHandle = $blockType.attr("data-type");
                if (this.previews.hasOwnProperty(blockTypeHandle)) {
                  this.createBlockTypePreview(
                    $blockType,
                    this.previews[blockTypeHandle]
                  );
                }
              }.bind(this)
            );

            // Delete preview from our cache when block type is removed
            matrixInput.on(
              "blockDeleted",
              function (ev) {
                var $blockType = ev["$block"];
                var blockTypeHandle = $blockType.attr("data-type");
                if (this.previews.hasOwnProperty(blockTypeHandle)) {
                  delete this.previews[blockTypeHandle];
                }
              }.bind(this)
            );

            // Insert our custom navigation and modal overlay
            this.createNavigation($matrixField, matrixFieldHandle);

            this.matrixFields[matrixFieldHandle] = $matrixField;

            $matrixField.addClass("preview-loaded");
          } else {
            console.error(error);
            Craft.cp.displayError("Error rendering matrix field preview");
          }
        }.bind(this),
        error: function (error) {
          console.error(error);
          Craft.cp.displayError("Error rendering matrix field preview");
        },
      });
    },

    /**
     * Replace the default block type chooser/navigation with our own
     * custom button and overlay
     */
    createNavigation: function ($matrixField, matrixFieldHandle) {
      var blockTypes = $matrixField.find(".btngroup .btn").map(
        function (i, button) {
          var $button = $(button);
          return {
            handle: $button.attr("data-type"),
            name: $button.text(),
          };
        }.bind(this)
      );

      var modal = this.createModal($matrixField, matrixFieldHandle);
      var $button = this.createModalButton($matrixField);

      var $grid = $("<div>", {
        class: "mfp-modal__grid",
      });

      //for (let [blockTypeHandle, preview] of Object.entries(this.previews)) {
      blockTypes.each(
        function (i, blockType) {
          var $item = $("<div>", {
            class: "mfp-modal__item",
          }).attr("data-block-type", blockType.handle);

          if (this.previews.hasOwnProperty(blockType.handle)) {
            var preview = this.previews[blockType.handle];
            var $img = $("<div>", {
              class: "mfp-modal__image",
            });
            var $name = $("<h2>", {
              class: "mfp-modal__name",
              text: preview["name"],
            });
            var $description = $("<p>", {
              class: "mfp-modal__description",
              text: preview["description"],
            });
            $img.append($("<img>").attr("src", preview["image"]));
            $item.prepend($img, $name, $description);
          } else {
            var $img = $("<div>", {
              class: "mfp-modal__image mfp-modal__image--default",
            });
            var $name = $("<h2>", {
              class: "mfp-modal__name",
              text: blockType.name,
            });
            $img.append($("<img>").attr("src", this.defaultImageUrl));
            $item.prepend($img, $name, $description);
          }

          $grid.append($item);

          // Add a new block type when an item in the modal is clicked
          this.addListener($item, "click", function (ev) {
            var targetBlockTypeHandle = $(ev.currentTarget).attr(
              "data-block-type"
            );

            var matrixInput = $matrixField.data("matrix");
            matrixInput.addBlock(targetBlockTypeHandle);
            modal.hide();
          });
        }.bind(this)
      );

      modal.$container.find(".body").append($grid);

      this.addListener($button, "click", function () {
        modal.show();
      });
    },

    createModal: function ($matrixField, matrixFieldHandle) {
      var $modal = $(
          '<form class="modal fitted mfp-modal" data-matrix-field="' +
            matrixFieldHandle +
            '" />'
        ).appendTo(Garnish.$bod),
        $body = $('<div class="body"/>').appendTo($modal).html(),
        $footer = $('<footer class="footer"/>').appendTo($modal),
        $buttons = $('<div class="buttons right"/>').appendTo($footer),
        $cancelBtn = $(
          '<div class="btn">' + Craft.t("app", "Close") + "</div>"
        ).appendTo($buttons);

      Craft.initUiElements($body);

      var modal = new Garnish.Modal($modal, {
        autoShow: false,
        hideOnEsc: true,
        desiredWidth: 600,
      });

      this.addListener($cancelBtn, "click", function () {
        modal.hide();
      });

      return modal;
    },

    createModalButton: function ($matrixField) {
      var $button = $("<div>")
        .addClass("btn dashed add icon")
        .text($matrixField.find(".menubtn").text());
      $matrixField.find(".buttons").append($button);
      return $button;
    },

    /**
     * Add a preview to the top of every block in the matrix field
     */
    createBlockTypePreview: function ($blockType, preview) {
      if ($blockType.find(".mfp-block-type-preview").length <= 0) {
        if (preview["image"] || preview["description"]) {
          var $div = $("<div>", {
            class: "mfp-block-type-preview",
          });
          var $thumb = $("<div>", {
            class: "mfp-block-type-preview__thumb",
          }).css("background-image", "url('" + preview["thumb"] + "')");
          var $img = $("<img>", {
            class: "mfp-block-type-preview__image",
            src: preview["image"],
          }).hide();
          var $name = $("<p>", {
            class: "mfp-block-type-preview__name",
            text: preview["name"],
          });
          var $description = $("<p>", {
            class: "mfp-block-type-preview__description",
            text: preview["description"],
          });
          var $text = $("<div>").append($name, $description);
          $thumb.on("mouseover", function () {
            $img.fadeIn("fast");
          });
          $img.on("mouseout", function () {
            $img.fadeOut("fast");
          });
          $blockType.find(".fields").prepend($div.append($thumb, $text, $img));
        } else {
          console.warn("Skipping block type preview for " + handle);
        }
      }
    },

    /**
     * Get matrix field handle
     *
     * Retrieve the handle from the data attribute in the matrix field
     */
    getMatrixFieldHandle: function ($matrixField) {
      return $matrixField
        .siblings('input[type="hidden"]')
        .attr("name")
        .match(/^fields\[(.*?)\]$/)[1];
    },
  });
})(jQuery);
