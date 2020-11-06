(function ($) {
  Craft.NeoFieldPreview = Garnish.Base.extend({
    init: function () {
      this.defaultImageUrl = fieldPreviewDefaultImage;

      window.Garnish.on(
        window.Neo.Input,
        "addBlock",
        {},
        function (ev) {
          this.blockAdded($(ev.block));
        }.bind(this)
      );
    },

    blockAdded($block) {
      console.log($block);
    },
  });
})(jQuery);
