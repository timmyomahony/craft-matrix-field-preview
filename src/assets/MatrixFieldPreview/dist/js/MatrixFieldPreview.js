var MFP = MFP || {};

(function ($) {
  MFP.MatrixFieldPreview = MFP.BaseFieldPreview.extend({
    previewsUrl: "matrix-field-preview/preview/get-previews",
    inputType: "matrix",
    initialiseInput: function (input, config) {
      input.$container.addClass("mfp-field mfp-matrix-field");
      if (config["field"]["enableTakeover"]) {
        input.$container.addClass("mfp-field--takeover");
      }

      input.on(
        "blockAdded",
        function (ev) {
          this.blockAdded(input, ev.$block, config, true);
        }.bind(this)
      );

      input.on(
        "blockDeleted",
        function (ev) {
          this.blockDeleted(input, ev.$block, config);
        }.bind(this)
      );

      this.setupInput(input, config);
    },
    setupInput: function (input, config) {
      // Create the modal button
      var modalButton = this.createModalButton(
        input.$container.find("> .buttons"),
        config
      );

      input.modalButton = modalButton;

      // Create modal and grid
      var modal = this.createModal(input.$container, config["blockTypes"]);

      // When preview button clicked
      modalButton.on("click", function () {
        modal.show();
      });

      // When modal item is clicked
      modal.on(
        "gridItemClicked",
        {},
        function (event) {
          input.addBlock(event.config.handle);
          modal.hide();
        }.bind(this)
      );

      input.modal = modal;

      // Setup all existing blocks
      var $blocks = input.$blockContainer.children();
      $blocks.each(
        function (i, $block) {
          this.blockAdded(input, $($block), config, false);
        }.bind(this)
      );
    },
    blockAdded: function (input, $block, config, updateButton) {
      // Note that we are using the DOM element here and not the Garnish instance:
      // https://github.com/craftcms/cms/issues/7130
      var blockHandle = $block.attr("data-type");
      var blockConfig = config["blockTypes"][blockHandle];

      if (!blockConfig["image"] && !blockConfig["description"]) {
        console.warn("No block types configured for this block");
        return;
      }

      // Add inline preview
      var inlinePreview = this.createInlinePreview(
        $block.find(".fields"),
        blockConfig
      );

      if (updateButton) {
        this.updateModalButton(input);
      }
    },
    blockDeleted: function (input, $block, config) {
      this.updateModalButton(input);
    },
    getInputClass: function () {
      return Craft.MatrixInput;
    },
    getModalButtonSettings: function (config) {
      if (config["field"]["enableTakeover"]) {
        return {
          takeover: true,
        };
      }
      return {};
    },
    getFieldHandle: function (input) {
      return input.id.replace("fields-", "");
    },
  });
})(jQuery);
