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

    setupInput: function (neoInput, config) {
      console.debug("Setting up input: ", neoInput);

      neoInput.$container.addClass("mfp-field mfp-neo-field");

      var $button = this.createButton(config);
      neoInput.$buttonsContainer.find("> .ni_buttons").append($button);
      neoInput._blocks.forEach(
        function (block) {
          this.setupBlock(block, config);
        }.bind(this)
      );
    },

    setupBlock: function (neoBlock, config) {
      console.debug("Setting up block:", neoBlock);

      var blockTypeHandle = neoBlock._blockType._handle;
      var blockTypeConfig = config["blockTypes"][blockTypeHandle];

      if (blockTypeConfig["image"] || blockTypeConfig["description"]) {
        var $blockTypePreview = this.createBlockTypePreview(blockTypeConfig);
        neoBlock.$bodyContainer.prepend($blockTypePreview);
      }

      if (neoBlock.$buttonsContainer.length > 0) {
        var $button = this.createButton(config);
        neoBlock.$buttonsContainer.find(".ni_buttons").append($button);
      }
    },

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
     * Create the inline preview with image and description
     */
    createBlockTypePreview: function (blockTypeConfig) {
      console.log(blockTypeConfig);
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

    createButton: function (config) {
      // NOTE: unlike Matrix fields, neo fields cannot be "taken over"
      return $("<div>")
        .addClass("mfp-modal-trigger btn icon dashed search")
        .text("Block Previews");
    },

    createModal: function () {},
  });
})(jQuery);
