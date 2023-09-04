var MFP = MFP || {};

(function ($) {
  MFP.NeoFieldPreview = MFP.BaseFieldPreview.extend({
    previewsUrl: "matrix-field-preview/preview/get-previews",
    inputType: "neo",
    /**
     * Initialise Input
     *
     * @param {*} neoInput
     * @param {*} config
     */
    initialiseInput: function (neoInput, config) {
      if (config["field"]["enableTakeover"]) {
        neoInput.$container.addClass("mfp-field--takeover");
      }

      neoInput.on(
        "addBlock",
        function (ev) {
          this.setupNestedPreview(neoInput, ev.block, config);
        }.bind(this)
      );

      neoInput.on(
        "removeBlock",
        function (ev) {
          this.tearDownNestedPreview(ev.block);
        }.bind(this)
      );

      this.setupInput(neoInput, config);
    },

    /**
     * Setup Input
     *
     * @param {*} neoInput
     * @param {*} config
     */
    setupInput: function (neoInput, config) {
      neoInput.$container.addClass("mfp-field mfp-neo-field");

      // First setup previews for the top-level input
      this.setupTopLevelPreview(neoInput, config);

      // Now setup all included blocks
      neoInput.getBlocks().map((neoBlock) => {
        this.setupNestedPreview(neoInput, neoBlock, config);
      });
    },

    /**
     * Setup previews on the top-level element
     *
     *
     * @param {*} neoInput
     * @param {*} config
     */
    setupTopLevelPreview: function (neoInput, config) {
      var topLevelModal, topLevelModalButton;
      var topLevelBlockTypes = neoInput.getBlockTypes(true);
      var topLevelConfig = this.createBlockConfig(topLevelBlockTypes, config);

      if (topLevelBlockTypes.length > 0) {
        // Create modal trigger button
        topLevelModalButton = this.createModalButton(
          neoInput.$buttonsContainer.find("> .ni_buttons"),
          topLevelConfig
        );

        // Create modal and grid
        topLevelModal = this.createModal(neoInput.$container, topLevelConfig);

        // When preview button clicked
        topLevelModalButton.on("click", function () {
          topLevelModal.show();
        });

        // When a modal grid item is clicked
        topLevelModal.on(
          "gridItemClicked",
          {},
          function (event) {
            var neoBlockType = this.searchNeoBlockTypes(
              topLevelBlockTypes,
              event.config.handle
            );
            // FIXME: not sure is this the best way to trigger a new block
            neoInput["@newBlock"]({
              blockType: neoBlockType,
            });
            topLevelModal.hide();
          }.bind(this)
        );

        neoInput.modal = topLevelModal;
        neoInput.modalButtons = topLevelModalButton;
      }
    },

    /**
     * Setup preview on nested block
     *
     * @param {*} neoBlock
     * @param {*} config
     * @returns
     */
    setupNestedPreview: function (neoInput, neoBlock, config) {
      var blockHandle = neoBlock._blockType._handle;
      var blockConfig = config["blockTypes"][blockHandle];
      var neoChildBlockTypes = neoBlock.getButtons().getBlockTypes();

      // Add inline preview
      if (!blockConfig["image"] && !blockConfig["description"]) {
        console.warn(
          "No inline preview block types configured for this Neo block"
        );
      } else {
        neoBlock.inlinePreview = this.createInlinePreview(
          neoBlock.$bodyContainer,
          blockConfig,
          neoBlock
        );
      }

      if (neoChildBlockTypes.length > 0) {
        var modal, modalButton;
        var blockConfig = this.createBlockConfig(neoChildBlockTypes, config);

        // Create modal trigger button
        modalButton = this.createModalButton(
          neoBlock.$buttonsContainer.find(".ni_buttons"),
          config
        );

        // Create modal
        modal = this.createModal(
          neoBlock.$container,
          blockConfig,
          neoBlock,
          neoChildBlockTypes
        );

        // Show modal on click
        modalButton.on("click", function () {
          modal.show();
        });

        // Add block when preview is clicked in modal
        modal.on(
          "gridItemClicked",
          {},
          function (event) {
            var neoBlockType = this.searchNeoBlockTypes(
              neoChildBlockTypes,
              event.config.handle
            );
            neoBlock.trigger("newBlock", {
              blockType: neoBlockType,
              level: neoBlock.getLevel() + 1,
            });
            modal.hide();
          }.bind(this)
        );

        neoBlock.modalButton = modalButton;
        neoBlock.modal = modal;
      }

      // Handle "add block above" on Neo dropdown menu
      // https://github.com/spicywebau/craft-neo/blob/main/src/assets/src/input/Input.js#L1141
      neoBlock.on(
        "addBlockAbove",
        function (ev) {
          neoInput._tempButtons.$container.css("height", "auto");

          var inlineModalButton = this.createModalButton(
            neoInput._tempButtons.$container,
            config
          );

          inlineModalButton.on("click", function () {
            var parentBlock = neoBlock.getParent();
            // Parent is top-level
            if (parentBlock === null) {
              neoInput.modal.show();
            } else {
              parentBlock.modal.show();
            }
          });
        }.bind(this)
      );
    },

    /**
     * Tear down preview for nested block
     *
     * @param {*} neoBlock
     */
    tearDownNestedPreview: function (neoBlock) {
      // this.updateModalButton(neoBlock.modalButton, function () {
      //   return false;
      // });
    },

    /**
     * Filter Config For Block Types
     *
     * Given a list of Neo block types, return the matching configs.
     *
     * @param {*} neoBlockTypes
     * @param {*} config
     * @returns A object that maps the block type handle to the MFP config
     * object
     */
    createBlockConfig: function (neoBlockTypes, config) {
      var filteredBlockTypes = {};
      for (var i = 0; i < neoBlockTypes.length; i++) {
        var neoBlockType = neoBlockTypes[i];
        var _config = config["blockTypes"][neoBlockType["_handle"]];
        if (_config) {
          filteredBlockTypes[_config["handle"]] = _config;
        }
      }
      return $.extend({}, config, { blockTypes: filteredBlockTypes });
    },

    /**
     * Searchh Neo Block Types
     *
     * @param {*} neoBlockTypes
     * @param {*} handle
     * @returns
     */
    searchNeoBlockTypes: function (neoBlockTypes, handle) {
      return $.grep(neoBlockTypes, function (neoBlockType) {
        return neoBlockType.getHandle() === handle;
      })[0];
    },

    /**
     * Get Input Class
     *
     * @returns
     */
    getInputClass: function () {
      try {
        return window.Neo.Input;
      } catch (err) {
        return undefined;
      }
    },

    /**
     * Get Field Elements
     *
     * @returns
     */
    getFieldElements: function () {
      return $(".neo-input");
    },

    /**
     * Get Data Key
     *
     * @returns
     */
    getDataKey: function () {
      return "neo";
    },

    /**
     * Get Field Handle
     *
     * @param {*} input
     * @returns
     */
    getFieldHandle: function (input) {
      return input._name;
    },
  });
})(jQuery);
