var MFP = MFP || {};

(function ($) {
  /**
   * Matrix Field Preview block type modal
   *
   * A modal dialog based on the default Garnish modal code. This is also
   * built and styled with similar functionality to the default asset overlay
   * used on elements with a sidebar and search toolbar.
   *
   * Useful links:
   *
   * - https://github.com/craftcms/cms/blob/2d53a30c99b356ba79705f2a9181706e7c39b388/src/web/assets/garnish/src/Modal.js
   */
  MFP.BlockTypeModal = Garnish.Modal.extend({
    $container: undefined,

    query: "",
    category: undefined,
    // Used for Neo only: when inserting a block "above" we need to track positioning
    insertionIndex: undefined,
  
    /**
     * 
     * @param {*} container 
     * @param {*} settings 
     * @param {*} config 
     * @param {*} defaultImageUrl 
     */
    init: function (container, settings, config, defaultImageUrl) {
      this.config = config;
      this.defaultImageUrl = defaultImageUrl;
      this.searching = false;

      settings["resizable"] = true;

      Garnish.Modal.prototype.init.call(this, container, settings);

      this.buildModalHtml.call(this);

      // HACK: This seems like the only way to resize.
      this.desiredHeight = 1000;
      this.desiredWidth = 1200;
      Garnish.Modal.prototype.updateSizeAndPosition.call(this);
    },

    /**
     * 
     * @param {*} ev 
     * @returns 
     */
    selectCategory: function (ev) {
      var $href = $(ev.target);
      this.category = $href.data("category");
      this.$container.find(".mfp-modal__sidebar__a").removeClass("sel");
      $href.addClass("sel");
      this.filter();
      ev.preventDefault;
      return false;
    },

    /**
     * 
     * @returns 
     */
    buildSidebarHtml: function () {
      var sidebar = $('<aside class="mfp-modal__sidebar sidebar"/>');
      var sidebarNav = $('<nav class="mfp-modal__sidebar__nav" />');
      var sidebarUl = $('<ul class="mfp-modal__sidebar__ul" />');

      // Add link for each category
      $.each(
        this.config["categories"],
        function (i, category) {
          var sidebarHref = $("<a class='mfp-modal__sidebar__a' />")
            .text(category.name)
            .attr("data-category", category.id);
          var sidebarLi = $("<li class='mfp-modal__sidebar__li'></li>").append(
            sidebarHref
          );
          if (category.description.length > 0) {
            sidebarHref.append(
              $("<span />", {
                class: "info",
                title: category.description,
              })
            );
          }
          sidebarUl.append(sidebarLi);

          sidebarLi.on("click", this.selectCategory.bind(this));
        }.bind(this)
      );
      sidebar.append(sidebarNav.append(sidebarUl));

      // Add default 'All' option
      var sidebarDefaultLi = $(
        "<li class='mfp-modal__sidebar__li'></li>"
      ).append(
        $("<a class='mfp-modal__sidebar__a sel' />")
          .text(Craft.t("matrix-field-preview", "All Categories"))
          .attr("data-category", undefined)
      );
      sidebarUl.prepend(sidebarDefaultLi);

      sidebarDefaultLi.on("click", this.selectCategory.bind(this));

      return sidebar;
    },

    /**
     * 
     * @returns 
     */
    buildFooterHtml: function () {
      var footer = $("<footer />", {
        class: "mfp-modal__footer footer",
      });
      var footerClose = $("<button />", {
        class: "btn",
        type: "button",
        tabindex: 0,
      }).text(Craft.t("matrix-field-preview", "Close"));
      var footerButtons = $(
        '<div class="mfp-modal__footer__toolbar__buttons buttons right"/>'
      );

      footerClose.on(
        "click",
        function () {
          this.hide();
        }.bind(this)
      );

      footerButtons.append(footerClose);
      footer.append(footerButtons);
      return footer;
    },

    /**
     * Build toolbar HTML
     *
     */
    buildToolbarHtml: function () {
      var seachInput = $("<input />", {
        class: "mfp-modal__toolbar__search__input text fullwidth",
        type: "text",
        placeholder: "Search",
        dir: "ltr",
        "aria-lable": "Search",
      });
      var searchContainer = $("<div />", {
        class:
          "mfp-modal__toolbar__search flex-grow texticon search icon clearable",
      });
      var toolbar = $("<div />", {
        class: "mfp-modal__toolbar toolbar flex flex-nowrap",
      });

      toolbar.append(searchContainer.append(seachInput));

      seachInput.on(
        "keyup",
        this.debounce(
          function (ev) {
            this.query = ev.target.value;
            this.filter();
          }.bind(this),
          400
        )
      );

      return toolbar;
    },

    /**
     * Build empty message Html
     *
     */
    buildEmptyMessageHtml: function () {
      return $(
        '<div class="mfp-modal__empty"><span>No block types found</span></div>'
      );
    },

    /**
     * Build grid items Html
     *
     */
    buildGridItemsHtml: function () {
      var gridContainer = $("<div />", {
        class: "mfp-modal__grid",
      });
      var grid = $("<ul />", { class: "mfp-grid" });

      $.each(
        this.config["blockTypes"],
        function (i, blockTypeConfig) {
          var gridItem = $("<li>", {
            class: "mfp-grid-item",
          })
            .attr("data-block-type", blockTypeConfig.handle.toLowerCase())
            .attr("data-name", blockTypeConfig.name.toLowerCase())
            .attr("data-description", blockTypeConfig.description.toLowerCase())
            .attr("data-category", blockTypeConfig.categoryId);

          var imgContainer = $("<button>", {
            class: "mfp-grid-item__button mfp-grid-item__button--default",
          })
            .attr("tabindex", 0)
            .attr("role", "button");

          var img = $("<img>").attr("src", this.defaultImageUrl);

          var content = $("<div>", {
            class: "mfp-grid-item__content",
          });

          var name = $("<h2>", {
            class: "mfp-grid-item__content__name",
            text: blockTypeConfig.name,
          });

          var description = $("<div>", {
            class: "mfp-grid-item__content__description",
          });
          if (blockTypeConfig["image"]) {
            imgContainer.removeClass("mfp-grid-item__button--default");
            img.attr("src", blockTypeConfig["image"]);

            var previewButton = $("<div />", {
              class: "mfp-grid-item__preview expand icon",
            });
            previewButton.on(
              "click",
              function (ev) {
                new Craft.PreviewFileModal(blockTypeConfig.imageId, null, {
                  startingWidth: 2000,
                  startingHeight: 2000,
                });
                ev.preventDefault();
                return false;
              }.bind(this)
            );
            previewButton.appendTo(imgContainer);
          }
          if (blockTypeConfig["name"]) {
            name.text(blockTypeConfig["name"]);
          }
          if (blockTypeConfig["description"]) {
            description.html(blockTypeConfig["descriptionHTML"]);
          }

          img.appendTo(imgContainer);
          name.appendTo(content);
          description.appendTo(content);
          gridItem.prepend(imgContainer, content);

          // Add click handlers
          var onClickHandler = function () {
            this.trigger("gridItemClicked", {
              config: blockTypeConfig,
              insertionIndex: this.insertionIndex
            });
          };
          imgContainer.on("click", onClickHandler.bind(this));
          name.on("click", onClickHandler.bind(this));

          grid.append(gridItem);
        }.bind(this)
      );

      gridContainer.append(grid);
      return gridContainer;
    },

    /**
     * Build model HTML
     *
     */
    buildModalHtml: function () {
      this.$container.addClass("mfp-modal modal elementselectormodal");

      var includeCategories = this.config.categories.length > 0;

      var body = $("<div />", {
        class: "mfp-modal__body body",
      });
      var content = $("<div />", {
        class: "mfp-modal__content content",
      });
      var main = $("<main />", {
        class: "mfp-modal__main main",
      });
      var toolbar = this.buildToolbarHtml();
      var grid = this.buildGridItemsHtml();
      var emptyMessage = this.buildEmptyMessageHtml();
      var footer = this.buildFooterHtml();

      if (includeCategories) {
        var sidebar = this.buildSidebarHtml();
        body.addClass("has-sidebar");
        content.addClass("has-sidebar");
        content.append(sidebar);
      }

      body.append(content);
      main.append(toolbar).append(emptyMessage).append(grid);
      content.append(main);
      this.$container.append(body).append(footer);
    },

    /**
     * Filter
     *
     */
    filter: function () {
      var query = this.query.toLowerCase();
      var category = this.category;

      var hasCategory = this.category !== undefined;
      var hasQuery = query.length > 0;

      var $allGridItems = this.getGridItems();
      var $activeGridItems = $allGridItems
        .hide()
        .filter(function () {
          // First filter by category
          if (hasCategory) {
            return $(this).data("category") === category;
          }
          return true;
        })
        .filter(function () {
          if (!hasQuery) {
            return true;
          } else {
            var blockType = $(this).data("block-type");
            var name = $(this).data("name");
            var description = $(this).data("description");
            return (
              hasQuery &&
              (blockType.includes(query) ||
                name.includes(query) ||
                description.includes(query))
            );
          }
        });

      $allGridItems.hide();
      if ($activeGridItems.length === 0) {
        this.showEmpty();
      } else {
        this.hideEmpty();
        $activeGridItems.show();
      }
    },

    /**
     * 
     * @returns 
     */
    getGridItems: function () {
      return this.$container.find(".mfp-grid-item");
    },

    /**
     * 
     */
    showAll: function () {
      this.getGridItems().show();
    },

    /**
     * 
     */
    showEmpty: function () {
      this.$container.find(".mfp-modal__empty").show().css("display", "flex");
    },

    /**
     * 
     */
    hideEmpty: function () {
      this.$container.find(".mfp-modal__empty").hide();
    },

    /**
     * 
     */
    resetSearch: function () {
      this.$container.find(".mfp-modal__empty").val("");
      this.query = "";
      this.showAll();
      this.hideEmpty();
    },

    /**
     * 
     * @param {*} func 
     * @param {*} wait 
     * @param {*} immediate 
     * @returns 
     */
    debounce: function (func, wait, immediate) {
      var timeout;
      return function () {
        var context = this,
          args = arguments;
        var later = function () {
          timeout = null;
          if (!immediate) func.apply(context, args);
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
      };
    },
  });
})(jQuery);
