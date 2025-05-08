var MFP = MFP || {};

(function ($) {
  MFP.BlockTypeModalButton = Garnish.Base.extend({

    /**
     * 
     * @param {*} target 
     * @param {*} settings 
     * @param {*} iconUrl 
     */
    init: function (target, settings, iconUrl) {
      this.settings = $.extend(
        {
          takeover: false,
          buttonLabel: "",
          buttonIcon: "",
          extraClasses: "",
        },
        settings
      );

      this.$target = $(target);

      var classes = "mfp-modal-button btn flex flex-nowrap gap-xs";
      var $label = $("<span>");
      var $icon = $("<span>", {
        class: "mfp-modal-button__icon cp-icon small",
      });

      var buttonLabel = this.settings.buttonLabel || Craft.t('matrix-field-preview', 'New Entry');
      var buttonIcon = this.settings.buttonIcon;

      // Set an optional button icon
      if (buttonIcon) {
        $icon.append(buttonIcon);
        this.$target.append($icon);
      }

      if (!this.settings.takeover) {
        classes += " mfp-modal-button--secondary dashed";
      } else {
        classes += " mfp-modal-button--primary dashed";
      }

      $label.text(buttonLabel);
      this.$target.append($label);
      this.$target.addClass(classes);

      this.$target.on(
        "click",
        function () {
          if (!this.$target.attr("disabled")) {
            this.trigger("click");
          }
        }.bind(this)
      );
    },
    
    /**
     * 
     */
    disable: function () {
      this.$target.addClass("disabled");
      this.$target.attr("disabled", true);
    },

    /**
     * 
     */
    enable: function () {
      this.$target.removeClass("disabled");
      this.$target.attr("disabled", false);
    },
  });
})(jQuery);
