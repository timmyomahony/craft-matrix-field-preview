(function($) {
  var settings = {
    postParameters: {
      previewId: $(".mfp-preview-image").attr("data-preview")
    },
    containerSelector: ".mfp-preview-image",
    uploadAction: "matrix-field-preview/preview-image/upload-preview-image",
    deleteAction: "matrix-field-preview/preview-image/delete-preview-image",
    uploadButtonSelector: ".mfp-preview-image__button--upload",
    deleteButtonSelector: ".mfp-preview-image__button--delete",
    fileInputSelector: "input[name=preview-image]",
    uploadParamName: "previewImage"
  };

  new Craft.ImageUpload(settings);

  var settings = {
    allowSavingAsNew: false,
    onSave: function() {
      location.reload();
    },
    allowDegreeFractions: Craft.isImagick
  };
})(jQuery);
