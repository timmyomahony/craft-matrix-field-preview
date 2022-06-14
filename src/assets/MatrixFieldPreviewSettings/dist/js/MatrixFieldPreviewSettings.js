(function ($) {
  /**
   * Based on user image uploading:
   *
   * https://github.com/craftcms/cms/blob/master/src/web/assets/edituser/src/profile.js
   */
  if ($(".mfp-settings-preview-image").length > 0) {
    new Craft.ImageUpload({
      postParameters: {
        blockTypeConfigId: $(".mfp-settings-preview-image").attr("data-blocktypeconfig"),
      },
      containerSelector: ".mfp-settings-preview-image",
      uploadAction: uploadImageUrl,
      deleteAction: deleteImageUrl,
      uploadButtonSelector: ".btn.mfp-settings-preview-image__upload",
      deleteButtonSelector: ".btn.mfp-settings-preview-image__delete",
      fileInputSelector: "input[name='preview-image']",
      uploadParamName: "previewImage"
    });
  }
})(jQuery);
