(function ($) {
  Craft.NeoFieldPreview = Garnish.Base.extend({
    configs: {},
    previewsUrl: "matrix-field-preview/preview/get-previews",

    init: function () {
      this.defaultImageUrl = fieldPreviewDefaultImage;

      if (typeof window.Neo !== "undefined") {
        window.Garnish.on(
          window.Neo.Input,
          "afterInit",
          {},
          function (ev) {
            console.debug("Neo input initialised:", ev);
            this.onInputLoaded(ev.target);
          }.bind(this)
        );
      }
    },

    /**
     * Input loaded
     *
     * When this neo field has loaded, fetch the config from the server and
     * initialise the field and block types.
     */
    onInputLoaded: function (neoInput) {
      var fieldHandle = neoInput._name;
      this.getConfig(fieldHandle)
        .done(
          function (response) {
            if (response["success"]) {
              console.debug("Preview config fetched:", response);
              var config = response["config"];
              this.configs[fieldHandle] = config;
              this.initialiseInput(neoInput, config);
            } else {
              console.error(response["error"]);
            }
          }.bind(this)
        )
        .fail(
          function (response) {
            console.error(
              "Error fetching config for neo field:",
              fieldHandle,
              response
            );
          }.bind(this)
        );
    },

    /**
     * Initialise
     *
     * Add event handlers for a particular input and initialise it
     */
    initialiseInput: function (neoInput, config) {
      neoInput.on(
        "addBlock",
        function (ev) {
          this.setupBlock(ev.block, config);
        }.bind(this)
      );

      neoInput.on(
        "removeBlock",
        function (ev) {
          console.debug("Block removed: ", ev.block);
        }.bind(this)
      );

      this.setupInput(neoInput, config);
    },

    /**
     * Setup input
     *
     * Create dom elements for an initial entire neo input
     */
    setupInput: function (neoInput, config) {
      console.debug("Setting up input: ", neoInput);

      neoInput.$container.addClass("mfp-field mfp-neo-field");

      var $button = this.createButton(config);
      neoInput.$buttonsContainer.find("> .ni_buttons").append($button);

      // Now handle all child blocks
      neoInput._blocks.forEach(
        function (block) {
          this.setupBlock(block, config);
        }.bind(this)
      );
    },

    /**
     * Setup block
     *
     * Create dom elements for a particular neo block
     */
    setupBlock: function (neoBlock, config) {
      console.debug("Setting up block:", neoBlock._blockType._handle, neoBlock);
      var blockTypeHandle = neoBlock._blockType._handle;
      var blockTypeConfig = config["blockTypes"][blockTypeHandle];

      if (!blockTypeConfig["image"] && !blockTypeConfig["description"]) {
        console.warn("No block types configured for this block");
        return;
      }

      // Add inline preview
      var $blockTypePreview = this.createBlockTypePreview(blockTypeConfig);
      neoBlock.$bodyContainer.prepend($blockTypePreview);

      // Add modal previews
      if (neoBlock.$buttonsContainer.length > 0) {
        // Filter out the block types we need to display for this particular
        // neo block. Not all neo blocks show all block types, so we should
        // only display those relevant
        var filteredConfig = {};
        var neoBlockTypes = neoBlock.getButtons().getBlockTypes();
        for (var i = 0; i < neoBlockTypes.length; i++) {
          var neoBlockType = neoBlockTypes[i];
          var _config = config["blockTypes"][neoBlockType["_handle"]];
          if (_config) {
            filteredConfig[_config["handle"]] = _config;
          }
        }

        // Create button
        neoBlock.$mfpButton = this.createButton(config);

        // Create modal
        neoBlock.$mfpButton.on(
          "click",
          function () {
            if (neoBlock.$mfpModal) {
              neoBlock.mfpModal.show();
            } else {
              neoBlock.mfpModal = this.createModal();
              neoBlock.mfpModal = this.populateModal(
                neoBlock.mfpModal,
                filteredConfig
              );
              neoBlock.$container.append(neoBlock.mfpModal);
              neoBlock.mfpModal.show();
            }
          }.bind(this)
        );

        neoBlock.$buttonsContainer
          .find(".ni_buttons")
          .append(neoBlock.$mfpButton);
      }
    },

    /**
     * Get config
     *
     * Get the config for a particular neo input from the server
     */
    getConfig: function (fieldHandle) {
      return $.get({
        url: Craft.getActionUrl(this.previewsUrl),
        data: {
          type: "neo",
          fieldHandle: fieldHandle,
        },
        dataType: "json",
      });
    },

    /**
     * Create block type preview
     *
     * An inline preview for a particular neo block
     */
    createBlockTypePreview: function (blockTypeConfig) {
      var $div = $("<div>", {
        class: "mfp-block-type-preview",
      });

      var $thumb = $("<div>", {
        class: "mfp-block-type-preview__thumb",
      });

      if (blockTypeConfig["image"]) {
        $thumb.css(
          "background-image",
          "url('" + blockTypeConfig["thumb"] + "')"
        );

        var $img = $("<img>", {
          class: "mfp-block-type-preview__image",
          src: blockTypeConfig["image"],
        }).hide();

        $thumb.on("mouseover", function () {
          $img.fadeIn("fast");
        });

        $img.on("mouseout", function () {
          $img.fadeOut("fast");
        });
      } else {
        $thumb.css("background-image", "url('" + this.defaultImageUrl + "')");
      }

      var $name = $("<p>", {
        class: "mfp-block-type-preview__name",
        text: blockTypeConfig["name"],
      });

      var $description = $("<p>", {
        class: "mfp-block-type-preview__description",
        text: blockTypeConfig["description"],
      });

      var $text = $("<div>").append($name, $description);

      return $div.append($thumb, $text, $img);
    },

    /**
     * Create modal
     *
     * An empty Craft Garnish modal
     */
    createModal: function () {
      var $modal = $('<form class="modal fitted mfp-modal"/>'),
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

    /**
     * Populate modal
     *
     * Create previews and add to modal body
     */
    populateModal: function (modal, blockTypesConfigs) {
      var $grid = $("<div>", {
        class: "mfp-modal__grid",
      });

      $.each(
        blockTypesConfigs,
        function (i, blockTypeConfig) {
          var $item = $("<div>", {
            class: "mfp-modal__item",
          }).attr("data-block-type", blockTypeConfig.handle);

          var $img = $("<div>", {
            class: "mfp-modal__image mfp-modal__image--default",
          }).append($("<img>").attr("src", this.defaultImageUrl));

          var $name = $("<h2>", {
            class: "mfp-modal__name",
            text: blockTypeConfig.name,
          });

          var $description = $("<p>", {
            class: "mfp-modal__description",
          });

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

          $item.prepend($img, $name, $description);

          $grid.append($item);

          // Add a new block type when an item in the modal is clicked
          this.addListener($item, "click", function (ev) {
            console.log("test");
            //modal.hide();
          });
        }.bind(this)
      );

      modal.$container.find(".body").append($grid);
      return modal;
    },

    /**
     * Create button
     *
     * Button that launches a modal overlay for a particular neo block
     */
    createButton: function (config) {
      // NOTE: unlike Matrix fields, neo fields cannot be "taken over"
      return $("<div>")
        .addClass("mfp-modal-trigger btn icon dashed search")
        .text("Block Previews");
    },
  });
})(jQuery);
