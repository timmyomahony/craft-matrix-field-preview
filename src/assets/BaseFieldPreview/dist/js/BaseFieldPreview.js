var MFP = MFP || {};

/**
 * Base field preview class
 *
 * This is base class for handling the initialisation of all of the JavasScript
 * related to Matrix Field Preview.
 * 
 * Note that both the "Matrix Field Preview" and "Neo Field Preview" JavaScript
 * initialisation stem from this base class:
 * 
 * - MatrixFieldPreview.js
 * - NeoFieldPreview.js (in a different asset bundle)
 * 
 * There are 3 things that need to be loaded:
 * 
 * - BlockTypeInlinePreview: the inline preview for every matrix field block
 *   type that shows a screenshot plus small overlay on-hover.
 * - BlockTypeModalButton: either a simple "preview content" button that shows
 *   the modal, or a "take over" button that replaces the default block type
 *   controls for the matrix field.
 * - BlockTypeModal: the main modal that is launched when the above button is
 *   pressed. This shows the previews and allows the user to select a block
 *   type.
 */
(function ($) {
  MFP.BaseFieldPreview = Garnish.Base.extend({
    configs: {},
    previewsUrl: null,
    inputClass: null,
    inputType: null,

    /**
     * Initalise
     * 
     * This is called automatically by Garnish when a new instance is created
     * via new MatrixFieldPreview();
     */
    init: function () {
      if (typeof this.getInputClass() !== "undefined") {
        // via $view->registerJsVar
        this.defaultImageUrl = matrixFieldPreviewDefaultImage;
        this.previewIcon = matrixFieldPreviewIcon;

        // Attempt to load existing Garnish elements. This handles the
        // the situation where our matrix or neo fields have already loaded
        // before out Matrix Field Preview classes
        var loadFields = function () {
          var fields = [];
          this.getFieldElements().each(function(i, field) {
            var field = $(field).data(this.getDataKey());
            if (field) {
              fields.push(field);
            }
          }.bind(this));
          return fields;
        }.bind(this);

        var fields = loadFields();
        if (fields.length > 0) {
          fields.forEach(function(field) {
            console.debug("Loading FP via existing loaded fields");
            this.onInputLoaded(field);
          }.bind(this));
        } else {
          console.debug("Loading MFP via Garnish 'afterInit' listener");
          Garnish.on(
            this.getInputClass(),
            "afterInit",
            {},
            function (ev) {
              this.onInputLoaded(ev.target);
            }.bind(this)
          );
        }
      }
    },

    /**
     * On Input Loaded
     * 
     * Initalise our Matrix Field Preview classes once the Craft input has
     * finishing loading. Fetch the configurations via Ajax then create
     * the modal and previews.
     * 
     * @param {*} input - the Craft input class being targeted (Craft.MatrixInput)
     */
    onInputLoaded: function (input) {
      var fieldHandle = this.getFieldHandle(input);
      this.getConfig(fieldHandle)
        .done(
          function (response) {
            if (response["success"]) {
              var config = response["config"];
              this.configs[fieldHandle] = config;
              if (!config["field"]["enablePreviews"]) {
                return;
              }
              this.initialiseInput(input, config);
            } else {
              console.warn(
                "No matrix field previews configs found for field " +
                  fieldHandle
              );
            }
          }.bind(this)
        )
        .fail(
          function (response) {
            console.error(
              "Error fetching config for matrix field:",
              fieldHandle,
              response
            );
          }.bind(this)
        );
    },

    /**
     * Create Inline Preview
     * 
     * Initialise the inline preview that is displayed on every existing
     * matrix field block type. This preview shows a screenshot and opens
     * a small overlay on hover.
     * 
     * @param {*} $target 
     * @param {*} config 
     * @returns 
     */
    createInlinePreview: function ($target, config) {
      var inlinePreview = new MFP.BlockTypeInlinePreview(
        $("<div>"),
        config,
        this.defaultImageUrl
      );
      $target.prepend(inlinePreview.$target);
      return inlinePreview;
    },

    /**
     * Create Modal Button
     * 
     * Initialise the button that launches the modal for this matrix field.
     * 
     * @param {*} $target 
     * @param {*} config 
     * @returns 
     */
    createModalButton: function ($target, config) {
      var settings = this.getModalButtonSettings(config);
      var modalButton = new MFP.BlockTypeModalButton(
        $("<div>"),
        settings,
        this.previewIcon
      );
      $target.append(modalButton.$target);
      return modalButton;
    },

    /**
     * Get Modal Button Settings
     * 
     * @param {*} config 
     * @returns 
     */
    getModalButtonSettings: function (config) {
      return {};
    },

    /**
     * Update Modal Button
     * 
     * @param {*} button 
     * @param {*} callback 
     */
    updateModalButton: function (button, callback) {
      // FIXME: There is a bug in Craft. When we remove a block Craft fires
      // the event before the actual element has been removed from the DOM:
      //
      // https://github.com/craftcms/cms/blob/master/src/web/assets/matrix/src/MatrixInput.js#L763
      //
      // So we have to use this timeout hack as a fix
      setTimeout(
        function () {
          if (callback()) {
            button.enable();
          } else {
            button.disable();
          }
        }.bind(this),
        600
      );
    },

    /**
     * Create Modal
     * 
     * @param {*} $target 
     * @param {*} config 
     * @returns 
     */
    createModal: function ($target, config) {
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
     * Get Input Class
     * 
     * @returns 
     */
    getInputClass: function () {
      return this.inputClass;
    },

    /**
     * Get Field Elements
     * 
     * @returns 
     */
    getFieldElements: function () {
      return null;
    },

    /**
     * Get Data Key
     * 
     * @returns 
     */
     getDataKey: function () {
      return null;
    },

    /**
     * Get Config
     * 
     * @param {*} fieldHandle 
     * @returns 
     */
    getConfig: function (fieldHandle) {
      return $.get({
        url: Craft.getActionUrl(this.previewsUrl),
        data: {
          type: this.inputType,
          fieldHandle: fieldHandle,
        },
        dataType: "json",
      });
    },
  });
})(jQuery);
