/**
 * @file
 */

(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.mapUrl = {
    attach: function (context, settings) {
      // Remove link only to father.
      $('.cd-accordion-menu', context).on('click', '.has-children > a', function (event) {
        event.preventDefault();
        $(this).parent().find('input[type="checkbox"]').trigger('click');
      });

      var accordionsMenu = $('.cd-accordion-menu', context);
      if ( accordionsMenu.length > 0 ) {
        accordionsMenu.each(function(){
          var accordion = $(this);
          //detect change in the input[type="checkbox"] value
          accordion.on('change', 'input[type="checkbox"]', function(){
            var checkbox = $(this);
            if (checkbox.prop('checked')) {
              checkbox.siblings('ul').attr('style', 'display:none;').slideDown(300);
              accordion.find('.arrow-left').hide();
              accordion.find('.arrow-down').show();
            }
            else {
              checkbox.siblings('ul').attr('style', 'display:block;').slideUp(300);
              accordion.find('.arrow-left').show();
              accordion.find('.arrow-down').hide();
            }
          });
        });
      }
    }
  }
})(jQuery, Drupal, drupalSettings);