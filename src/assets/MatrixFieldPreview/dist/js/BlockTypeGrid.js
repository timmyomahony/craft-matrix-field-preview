var MFP = MFP || {};

(function ($) {
  MFP.BlockTypeGrid = Garnish.Base.extend({
    $target: null,
    config: null,
    /**
     * @param config: array of block type configs
     */
    init: function (target, config, defaultImageUrl) {
      this.$target = $(target);
      if (this.$target.data("blocktypegrid")) {
        Garnish.log("Double-instantiating a context menu on an element");
        this.$target.data("blocktypegrid").destroy();
      }
      this.$target.data("blocktypegrid", this);
      this.config = config;
      this.defaultImageUrl = defaultImageUrl;
      this.buildGridItems();
    },

    buildGridItems: function () {
      this.$target.addClass("mfp-grid");
      $.each(
        this.config,
        function (i, blockTypeConfig) {
          var $item = $("<div>", {
            class: "mfp-grid-item",
          }).attr("data-block-type", blockTypeConfig.handle);

          var $img = $("<div>", {
            class: "mfp-grid-item__image mfp-grid-item__image--default",
          }).append($("<img>").attr("src", this.defaultImageUrl));

          var $name = $("<h2>", {
            class: "mfp-grid-item__name",
            text: blockTypeConfig.name,
          });

          var $description = $("<p>", {
            class: "mfp-grid-item__description",
          });

          if (blockTypeConfig["image"]) {
            $img.removeClass("mfp-grid-item__image--default");
            $img.children("img").attr("src", blockTypeConfig["image"]);
          }
          if (blockTypeConfig["name"]) {
            $name.text(blockTypeConfig["name"]);
          }
          if (blockTypeConfig["description"]) {
            $description.text(blockTypeConfig["description"]);
          }

          $item.prepend($img, $name, $description);

          $item.on(
            "click",
            function () {
              this.trigger("gridItemClicked", {
                config: blockTypeConfig,
              });
            }.bind(this)
          );

          this.$target.append($item);
        }.bind(this)
      );
    },
  });
})(jQuery);
