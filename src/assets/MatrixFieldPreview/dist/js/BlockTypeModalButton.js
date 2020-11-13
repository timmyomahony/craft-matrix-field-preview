var MFP = MFP || {};

(function ($) {
  MFP.BlockTypeModalButton = Garnish.Base.extend({
    init: function (target, settings, iconUrl) {
      this.settings = $.extend(
        {
          takeover: false,
          extraClasses: "",
          previewText: "Content Previews",
          takeoverText: "Add Block",
        },
        settings
      );

      console.log(this.settings, iconUrl);

      this.$target = $(target);
      this.$target.addClass("mfp-modal-button btn");

      if (!this.settings.takeover) {
        this.$target
          .addClass("mfp-modal-button--secondary dashed")
          .text(this.settings.previewText);
        this.$target.css("background-image", "url('" + iconUrl + "')");
      } else {
        this.$target
          .addClass("mfp-modal-button--primary icon add")
          .text(this.settings.takeoverText);
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
