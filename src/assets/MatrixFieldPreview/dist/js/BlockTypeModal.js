var MFP = MFP || {};

(function ($) {
  /**
   * Block Type Modal
   *
   * A custom modal that extends the default Craft modal to help house our
   * custom block types
   */
  MFP.BlockTypeModal = Garnish.Modal.extend({
    $container: undefined,
    $body: undefined,
    $footer: undefined,
    $toolbar: undefined,
    $buttons: undefined,
    $cancelBtn: undefined,
    $searchInputContainer: undefined,
    $searchInput: undefined,
    $grid: undefined,

    init: function (container, settings, config, defaultImageUrl) {
      Garnish.Modal.prototype.init.call(this, container, settings);

      this.config = config;
      this.defaultImageUrl = defaultImageUrl;
      this.searching = false;

      this.buildModal.call(this);
      this.buildGridItems.call(this);
      this.buildSearchBar.call(this);

      // HACK: This seems like the only way to resize.
      this.desiredHeight = 1000;
      Garnish.Modal.prototype.updateSizeAndPosition.call(this);

      this.on(
        "show",
        function () {
          // HACK: For some reason, this is the only way to get autofocus on
          // open working. It might have something to do with the way the modal
          // fades in - possibly the input isn't visible/ready at this time.
          setTimeout(
            function () {
              this.$searchInput.focus();
            }.bind(this),
            500
          );
        }.bind(this)
      );

      this.on(
        "hide",
        function () {
          this.resetSearch();
        }.bind(this)
      );
    },

    buildModal: function () {
      this.$container.addClass("modal mfp-modal");

      this.$body = $('<div class="mfp-modal__body body"/>');
      this.$footer = $('<footer class="mfp-modal__footer footer"/>');
      this.$toolbar = $(
        '<div class="mfp-modal__footer__toolbar toolbar flex flex-nowrap">'
      );
      this.$buttons = $(
        '<div class="mfp-modal__footer__toolbar__buttons buttons right"/>'
      );
      this.$cancelBtn = $(
        '<div class="btn">' + Craft.t("app", "Close") + "</div>"
      );

      this.$body.appendTo(this.$container);
      this.$footer.appendTo(this.$container);
      this.$toolbar.appendTo(this.$footer);
      this.$buttons.appendTo(this.$toolbar);
      this.$cancelBtn.appendTo(this.$buttons);

      this.$cancelBtn.on(
        "click",
        function () {
          this.hide();
        }.bind(this)
      );
    },

    buildSearchBar: function () {
      // Create elements
      this.$searchInputContainer = $(
        '<div class="mfp-modal__footer__toolbar__search flex-grow texticon search icon clearable" />'
      );
      this.$searchInput = $(
        '<input class="text" type="text" placeholder="Search" dir="ltr" aria-label="Search" />'
      );
      this.$searchEmpty = $(
        '<div class="mfp-modal__empty"><span>No results</span></div>'
      );

      this.$searchInput.appendTo(this.$searchInputContainer);
      this.$searchInputContainer.prependTo(this.$toolbar);
      this.$searchEmpty.appendTo(this.$body);

      this.$searchInput.on(
        "keyup",
        this.debounce(function (ev) {
          this.search(ev.target.value);
        }.bind(this), 400)
      );
    },

    buildGridItems: function () {
      this.$grid = $("<ul>", {
        class: "mfp-grid",
      });

      $.each(
        this.config,
        function (i, blockTypeConfig) {
          var $gridItem = $("<li>", {
            class: "mfp-grid-item",
          })
            .attr("data-block-type", blockTypeConfig.handle.toLowerCase())
            .attr("data-name", blockTypeConfig.name.toLowerCase())
            .attr(
              "data-description",
              blockTypeConfig.description.toLowerCase()
            );

          var $imgContainer = $("<button>", {
            class: "mfp-grid-item__button mfp-grid-item__button--default",
          })
            .attr("tabindex", 0)
            .attr("role", "button");

          var $img = $("<img>").attr("src", this.defaultImageUrl);

          var $content = $("<div>", {
            class: "mfp-grid-item__content",
          });

          var $name = $("<h2>", {
            class: "mfp-grid-item__content__name",
            text: blockTypeConfig.name,
          });

          var $description = $("<div>", {
            class: "mfp-grid-item__content__description",
          });
          if (blockTypeConfig["image"]) {
            $imgContainer.removeClass("mfp-grid-item__button--default");
            $img.attr("src", blockTypeConfig["image"]);
          }
          if (blockTypeConfig["name"]) {
            $name.text(blockTypeConfig["name"]);
          }
          if (blockTypeConfig["description"]) {
            $description.html(blockTypeConfig["descriptionHTML"]);
          }

          $img.appendTo($imgContainer);
          $name.appendTo($content);
          $description.appendTo($content);
          $gridItem.prepend($imgContainer, $content);

          var onClickHandler = function () {
            this.trigger("gridItemClicked", {
              config: blockTypeConfig,
            });
          };

          // When an item is clicked, insert it
          $imgContainer.on("click", onClickHandler.bind(this));
          $name.on("click", onClickHandler.bind(this));

          this.$grid.append($gridItem);
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

    showEmpty: function () {
      this.$searchEmpty.show().css("display", "flex");
    },

    hideEmpty: function () {
      this.$searchEmpty.hide();
    },

    resetSearch: function () {
      this.$searchInput.val("");
      this.showAll();
      this.hideEmpty();
    },

    debounce: function (func, wait, immediate) {
      var timeout;
      return function() {
        var context = this, args = arguments;
        var later = function() {
          timeout = null;
          if (!immediate) func.apply(context, args);
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
      };
    },

    search: function (query) {
      console.debug(`Searching grid items for '${query}'`);

      if (query.length <= 0) {
        this.resetSearch();
        return;
      }

      var count = 0;
      var lowerQuery = query.toLowerCase();
      var $gridItems = this.getGridItems();
      $.each(
        $gridItems,
        function (i, gridItem) {
          var blockType = $(gridItem).data("block-type");
          var name = $(gridItem).data("name");
          var description = $(gridItem).data("description");
          var found =
            blockType.includes(lowerQuery) ||
            name.includes(lowerQuery) ||
            description.includes(lowerQuery);
          if (found) {
            count++;
            $(gridItem).show();
          } else {
            $(gridItem).hide();
          }
        }.bind(this)
      );

      if (count == 0) {
        this.showEmpty();
      } else {
        this.hideEmpty();
        // Focus the first result
        //$gridItems.filter(":visible").find(".mfp-grid-item__button").get(0).focus();
      }
    },
  });
})(jQuery);
