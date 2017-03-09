Drupal.TBMegaMenu = Drupal.TBMegaMenu || {};

(function ($, Drupal, drupalSettings) {
  "use strict";
  
  Drupal.behaviors.tbMegaMenuBackendAction = {
    attach: function (context) {
      $('select[name="tb-megamenu-animation"]').change(function() {
        $('#tb-megamenu-duration-wrapper').css({'display': ($(this).val() == 'none' ? 'none' : 'inline-block')});
        $('#tb-megamenu-delay-wrapper').css({'display': ($(this).val() == 'none' ? 'none' : 'inline-block')});
      });
      
      $(".tb-megamenu-column-inner .close").click(function() {
        $(this).parent().html("");
      });
      
      $("#tb-megamenu-admin select").chosen({
        disable_search_threshold : 15,
        allow_single_deselect: true
      });
      
      /* Init TB Mega Menu. */
      if (drupalSettings.TBMegaMenu.menu_name !== undefined) {
        $("#tb-megamenu-admin-mm-container").megamenuAdmin({menu_name: drupalSettings.TBMegaMenu.menu_name});
      }
    }
  };
})(jQuery, Drupal, drupalSettings);

