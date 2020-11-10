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
            console.debug("Neo input initialised:", ev);
            this.onInputLoaded(ev.target);
          }.bind(this)
        );
      }
    },

    onInputLoaded: function (neoInput) {
      neoInput.on(
        "addBlock",
        function (ev) {
          this.onBlockAdded(ev.block);
        }.bind(this)
      );

      neoInput.on(
        "removeBlock",
        function (ev) {
          this.onBlockRemoved(ev.block);
        }.bind(this)
      );
    },

    onBlockRemoved: function (block) {
      console.debug("Block removed: ", block);
    },

    onBlockAdded: function (block) {
      console.debug("Block added: ", block);
      this.setupBlock(block);
    },

    setupBlock: function (block) {
      // Find all children
    },

    getConfig: function () {},

    createInlinePreview: function () {},

    createButtons: function () {},

    createModal: function () {},
  });
})(jQuery);
