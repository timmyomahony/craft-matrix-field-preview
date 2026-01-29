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
    // Used for Matrix only: when inserting a block "above" we need to track positioning
    targetEntry: undefined,
    // Track focused grid item index for keyboard navigation
    focusedGridItemIndex: -1,

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

      // Setup keyboard navigation
      this.setupKeyboardNavigation();
    },

    /**
     * Setup keyboard navigation for grid items
     */
    setupKeyboardNavigation: function () {
      // Handle movement within the grid items when using arrow keys
      this.$container.on("keydown", ".mfp-grid", this.handleArrowPress.bind(this));

      // Handle switch from search input to first grid item via "tab" or "down" arrow
      this.$container.on("keydown", ".mfp-modal__toolbar__search__input", function (ev) {
        if ((ev.key === "Tab" || ev.key === "ArrowDown") && !ev.shiftKey) {
          var $visibleItems = this.getVisibleGridItems();
          if ($visibleItems.length > 0) {
            ev.preventDefault();
            this.focusGridItem(0);
          }
        }
      }.bind(this));

      // Handle switch from sidebar to first grid item via "right" arrow
      this.$container.on("keydown", ".mfp-modal__sidebar", function (ev) {
        console.log('test');
        if (ev.key === "ArrowRight" && !ev.shiftKey) {
          var $visibleItems = this.getVisibleGridItems();
          if ($visibleItems.length > 0) {
            ev.preventDefault();
            this.focusGridItem(0);
          }
        }
      }.bind(this));
    },

    /**
     * Handle keydown events for grid navigation
     * @param {Event} ev
     */
    handleArrowPress: function (ev) {
      // Only handle navigation when a grid item or its child is focused
      var $focused = $(document.activeElement);
      var $gridItem = $focused.closest(".mfp-grid-item");

      if ($gridItem.length === 0) {
        return;
      }

      var $visibleItems = this.getVisibleGridItems();
      var currentIndex = $visibleItems.index($gridItem);
      var columnsPerRow = this.getColumnsPerRow();
      var newIndex = currentIndex;

      switch (ev.key) {
        case "ArrowRight":
          newIndex = Math.min(currentIndex + 1, $visibleItems.length - 1);
          ev.preventDefault();
          break;
        case "ArrowLeft":
          newIndex = Math.max(currentIndex - 1, 0);
          ev.preventDefault();
          break;
        case "ArrowDown":
          newIndex = Math.min(currentIndex + columnsPerRow, $visibleItems.length - 1);
          ev.preventDefault();
          break;
        case "ArrowUp":
          newIndex = Math.max(currentIndex - columnsPerRow, 0);
          ev.preventDefault();
          break;
        case "Enter":
        case " ":
          // Trigger click on the grid item button
          $gridItem.find(".mfp-grid-item__button").first().trigger("click");
          ev.preventDefault();
          return;
        case "Tab":
          if (ev.shiftKey && currentIndex === 0) {
            // Shift+Tab from first item goes back to search
            ev.preventDefault();
            this.$container.find(".mfp-modal__toolbar__search__input").focus();
            this.focusedGridItemIndex = -1;
            return;
          }
          break;
        default:
          return;
      }

      if (newIndex !== currentIndex) {
        this.focusGridItem(newIndex);
      }
    },

    /**
     * Get the number of columns per row based on grid layout
     * @returns {number}
     */
    getColumnsPerRow: function () {
      var $grid = this.$container.find(".mfp-grid");
      if ($grid.length === 0) {
        return 1;
      }
      var gridWidth = $grid.width();
      var $firstItem = this.getVisibleGridItems().first();
      if ($firstItem.length === 0) {
        return 1;
      }
      var itemWidth = $firstItem.outerWidth(true);
      return Math.max(1, Math.floor(gridWidth / itemWidth));
    },

    /**
     * Get visible grid items (not hidden by filter)
     * @returns {jQuery}
     */
    getVisibleGridItems: function () {
      return this.$container.find(".mfp-grid-item:visible");
    },

    /**
     * Focus a grid item by index
     * @param {number} index
     */
    focusGridItem: function (index) {
      var $visibleItems = this.getVisibleGridItems();
      if (index >= 0 && index < $visibleItems.length) {
        this.focusedGridItemIndex = index;
        var $item = $visibleItems.eq(index);
        $item.find(".mfp-grid-item__button").first().focus();
      }
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

      // Keep track of the block types that have a category assigned
      // so that we can only show the related categories
      var activeCategories = Object.values(this.config.blockTypes).filter(function (blockType) {
        return blockType.categoryId !== null;
      }).map(function (blockType) {
        return blockType.categoryId;
      });

      // Add link for each category
      $.each(
        this.config["categories"],
        function (i, category) {
          // Only show the category if it has a block type assigned
          if (!activeCategories.includes(category.id)) {
            return;
          }

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
        Object.values(this.config.blockTypes),
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
              insertionIndex: this.insertionIndex,
              targetEntry: this.targetEntry
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

      // Only show categories sidebar if the user has configured categories
      // and if at least one block type has a category assigned
      var includeCategories = this.config.categories.length > 0 && Object.values(this.config.blockTypes).some(function (blockType) {
        return blockType.categoryId !== null;
      });

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

      // Reset focused index when filter changes
      this.focusedGridItemIndex = -1;
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
