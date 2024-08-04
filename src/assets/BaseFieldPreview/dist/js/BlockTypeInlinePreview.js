var MFP = MFP || {};

(function ($) {
  MFP.BlockTypeInlinePreview = Garnish.Base.extend({
    $target: null,
    config: null,

    /**
     * @param config: A block type config
     */
    init: function (target, config, defaultImageUrl) {
      this.$target = $(target);
      if (this.$target.data("inlinepreview")) {
        Garnish.log("Double-instantiating a context menu on an element");
        this.$target.data("inlinepreview").destroy();
      }
      this.$target.data("inlinepreview", this);

      this.config = config;
      this.defaultImageUrl = defaultImageUrl;
      this.buildPreview();
    },

    /**
     * 
     */
    buildPreview: function () {
      this.$target.addClass("mfp-block-type-preview");

      var $thumb = $("<div>", {
        class: "mfp-block-type-preview__thumb",
      });

      if (this.config["image"]) {
        $thumb.css("background-image", "url('" + this.config["thumb"] + "')");

        var $img = $("<img>", {
          class: "mfp-block-type-preview__image",
          src: this.config["image"],
        }).hide();

        $thumb.on("mouseover", function () {
          $img.fadeIn("fast");
        });

        $img.on("mouseout", function () {
          $img.fadeOut("fast");
        });
      } else {
        console.warn("No preview image found for handle " + this.config.handle);
        $thumb.css("background-image", "url('" + this.defaultImageUrl + "')");
      }

      var $name = $("<p>", {
        class: "mfp-block-type-preview__name",
        text: this.config["name"],
      });

      var $description = $("<div>", {
        class: "mfp-block-type-preview__description",
        html: this.config["descriptionHTML"],
      });

      var $text = $("<div>").append($name, $description);

      this.$target.append($thumb, $text, $img);
    },
  });
})(jQuery);
