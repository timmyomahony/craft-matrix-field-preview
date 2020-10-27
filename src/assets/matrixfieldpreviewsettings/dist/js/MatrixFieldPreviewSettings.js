(function ($) {
  var settings = {
    postParameters: {
      previewId: $(".mfp-settings-preview-image").attr("data-preview"),
    },
    containerSelector: ".mfp-settings-preview-image",
    uploadAction: "matrix-field-preview/preview-image/upload-preview-image",
    deleteAction: "matrix-field-preview/preview-image/delete-preview-image",
    uploadButtonSelector: ".mfp-settings-preview-image__button--upload",
    deleteButtonSelector: ".mfp-settings-preview-image__button--delete",
    fileInputSelector: "input[name=preview-image]",
    uploadParamName: "previewImage",
  };

  new Craft.ImageUpload(settings);

  var settings = {
    allowSavingAsNew: false,
    onSave: function () {
      location.reload();
    },
    allowDegreeFractions: Craft.isImagick,
  };
})(jQuery);
