var MFP = MFP || {};

(function ($) {
  /**
   * Matrix Field Preview
   * 
   * This is the "loader" class that is injected after every control panel
   * page load. It's job is to fetch the configurations from the admin panel
   * via AJAX and then initialise the matrix field modal and inline previews.
   * 
   * Inherits: BaseFieldPreview
   */
  MFP.MatrixFieldPreview = MFP.BaseFieldPreview.extend({
    previewsUrl: "matrix-field-preview/preview/get-previews",
    inputType: "matrix",

    /**
     * 
     * @param {*} input 
     * @param {*} config 
     */
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

    /**
     * 
     * @param {*} input 
     * @param {*} config 
     */
    setupInput: function (input, config) {
      // Create the modal button
      var modalButton = this.createModalButton(
        input.$container.find("> .buttons"),
        config
      );

      input.modalButton = modalButton;

      // Create modal and grid
      var modal = this.createModal(input.$container, config);

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

    /**
     * 
     * @param {*} input 
     * @param {*} $block 
     * @param {*} config 
     * @param {*} updateButton 
     * @returns 
     */
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

      // Update the modal button
      this.updateModalButton(input.modalButton, function () {
        return input.canAddMoreBlocks();
      });
    },

    /**
     * 
     * @param {*} input 
     * @param {*} $block 
     * @param {*} config 
     */
    blockDeleted: function (input, $block, config) {
      // Update the modal button
      this.updateModalButton(input.modalButton, function () {
        return input.canAddMoreBlocks();
      });
    },

    /**
     * 
     * @returns 
     */
    getInputClass: function () {
      return Craft.MatrixInput;
    },

    /**
     * Get Field Elements
     *
     * @returns the jQuery field elements on the page 
     */
    getFieldElements: function () {
      return $(".matrix-field");
    },

    /**
     * Get Data Name
     *
     * @returns the key used on the element to store the Garnish plugin
     */
     getDataKey: function () {
      return "matrix";
    },

    /**
     * 
     * @param {*} config 
     * @returns 
     */
    getModalButtonSettings: function (config) {
      if (config["field"]["enableTakeover"]) {
        return {
          takeover: true,
        };
      }
      return {};
    },

    /**
     * 
     * @param {*} input 
     * @returns 
     */
    getFieldHandle: function (input) {
      return input.id.replace("fields-", "");
    },
  });
})(jQuery);
