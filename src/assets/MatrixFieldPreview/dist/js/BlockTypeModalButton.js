var MFP = MFP || {};

(function ($) {
  MFP.BlockTypeModalButton = Garnish.Base.extend({
    init: function (target, text, extraClasses) {
      this.$target = $(target);
      this.$target.addClass("mfp-modal-button btn dashed").text(text);
      if (extraClasses) {
        this.$target.addClass(extraClasses);
      }
      this.$target.on(
        "click",
        function () {
          this.trigger("click");
        }.bind(this)
      );
    },
    disable: function () {
      this.$target.addClass("disabled");
    },
    enable: function () {
      this.$target.removeClass("disabled");
    },
  });
})(jQuery);
