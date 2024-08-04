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
     * Initialise Input
     *
     * Create listeners on the input
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
        "entryAdded",
        function (ev) {
          this.blockAdded(input, ev.$entry, config, true);
        }.bind(this)
      );

      input.on(
        "entryDeleted",
        function (ev) {
          this.blockDeleted(input, ev.$entry, config);
        }.bind(this)
      );

      this.setupInput(input, config);
    },

    /**
     * Setup Input
     *
     * @param {*} input
     * @param {*} config
     */
    setupInput: function (input, config) {
      // Create the modal button
      var $modalButtonTarget = input.$container.find("> .buttons");

      // Spoon compatibility
      var $spoonButtons = input.$container.find("> .buttons-spooned");
      if ($spoonButtons.length > 0) {
        $modalButtonTarget = $spoonButtons;
      }

      // MatrixMate compatibility
      var $matrixMateButton = input.$container.find("> .matrixmate-buttons");
      if ($matrixMateButton.length > 0) {
        $modalButtonTarget = $matrixMateButton;
        input.$container.addClass("mfp-field--matrix-mate");
      }

      var modalButton = this.createModalButton($modalButtonTarget, config);

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
          input.addEntry(event.config.handle);
          modal.hide();
        }.bind(this)
      );

      input.modal = modal;

      // Setup all existing blocks
      var $blocks = input.$entriesContainer.children();
      $blocks.each(
        function (i, $block) {
          this.blockAdded(input, $($block), config, false);
        }.bind(this)
      );
    },

    /**
     * Block Added
     * 
     * Respond to the matrix field adding a new block by setting
     * up MFP previews.
     * 
     * TODO: Rename to "onEntryAdded"
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

      // Add inline preview
      if (!blockConfig["image"] && !blockConfig["description"]) {
        console.warn("No block types configured for this block");
      } else {
        var inlinePreview = this.createInlinePreview(
          $block.find("> .fields"),
          blockConfig
        );
      }

      // Update the modal button
      this.updateModalButton(input.modalButton, function () {
        return input.canAddMoreEntries();
      });
    },

    /**
     * Block Deleted
     *
     * @param {*} input
     * @param {*} $block
     * @param {*} config
     */
    blockDeleted: function (input, $block, config) {
      // Update the modal button
      this.updateModalButton(input.modalButton, function () {
        return input.canAddMoreEntries();
      });
    },

    /**
     * Get Input Class
     *
     * @returns
     */
    getInputClass: function () {
      return Craft.MatrixInput;
    },

    /**
     * Get Field Elements
     *
     * @returns
     */
    getFieldElements: function () {
      return $(".matrix-field");
    },

    /**
     * Get Data Key
     *
     * @returns
     */
    getDataKey: function () {
      return "matrix";
    },

    /**
     * Get Field Handle
     *
     * @param {*} input
     * @returns
     */
    getFieldHandle: function (input) {
      return input.id.replace("fields-", "");
    },
  });
})(jQuery);
