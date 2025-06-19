var MFP = MFP || {};

(function ($) {
  /**
   * Matrix Field Preview
   *
   * This is the "loader" class that is injected after every control panel
   * page load. Its job is to fetch the configurations from the admin panel
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
          this.onEntryAdded(input, ev.$entry, config, true);
        }.bind(this)
      );

      input.on(
        "entryDeleted",
        function (ev) {
          this.onEntryDeleted(input, ev.$entry, config);
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
          this.onEntryAdded(input, $($block), config, false);
        }.bind(this)
      );
    },

    /**
     * Entry Added
     * 
     * Respond to the matrix field adding a new entry by setting
     * up MFP previews.
     *
     * @param {*} input
     * @param {*} $block
     * @param {*} config
     * @param {*} updateButton
     * @returns
     */
    onEntryAdded: function (input, $block, config, updateButton) {
      // Note that we are using the DOM element here and not the Garnish instance:
      // https://github.com/craftcms/cms/issues/7130
      var blockHandle = $block.attr("data-type");
      var blockConfig = config["blockTypes"][blockHandle];

      console.debug("Entry added to matrix field '" + config.field.handle + "' : '" + blockHandle + "'");

      // Add inline preview
      if (blockConfig && (blockConfig["image"] || blockConfig["description"])) {
        var inlinePreview = this.createInlinePreview(
          $block.find("> .fields"),
          blockConfig
        );
      } else {
        console.warn("No entry types configured for this entry");
      }
      
      // Update the modal button
      this.updateModalButton(input.modalButton, function () {
        return input.canAddMoreEntries();
      });

      // Add menu action to the block
      if (input.canAddMoreEntries()) {
        this.insertMenuAction(input, $block, config);
      }
    },

    /**
     * Block Deleted
     *
     * @param {*} input
     * @param {*} $block
     * @param {*} config
     */
    onEntryDeleted: function (input, $block, config) {
      var blockHandle = $block.attr("data-type");

      console.debug("Entry deleted from matrix field '" + config.field.handle + "' : '" + blockHandle + "'");
    
      // Update the modal button
      this.updateModalButton(input.modalButton, function () {
        return input.canAddMoreEntries();
      });
    },

    /**
     * Insert Menu Action
     *
     * Add an action to the matrix field. An action is an inline button in the dropdown menu
     * to the top-right of every block that lets the user launch the preview modal. 
     *
     * @param {*} input 
     * @param {*} $block 
     * @param {*} config 
     */
    insertMenuAction: function (input, $block, config) {
      var buttonLabel = config['field']['buttonLabel'] || Craft.t('matrix-field-preview', 'New Entry');
      var buttonIcon = config['field']['buttonIcon'];
      
      // HACK: The disclosure menu is not available immediately after the
      // block is added, so we need to wait for it to be available.
      setTimeout(function () {
        var disclosureMenu = $block.find(".action-btn").data('disclosureMenu')

        // Create a new HR and item
        disclosureMenu.addHr();
        disclosureMenu.addGroup();
        var item = disclosureMenu.addItem({
          icon: buttonIcon ? async () => await Craft.ui.icon(buttonIcon) : '',
          label: buttonLabel,
        });

        // Add click handler to the new menu item
        $(item).on("click", function () {
          input.modal.show();
        });

        // Move the new hr and menu item "up" so its directly below the native matrix field items
        var dstHr = disclosureMenu.$container.children('hr').eq(3);
        var dstUl = disclosureMenu.$container.children('ul').eq(3);
        var srcHr = disclosureMenu.$container.children('hr').last()
        var srcUl = disclosureMenu.$container.children('ul').last()
        $(srcHr).insertAfter(dstHr);
        $(srcUl).insertAfter(dstHr);

        // If the field has "takeover" enabled, remove the native menu items
        if (config['field']['enableTakeover'] == true) {
          dstHr.remove()
          dstUl.remove()
        }
      }, 100);      
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
     * FIXME: Ideally there would be a better approach to getting the matrix
     * field handle from Craft's matrix field implementations, but that information
     * doesn't seem to be stored so we have to use the element's CSS ID along with
     * some regex to pull it.
     * 
     * @param {*} input
     * @returns
     */
    getFieldHandle: function (input) {
      const regex = /fields-([^-]+)(?!(.*fields-))/g;
      while ((match = regex.exec(input.id)) !== null) {
        lastMatch = match[1];
      }
      return lastMatch;
    },
  });
})(jQuery);
