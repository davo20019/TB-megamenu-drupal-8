(function ($, Drupal, drupalSettings) {
  "use strict";

  Drupal.behaviors.disableClicks = {
    attach: function (context, settings) {
      var menu = $(context).find('.tb-megamenu-item.mega.disable > a').once('disable-clicks');
      
      $(menu).click(function (event) {
          event.preventDefault();
      });

    }
  };
})(jQuery, Drupal, drupalSettings);