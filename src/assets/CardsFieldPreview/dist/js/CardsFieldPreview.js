var MFP = MFP || {};

(function ($) {
  /**
   * Cards Field Preview
   *
   * This class handles Matrix fields that use the "cards", "cards-grid", or
   * "index" view modes. These view modes use Craft.NestedElementManager instead
   * of Craft.MatrixInput.
   *
   * Inherits: BaseFieldPreview
   */
  MFP.CardsFieldPreview = MFP.BaseFieldPreview.extend({
    previewsUrl: "matrix-field-preview/preview/get-previews",
    inputType: "matrix",

    /**
     * Get Input Class
     *
     * @returns Craft.NestedElementManager
     */
    getInputClass: function () {
      return Craft.NestedElementManager;
    },

    /**
     * Get Field Handle
     *
     * Extract the field handle from the container ID. The NestedElementManager
     * receives a selector like '#fields-{handle}' or '#fields-{handle}-...'
     *
     * @param {*} input - The Craft.NestedElementManager instance
     * @returns {string|null}
     */
    getFieldHandle: function (input) {
      var id = input.$container.attr("id");
      if (!id) {
        return null;
      }
      // Match pattern: fields-{handle} (may have additional suffixes)
      var regex = /fields-([^-]+)(?!(.*fields-))/g;
      var lastMatch = null;
      var match;
      while ((match = regex.exec(id)) !== null) {
        lastMatch = match[1];
      }
      return lastMatch;
    },

    /**
     * Is Cards View
     *
     * @param {*} input
     * @returns {boolean}
     */
    isIndexView: function (input) {
      return input.$container.hasClass("element-index")
    },

    /**
     * Is Index View
     *
     * @param {*} input
     * @returns {boolean}
     */
    isCardsView: function (input) {
      return input.$container.hasClass("nested-element-cards");
    },

    /**
     * On Input Loaded
     *
     * Override to filter out non-Matrix NestedElementManager instances.
     *
     * @param {*} input
     */
    onInputLoaded: function (input) {
      // Only process if this looks like a Matrix field in cards/index view
      if (!this.isIndexView(input) && !this.isCardsView(input)) {
        return;
      }

      // Call parent implementation
      MFP.BaseFieldPreview.prototype.onInputLoaded.call(this, input);
    },

    /**
     * Initialise Input
     *
     * Set up the previews for a cards/index view Matrix field.
     *
     * @param {*} input
     * @param {*} config
     */
    initialiseInput: function (input, config) {
      input.$container.addClass("mfp-field");
      if (config["field"]["enableTakeover"]) {
        input.$container.addClass("mfp-field--takeover");
      }

      if (this.isIndexView(input)) {
        this.setupIndexView(input, config);
      } else if (this.isCardsView(input)) {
        this.setupCardsView(input, config);
      }
    },

    /**
     * Setup Index View
     *
     * @param {*} input
     * @param {*} config
     */
    setupIndexView: function (input, config) {
      input.$container.addClass("mfp-index-view");

      var $existingBtn = input.$container.find('.toolbar > .search-container').siblings('.btn').first()

      // Create modal button and position it above the cards
      var $buttonContainer = $("<div>", {
        class: "mfp-index-view-button-container",
      });
      $existingBtn.after($buttonContainer);

      var modalButton = this.createModalButton($buttonContainer, config);
      modalButton.$target.removeClass('dashed')
      input.modalButton = modalButton;

      // Create modal
      var modal = this.createModal(input.$container, config);
      input.modal = modal;

      // When preview button clicked
      modalButton.on("click", function () {
        modal.show();
      });

      // Listen for modal item click to create new entry
      modal.on(
        "gridItemClicked",
        {},
        function (event) {
          // NestedElementManager.createElement expects an entry type ID, not a handle
          this.createEntry(input, event.config.id);
          modal.hide();
        }.bind(this)
      );

      // Listen for element additions to add hover previews
      input.on(
        "addElements",
        function (ev) {
          this.onElementsAdded(input, ev.elements, config);
        }.bind(this)
      );

      // Listen for element removals to update button state
      input.on(
        "removeElements",
        function (ev) {
          this.onElementsRemoved(input, ev.elements, config);
        }.bind(this)
      );

      // Initial button state update
      this.updateModalButtonState(input, modalButton);
    },

    /**
     * Setup Cards View
     *
     * @param {*} input
     * @param {*} config
     */
    setupCardsView: function (input, config) {
      input.$container.addClass("mfp-cards-view");

      // Find the flex container that holds the "New entry" button
      var $flexContainer = input.$container.find("> .flex.flex-inline");

      // Create a button container alongside the "New entry" button
      var $buttonContainer = $("<div>", {
        class: "mfp-cards-view-button-container",
      });

      if ($flexContainer.length > 0) {
        $flexContainer.append($buttonContainer);
      } else {
        // Fallback: prepend to the container
        input.$container.prepend($buttonContainer);
      }

      var modalButton = this.createModalButton($buttonContainer, config);
      input.modalButton = modalButton;

      // Create modal
      var modal = this.createModal(input.$container, config);
      input.modal = modal;

      // When preview button clicked
      modalButton.on("click", function () {
        modal.show();
      });

      // Listen for modal item click to create new entry
      modal.on(
        "gridItemClicked",
        {},
        function (event) {
          // NestedElementManager.createElement expects an entry type ID, not a handle
          this.createEntry(input, event.config.id);
          modal.hide();
        }.bind(this)
      );

      // Listen for element additions
      input.on(
        "addElements",
        function (ev) {
          this.onElementsAdded(input, ev.elements, config);
        }.bind(this)
      );

      // Listen for element removals
      input.on(
        "removeElements",
        function (ev) {
          this.onElementsRemoved(input, ev.elements, config);
        }.bind(this)
      );

      // Initial button state update
      this.updateModalButtonState(input, modalButton);
    },

    /**
     * Create Entry
     *
     * Create a new entry of the specified type using the NestedElementManager.
     *
     * @param {*} input - The Craft.NestedElementManager instance
     * @param {*} typeId - The entry type ID (from Craft's EntryType)
     */
    createEntry: function (input, typeId) {
      // NestedElementManager.createElement expects an attributes object with typeId
      if (typeof input.createElement === "function") {
        input.createElement({ typeId: typeId });
      } else if (typeof input.createEntry === "function") {
        // Fallback for older versions
        input.createEntry(typeId);
      } else {
        // Last resort: try to find and click the create button with correct type
        var $createBtn = input.$container.find(
          '[data-type-id="' + typeId + '"]'
        );
        if ($createBtn.length > 0) {
          $createBtn.trigger("click");
        } else {
          console.warn(
            "Could not find method to create entry of type ID: " + typeId
          );
        }
      }
    },

    /**
     * On Elements Added
     *
     * Handle new elements being added to the cards view.
     *
     * @param {*} input
     * @param {*} _elements - Unused but kept for event signature
     * @param {*} config
     */
    onElementsAdded: function (input, _elements, config) {
      console.debug("Elements added to cards field '" + config.field.handle + "'");

      // Update button state
      this.updateModalButtonState(input, input.modalButton);
    },

    /**
     * On Elements Removed
     *
     * Handle elements being removed from the cards view.
     *
     * @param {*} input
     * @param {*} _elements - Unused but kept for event signature
     * @param {*} config
     */
    onElementsRemoved: function (input, _elements, config) {
      console.debug(
        "Elements removed from cards field '" + config.field.handle + "'"
      );

      // Update button state
      this.updateModalButtonState(input, input.modalButton);
    },


    /**
     * Update Modal Button State
     *
     * Enable or disable the modal button based on whether more entries can be added.
     *
     * @param {*} input
     * @param {*} button
     */
    updateModalButtonState: function (input, button) {
      if (!button) {
        return;
      }

      // Use timeout to handle Craft's async DOM updates
      setTimeout(
        function () {
          var canAdd = this.canAddMoreEntries(input);
          if (canAdd) {
            button.enable();
          } else {
            button.disable();
          }
        }.bind(this),
        600
      );
    },

    /**
     * Can Add More Entries
     *
     * Check if more entries can be added to the field.
     *
     * @param {*} input
     * @returns {boolean}
     */
    canAddMoreEntries: function (input) {
      // Check if NestedElementManager has a method for this
      if (typeof input.canAddMoreElements === "function") {
        return input.canAddMoreElements();
      }
      if (typeof input.canAddMoreEntries === "function") {
        return input.canAddMoreEntries();
      }
      // Check for disabled create button as indicator
      var $createBtn = input.$container.find(".btn.add, [data-action='create']");
      if ($createBtn.length > 0 && $createBtn.hasClass("disabled")) {
        return false;
      }
      // Default to allowing additions
      return true;
    },
  });
})(jQuery);
