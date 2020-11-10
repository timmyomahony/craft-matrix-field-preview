var MFP = MFP || {};

(function ($) {
  MFP.BlockTypeModalButton = Garnish.Base.extend({
    init: function (target, config) {
      this.$target = $(target);

      this.$target
        .addClass("mfp-modal-button btn dashed")
        .text(config["field"]["buttonText"]);

      this.$target.on(
        "click",
        function () {
          this.trigger("click");
        }.bind(this)
      );
    },
  });
})(jQuery);
