var MFP = MFP || {};

(function ($) {
  MFP.NeoFieldPreview = Garnish.Base.extend({
    configs: {},
    previewsUrl: "matrix-field-preview/preview/get-previews",

    init: function () {
      this.defaultImageUrl = fieldPreviewDefaultImage;
      this.previewIconUrl = previewIcon;

      if (typeof Neo !== "undefined") {
        Garnish.on(
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
              this.initialiseNeoInput(neoInput, config);
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
    initialiseNeoInput: function (neoInput, config) {
      neoInput.on(
        "addBlock",
        function (ev) {
          this.setupNeoBlock(ev.block, config);
        }.bind(this)
      );

      neoInput.on(
        "removeBlock",
        function (ev) {
          console.debug("Block removed: ", ev.block);
        }.bind(this)
      );

      this.setupNeoInput(neoInput, config);
    },

    /**
     * Setup input
     *
     * Create dom elements for an initial entire neo input
     */
    setupNeoInput: function (neoInput, config) {
      console.debug("Setting up input: ", neoInput);
      var neoBlockTypes = neoInput.getBlockTypes();
      var modal, modalButton;

      neoInput.$container.addClass("mfp-field mfp-neo-field");
      if (neoBlockTypes.length > 0) {
        // Create modal trigger button
        modalButton = this.createModalButton(
          neoInput.$buttonsContainer.find("> .ni_buttons"),
          config,
          neoInput
        );

        // Create modal and grid
        modal = this.createModal(
          neoInput.$container,
          config["blockTypes"],
          neoInput,
          neoBlockTypes
        );

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
      }

      // Now handle all child blocks
      neoInput._blocks.forEach(
        function (neoBlock) {
          this.setupNeoBlock(neoBlock, config);
        }.bind(this)
      );
    },

    /**
     * Setup block
     *
     * Create dom elements for a particular neo block
     */
    setupNeoBlock: function (neoBlock, config) {
      console.debug("Setting up block:", neoBlock._blockType._handle, neoBlock);
      var blockTypeHandle = neoBlock._blockType._handle;
      var blockTypeConfig = config["blockTypes"][blockTypeHandle];
      var neoBlockTypes = neoBlock.getButtons().getBlockTypes();
      var inlinePreview, modal, modalButton, grid;

      if (!blockTypeConfig["image"] && !blockTypeConfig["description"]) {
        console.warn("No block types configured for this Neo block");
        return;
      }

      // Add inline preview
      inlinePreview = this.createInlinePreview(
        neoBlock.$bodyContainer,
        blockTypeConfig,
        neoBlock
      );

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
          config,
          neoBlock
        );

        // Create modal
        var modal = this.createModal(
          neoBlock.$container,
          filteredConfig,
          neoBlock,
          neoBlockTypes
        );

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

    createInlinePreview: function ($target, config, neoBlock) {
      var inlinePreview = new MFP.BlockTypeInlinePreview(
        $("<div>"),
        config,
        this.defaultImageUrl
      );
      neoBlock.$bodyContainer.prepend(inlinePreview.$target);
      return inlinePreview;
    },

    createModalButton: function ($target, config, neoBlock) {
      var modalButton = new MFP.BlockTypeModalButton($("<div>"), config);
      $target.append(modalButton.$target);
      return modalButton;
    },

    createModal: function ($target, config, neoBlock, neoBlockTypes) {
      var modal = new MFP.BlockTypeModal(
        $("<div>"),
        {
          autoShow: false,
          closeOtherModals: true,
          hideOnEsc: true,
          resizable: false,
        },
        config,
        this.defaultImageUrl
      );
      $target.append(modal.$container);
      return modal;
    },

    /**
     *
     */
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
  });
})(jQuery);
