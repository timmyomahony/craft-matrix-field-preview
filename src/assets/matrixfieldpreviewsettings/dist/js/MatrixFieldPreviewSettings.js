(function ($) {
  /**
   * Based on user image uploading:
   *
   * https://github.com/craftcms/cms/blob/master/src/web/assets/edituser/src/profile.js
   */
  new Craft.ImageUpload({
    postParameters: {
      blockTypeId: $(".mfp-settings-preview-image").attr("data-blocktype"),
    },
    containerSelector: ".mfp-settings-preview-image",
    uploadAction: "matrix-field-preview/preview-image/upload-preview-image",
    deleteAction: "matrix-field-preview/preview-image/delete-preview-image",
    uploadButtonSelector: ".btn.mfp-settings-preview-image__upload",
    deleteButtonSelector: ".btn.mfp-settings-preview-image__delete",
    fileInputSelector: "input[name='preview-image']",
    uploadParamName: "previewImage",
  });
})(jQuery);
