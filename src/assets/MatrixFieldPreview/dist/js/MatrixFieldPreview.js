(function ($) {
  /**
   * Create Matrix Field Preview
   *
   * This JavaScript file will only run on Control Panel requests. At a high
   * level it does the following:
   *
   * - Search for all matrix field inputs (via `.matrix-field`)
   * - For each matrix field on the page:
   *  - Fetch JSON configuration of preview fields from custom controller
   *  - Find all existing block types on the page by using the existing
   *    dropdown button used by default
   *  - Insert previews into existing and new block types
   *  - Create alternative modal selector with previews
   *
   * Access to existing MatrixInput:
   *
   * The Craft control panel has its own JavaScript object that is stored
   * on the matrix field HTML DOM element:
   *
   * > $('.matrix-field').data("matrix")
   *
   * But the BIG caveat is that you have to wait for the element to finish
   * executing and setting up. There's no way to wait or know when the
   * matrix field has been initialised.
   *
   * See the existing comments in the MatrixFieldPreview.php file that loads
   * this asset bundle for more details
   */
  Craft.MatrixFieldPreview = Garnish.Base.extend({
    $fields: null,
    fields: {},
    settingsUrl: "matrix-field-preview/preview/get-settings",
    previewsUrl: "matrix-field-preview/preview/get-previews",
    configs: {},
    // FIXME: I'm not sure why, but every time you insert a block
    // into the matrix field, either by the original buttons or by
    // our own modal overlay, our JavaScript code is being
    // reinitialised (not readded, just re-inited). It must be something
    // to do with the MatrixInput.addBlock method from Craft's
    // matrix field JavaScript, but I don't know why it's happening
    init: function (fields) {
      this.defaultImageUrl = fieldPreviewDefaultImage; // via $view->registerJsVar
      this.$fields = $(fields);
      if (this.$fields.length > 0) {
        this.$fields.each(
          function (i, field) {
            var $field = $(field);

            $field.addClass("mfp-matrix-field");

            var fieldHandle = this.getMatrixFieldHandle($field);
            if (!this.fields.hasOwnProperty(fieldHandle)) {
              console.debug(
                "Initialising matrix-field-preview plugin on field '" +
                  fieldHandle +
                  "'"
              );
              this.setupMatrixField($field, fieldHandle);
            } else {
              console.warn(
                "Matrix field '" + fieldHandle + "' is already initialised"
              );
            }
          }.bind(this)
        );
      }
    },

    setupMatrixField: function ($field, fieldHandle) {
      $.get({
        url: Craft.getActionUrl(this.previewsUrl),
        data: {
          fieldHandle: fieldHandle,
        },
        dataType: "json",
        success: function (response) {
          if (response["success"]) {
            console.debug(
              "Received response from matrix field config endpoint: ",
              response
            );
            this.configs[fieldHandle] = response;

            // Skip if previews are not enabled for this matrix field
            if (!response["fieldConfig"]["enablePreviews"]) {
              console.debug(
                "Previews are not enabled for this matrix field " + fieldHandle
              );
              return;
            }

            // Take over the existing matrix field dropdown
            if (response["fieldConfig"]["enableTakeover"]) {
              $field.addClass("mfp-take-over");
            }

            var matrixInput = $field.data("matrix");
            var $existingBlockTypes = $field.find(" > .blocks > .matrixblock");

            // Insert thumbnail previews into _existing_ block types
            $existingBlockTypes.each(
              function (i, blockType) {
                var $blockType = $(blockType);
                var blockTypeHandle = $blockType.attr("data-type");
                var config = this.configs[fieldHandle];
                if (
                  config["blockTypeConfigs"].hasOwnProperty(blockTypeHandle)
                ) {
                  // Insert block type previews for all the static block types
                  console.debug(
                    "Creating preview thumbnail for existing block type " +
                      blockTypeHandle +
                      " in matrix field " +
                      fieldHandle
                  );
                  this.createBlockTypePreview(
                    $blockType,
                    config["blockTypeConfigs"][blockTypeHandle]
                  );
                } else {
                  console.debug(
                    "No preview configuration for existing block type " +
                      blockTypeHandle +
                      " in matrix field " +
                      fieldHandle
                  );
                }
              }.bind(this)
            );

            // Insert thumbnail previews into _new_ block types that are added
            matrixInput.on(
              "blockAdded",
              function (ev) {
                var $blockType = ev["$block"];
                var blockTypeHandle = $blockType.attr("data-type");
                var config = this.configs[fieldHandle];
                console.log(
                  fieldHandle,
                  blockTypeHandle,
                  config,
                  config["blockTypeConfigs"]
                );
                if (
                  config["blockTypeConfigs"].hasOwnProperty(blockTypeHandle)
                ) {
                  console.debug(
                    "Inserting preview thumbnail for new block type " +
                      blockTypeHandle +
                      " in matrix field " +
                      fieldHandle
                  );
                  this.createBlockTypePreview(
                    $blockType,
                    config["blockTypeConfigs"][blockTypeHandle]
                  );
                } else {
                  console.debug(
                    "No preview configuration for new block type " +
                      blockTypeHandle +
                      " in matrix field " +
                      fieldHandle
                  );
                }

                this.updateModalButton($field);
              }.bind(this)
            );

            // Delete preview from our cache when block type is removed
            matrixInput.on(
              "blockDeleted",
              function (ev) {
                this.updateModalButton($field);
              }.bind(this)
            );

            // Insert our custom navigation and modal overlay
            this.createNavigation($field, fieldHandle);

            this.fields[fieldHandle] = $field;

            this.updateModalButton($field);

            $field.addClass("mfp-loaded");
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
    createNavigation: function ($field, fieldHandle) {
      var blockTypes = $field.find("> .buttons > .btngroup .btn").map(
        function (i, button) {
          var $button = $(button);
          return {
            handle: $button.attr("data-type"),
            name: $button.text(),
          };
        }.bind(this)
      );

      console.debug(
        "Blocktypes found in matrix field " + fieldHandle + ":",
        blockTypes
      );

      var modal = this.createModal($field, fieldHandle);
      var matrixInput = $field.data("matrix");
      var $modalButton = this.createModalButton($field, fieldHandle);

      var $grid = $("<div>", {
        class: "mfp-modal__grid",
      });

      blockTypes.each(
        function (i, blockType) {
          console.debug(
            "Creating modal preview for block type " +
              blockType.handle +
              " in matrix field " +
              fieldHandle
          );

          var config = this.configs[fieldHandle];

          var $item = $("<div>", {
            class: "mfp-modal__item",
          }).attr("data-block-type", blockType.handle);

          var $img = $("<div>", {
            class: "mfp-modal__image mfp-modal__image--default",
          }).append($("<img>").attr("src", this.defaultImageUrl));

          var $name = $("<h2>", {
            class: "mfp-modal__name",
            text: blockType.name,
          });

          var $description = $("<p>", {
            class: "mfp-modal__description",
          });

          if (config["blockTypeConfigs"].hasOwnProperty(blockType.handle)) {
            var blockTypeConfig = config["blockTypeConfigs"][blockType.handle];
            if (blockTypeConfig["image"]) {
              $img.removeClass("mfp-modal__image--default");
              $img.children("img").attr("src", blockTypeConfig["image"]);
            }
            if (blockTypeConfig["name"]) {
              $name.text(blockTypeConfig["name"]);
            }
            if (blockTypeConfig["description"]) {
              $description.text(blockTypeConfig["description"]);
            }
          }

          $item.prepend($img, $name, $description);

          $grid.append($item);

          // Add a new block type when an item in the modal is clicked
          this.addListener($item, "click", function (ev) {
            var targetBlockTypeHandle = $(ev.currentTarget).attr(
              "data-block-type"
            );
            matrixInput.addBlock(targetBlockTypeHandle);
            modal.hide();
          });
        }.bind(this)
      );

      modal.$container.find(".body").append($grid);

      this.addListener($modalButton, "click", function () {
        if (matrixInput.canAddMoreBlocks()) {
          modal.show();
        } else {
          console.debug("Maximum number of blocks reached");
        }
      });
    },

    createModal: function ($field, fieldHandle) {
      var $modal = $(
          '<form class="modal fitted mfp-modal" data-matrix-field="' +
            fieldHandle +
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

    createModalButton: function ($field, fieldHandle) {
      var buttonText = $field.find(".menubtn").text();
      var buttonIcon = "add";
      var config = this.configs[fieldHandle];
      if (!config["fieldConfig"]["enableTakeover"]) {
        buttonText = "Preview Blocks";
        buttonIcon = "search";
      }
      var $button = $("<div>")
        .addClass("mfp-modal-trigger btn icon dashed " + buttonIcon)
        .text(buttonText);
      $field.find(".buttons").append($button);
      return $button;
    },

    updateModalButton: function ($field) {
      // FIXME: There is a bug in Craft. When we remove a block Craft fires
      // the event before the actual element has been removed from the DOM:
      //
      // https://github.com/craftcms/cms/blob/master/src/web/assets/matrix/src/MatrixInput.js#L763
      //
      // So we have to use this timeout hack as a fix
      setTimeout(
        function () {
          if ($field.data("matrix").canAddMoreBlocks()) {
            this.enableModalButton($field);
          } else {
            this.disableModalButton($field);
          }
        }.bind(this),
        600
      );
    },

    disableModalButton: function ($field) {
      console.log("Adding disabled", $field.find(".mfp-modal-trigger"));
      $field.find(".mfp-modal-trigger").addClass("disabled");
    },

    enableModalButton: function ($field) {
      console.log("Removing disabled");
      $field.find(".mfp-modal-trigger").removeClass("disabled");
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
          });

          if (preview["image"]) {
            $thumb.css("background-image", "url('" + preview["thumb"] + "')");

            var $img = $("<img>", {
              class: "mfp-block-type-preview__image",
              src: preview["image"],
            }).hide();

            $thumb.on("mouseover", function () {
              $img.fadeIn("fast");
            });

            $img.on("mouseout", function () {
              $img.fadeOut("fast");
            });
          } else {
            $thumb.css(
              "background-image",
              "url('" + this.defaultImageUrl + "')"
            );
          }

          var $name = $("<p>", {
            class: "mfp-block-type-preview__name",
            text: preview["name"],
          });

          var $description = $("<p>", {
            class: "mfp-block-type-preview__description",
            text: preview["description"],
          });

          var $text = $("<div>").append($name, $description);
          $blockType.find(".fields").prepend($div.append($thumb, $text, $img));
        }
      }
    },

    /**
     * Get matrix field handle
     *
     * Retrieve the handle from the data attribute in the matrix field
     */
    getMatrixFieldHandle: function ($field) {
      return $field
        .siblings('input[type="hidden"]')
        .attr("name")
        .match(/^fields\[(.*?)\]$/)[1];
    },
  });
})(jQuery);
