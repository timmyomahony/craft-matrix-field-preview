var MFP = MFP || {};

(function ($) {
  /**
   * Block Type Modal
   *
   * A custom modal that extends the default Craft modal to help house our
   * custom block types
   */
  MFP.BlockTypeModal = Garnish.Modal.extend({
    init: function (container, settings, config, defaultImageUrl) {
      Garnish.Modal.prototype.init.call(this, container, settings);

      this.config = config;
      this.defaultImageUrl = defaultImageUrl;
      this.searching = false;

      this.buildModal.call(this);
      this.buildGridItems.call(this);
      this.buildSearchBar.call(this);

      // Hack to resize
      this.desiredWidth = 400;
      Garnish.Modal.prototype.updateSizeAndPosition.call(this);
    },

    buildModal: function () {
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

    buildSearchBar: function () {
      // Create elements
      this.$header = $('<header class="header"/>').prependTo(this.$container);
      this.$searchInputContainer = $(
        '<div class="flex-grow texticon search icon clearable" />'
      );
      this.$searchInput = $(
        '<input class="text" type="text" placeholder="Search" dir="ltr" aria-label="Search" />'
      ).appendTo(this.$searchInputContainer);
      this.$searchInputContainer.appendTo(this.$header);

      // Place elements
      this.$searchInput.appendTo(this.$searchInputContainer);
      this.$searchInputContainer.appendTo(this.$header);

      // When the user types
      this.$searchInput.on(
        "keyup",
        function (ev) {
          this.search(ev.target.value);
        }.bind(this)
      );
    },

    buildGridItems: function () {
      this.$grid = $("<div>", {
        class: "mfp-grid",
      });

      $.each(
        this.config,
        function (i, blockTypeConfig) {
          var $item = $("<div>", {
            class: "mfp-grid-item",
          })
            .attr("data-block-type", blockTypeConfig.handle.toLowerCase())
            .attr("data-name", blockTypeConfig.name.toLowerCase())
            .attr(
              "data-description",
              blockTypeConfig.description.toLowerCase()
            );

          var $img = $("<div>", {
            class: "mfp-grid-item__image mfp-grid-item__image--default",
          }).append($("<img>").attr("src", this.defaultImageUrl));

          var $content = $("<div>", {
            class: "mfp-grid-item__content",
          });

          var $name = $("<h2>", {
            class: "mfp-grid-item__content__name",
            text: blockTypeConfig.name,
          }).appendTo($content);

          var $description = $("<div>", {
            class: "mfp-grid-item__content__description",
          }).appendTo($content);

          if (blockTypeConfig["image"]) {
            $img.removeClass("mfp-grid-item__image--default");
            $img.children("img").attr("src", blockTypeConfig["image"]);
          }
          if (blockTypeConfig["name"]) {
            $name.text(blockTypeConfig["name"]);
          }
          if (blockTypeConfig["description"]) {
            $description.html(blockTypeConfig["descriptionHTML"]);
          }

          $item.prepend($img, $content);

          var onClickHandler = function () {
            this.trigger("gridItemClicked", {
              config: blockTypeConfig,
            });
          };

          // When an item is clicked, insert it
          $img.on("click", onClickHandler.bind(this));
          $name.on("click", onClickHandler.bind(this));

          this.$grid.append($item);
        }.bind(this)
      );

      this.$body.append(this.$grid);
    },

    getGridItems: function () {
      return this.$grid.find(".mfp-grid-item");
    },

    showAll: function () {
      console.debug("Showing all grid items");
      this.getGridItems().show();
    },

    resetSearch: function () {
      this.$searchInput.value = "";
      this.showAll();
    },

    search: function (query) {
      console.debug(`Searching grid items for '${query}'`);

      if (query.length <= 0) {
        this.resetSearch();
        return;
      }

      var lowerQuery = query.toLowerCase();
      $.each(
        this.getGridItems(),
        function (i, gridItem) {
          var blockType = $(gridItem).data("block-type");
          var name = $(gridItem).data("name");
          var description = $(gridItem).data("description");
          var found =
            blockType.includes(lowerQuery) ||
            name.includes(lowerQuery) ||
            description.includes(lowerQuery);
          if (found) {
            $(gridItem).show();
          } else {
            $(gridItem).hide();
          }
        }.bind(this)
      );
    },
  });
})(jQuery);
