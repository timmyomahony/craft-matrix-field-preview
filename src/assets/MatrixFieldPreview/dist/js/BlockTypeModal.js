var MFP = MFP || {};

(function ($) {
  /**
   * Block Type Modal
   *
   * A custom modal that extends the default Craft modal to help house our
   * custom block types
   */
  MFP.BlockTypeModal = Garnish.Modal.extend({
    init: function (container, settings) {
      Garnish.Modal.prototype.init.call(this, container, settings);

      this.buildContent();
    },

    buildContent() {
      this.$container.addClass("modal mfp-modal");
      this.$body = $('<div class="body"/>').appendTo(this.$container);
      this.$footer = $('<footer class="footer"/>').appendTo(this.$container);
      this.$buttons = $('<div class="buttons right"/>').appendTo(this.$footer);
      this.$cancelBtn = $(
        '<div class="btn">' + Craft.t("app", "Close") + "</div>"
      ).appendTo(this.$buttons);

      this.$cancelBtn.on(
        "click",
        function () {
          this.hide();
        }.bind(this)
      );
    },
  });
})(jQuery);
