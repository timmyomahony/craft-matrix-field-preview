(function ($) {
  Craft.NeoFieldPreview = Garnish.Base.extend({
    init: function () {
      this.defaultImageUrl = fieldPreviewDefaultImage;

      if (typeof window.Neo !== "undefined") {
        window.Garnish.on(
          window.Neo.Input,
          "afterInit",
          {},
          function (ev) {
            this.onInputLoaded(ev.target);
          }.bind(this)
        );
      }
    },

    onInputLoaded: function (neoInput) {
      console.log(neoInput);
      neoInput.on(
        "addBlock",
        function (ev) {
          this.onBlockAdded(ev.target);
        }.bind(this)
      );
    },

    onBlockAdded: function (block) {
      console.log(block);
    },
  });
})(jQuery);
