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
          this.setupBlock(ev.block);
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
      if (config["field"]["enableTakeover"]) {
        neoInput.$container.addClass("mfp-field--takeover");
      }

      var $button = this.createButton(config);
      neoInput.$buttonsContainer.find("> .ni_buttons").append($button);
    },

    setupBlock: function (neoBlock, config) {
      console.debug("Setting up block:", neoBlock);
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

    createInlinePreview: function () {},

    createButton: function (config) {
      var buttonText = "Add Block";
      var buttonIcon = "add";
      var classes = "mfp-modal-trigger btn icon dashed ";
      if (!config["field"]["enableTakeover"]) {
        buttonText = "Preview Blocks";
        buttonIcon = "search";
      } else {
        classes = classes + "mfp-modal-trigger--takeover ";
      }
      var $button = $("<div>")
        .addClass(classes + buttonIcon)
        .text(buttonText);
      return $button;
    },

    createModal: function () {},
  });
})(jQuery);
