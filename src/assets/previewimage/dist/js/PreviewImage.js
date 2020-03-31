/**
 * Matrix Field Preview plugin for Craft CMS
 *
 * Index Field JS
 *
 * @author    Timmy O'Mahony
 * @copyright Copyright (c) 2020 Timmy O'Mahony
 * @link      https://weareferal.com
 * @package   MatrixFieldPreview
 * @since     1.0.0
 */
(function($) {
  /** global: Craft */
  /** global: Garnish */
  var settings = {
    postParameters: {
      previewId: $(".preview-image").attr("data-preview")
    },
    containerSelector: ".preview-image",
    uploadAction: "matrix-field-preview/preview-image/upload-preview-image",
    deleteAction: "matrix-field-preview/preview-image/delete-preview-image",
    uploadButtonSelector: ".btn.upload-preview-image",
    deleteButtonSelector: ".btn.delete-preview-image",
    fileInputSelector: "input[name=preview-image]",
    uploadParamName: "previewImage",

    onAfterRefreshImage: function(response) {
      //   if (typeof response.html !== "undefined") {
      //     if (
      //       typeof changeSidebarPicture !== "undefined" &&
      //       changeSidebarPicture
      //     ) {
      //       $("#preview-image")
      //         .find("> img")
      //         .replaceWith(
      //           $("#current-preview-image")
      //             .find("> img")
      //             .clone()
      //         );
      //     }
      //   }
    }
  };

  new Craft.ImageUpload(settings);

  var settings = {
    allowSavingAsNew: false,
    onSave: function() {
      // So not optimal.
      location.reload();
    },
    allowDegreeFractions: Craft.isImagick
  };
})(jQuery);
