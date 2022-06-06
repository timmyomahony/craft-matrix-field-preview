var MFP = MFP || {};

(function ($) {
  MFP.NeoFieldPreview = MFP.BaseFieldPreview.extend({
    previewsUrl: "matrix-field-preview/preview/get-previews",
    inputType: "neo",
    initialiseInput: function (neoInput, config) {
      neoInput.on(
        "addBlock",
        function (ev) {
          this.blockAdded(ev.block, config);
        }.bind(this)
      );

      neoInput.on(
        "removeBlock",
        function (ev) {
          this.blockRemoved(ev.block);
        }.bind(this)
      );

      this.setupInput(neoInput, config);
    },
    setupInput: function (neoInput, config) {
      var neoBlocks = neoInput.getBlocks();
      var neoBlockTypes = neoInput.getBlockTypes();
      var modal, modalButton;

      neoInput.$container.addClass("mfp-field mfp-neo-field");
      if (neoBlockTypes.length > 0) {
        // Create modal trigger button
        var modalButton = this.createModalButton(
          neoInput.$buttonsContainer.find("> .ni_buttons"),
          config
        );

        // Create modal and grid
        var modal = this.createModal(neoInput.$container, config);

        // When preview button clicked
        modalButton.on("click", function () {
          modal.show();
        });

        // When a modal grid item is clicked
        modal.on(
          "gridItemClicked",
          {},
          function (event) {
            var neoBlockType = this.searchNeoBlockTypes(
              neoBlockTypes,
              event.config.handle
            );
            // FIXME: not sure is this the best way to trigger a new block
            neoInput["@newBlock"]({
              blockType: neoBlockType,
            });
            modal.hide();
          }.bind(this)
        );

        neoInput.modal = modal;
        neoInput.modalButtons = modalButton;
      }

      // Now handle all child blocks
      neoBlocks.forEach(
        function (neoBlock) {
          this.blockAdded(neoBlock, config);
        }.bind(this)
      );
    },
    blockAdded: function (neoBlock, config) {
      var blockHandle = neoBlock._blockType._handle;
      var blockConfig = config["blockTypes"][blockHandle];
      var neoBlockTypes = neoBlock.getButtons().getBlockTypes();

      if (!blockConfig["image"] && !blockConfig["description"]) {
        console.warn("No block types configured for this Neo block");
        return;
      }

      // Add inline preview
      var inlinePreview = this.createInlinePreview(
        neoBlock.$bodyContainer,
        blockConfig,
        neoBlock
      );

      neoBlock.inlinePreview = inlinePreview;

      if (neoBlockTypes.length > 0) {
        // Filter out the block types we need to display for this particular
        // neo block. Not all neo blocks show all block types, so we should
        // only display those relevant
        var filteredConfig = this.filterConfigForBlockTypes(
          neoBlockTypes,
          config
        );

        // Create modal trigger button
        var modalButton = this.createModalButton(
          neoBlock.$buttonsContainer.find(".ni_buttons"),
          config
        );

        neoBlock.modalButton = modalButton;

        // Create modal
        var modal = this.createModal(
          neoBlock.$container,
          filteredConfig,
          neoBlock,
          neoBlockTypes
        );

        neoBlock.modal = modal;

        // When preview button clicked
        modalButton.on("click", function () {
          modal.show();
        });

        // When a modal grid item is clicked
        modal.on(
          "gridItemClicked",
          {},
          function (event) {
            var neoBlockType = this.searchNeoBlockTypes(
              neoBlockTypes,
              event.config.handle
            );
            neoBlock.trigger("newBlock", {
              blockType: neoBlockType,
              level: neoBlock.getLevel() + 1,
            });
            modal.hide();
          }.bind(this)
        );
      }
    },
    blockRemoved: function (neoBlock) {
      this.updateModalButton(neoBlock.modalButton, function () {
        return false;
      });
    },
    filterConfigForBlockTypes: function (neoBlockTypes, config) {
      var filteredConfigs = {};
      for (var i = 0; i < neoBlockTypes.length; i++) {
        var neoBlockType = neoBlockTypes[i];
        var _config = config["blockTypes"][neoBlockType["_handle"]];
        if (_config) {
          filteredConfigs[_config["handle"]] = _config;
        }
      }
      return filteredConfigs;
    },
    searchNeoBlockTypes: function (neoBlockTypes, handle) {
      return $.grep(neoBlockTypes, function (neoBlockType) {
        return neoBlockType.getHandle() === handle;
      })[0];
    },
    getInputClass: function () {
      try {
        return window.Neo.Input;
      } catch (err) {
        return undefined;
      }
    },
    getFieldHandle: function (input) {
      return input._name;
    },
  });
})(jQuery);
