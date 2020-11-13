var MFP = MFP || {};

(function ($) {
  MFP.BaseFieldPreview = Garnish.Base.extend({
    configs: {},
    previewsUrl: null,
    inputClass: null,
    inputType: null,
    init: function () {
      if (typeof this.getInputClass() !== "undefined") {
        // via $view->registerJsVar
        this.defaultImageUrl = matrixFieldPreviewDefaultImage;
        this.previewIcon = matrixFieldPreviewIcon;
        var Input = this.getInputClass();
        if (typeof Input !== "undefined") {
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
              console.error(response["error"]);
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
    createInlinePreview: function ($target, config) {
      var inlinePreview = new MFP.BlockTypeInlinePreview(
        $("<div>"),
        config,
        this.defaultImageUrl
      );
      $target.prepend(inlinePreview.$target);
      return inlinePreview;
    },
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
    getModalButtonSettings: function (config) {
      return {};
    },
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
    getInputClass: function () {
      return this.inputClass;
    },
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
