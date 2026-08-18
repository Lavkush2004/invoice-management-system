/* MetisMenu - Collapsible Menu Plugin */

(function($) {
  'use strict';
  
  $.fn.metisMenu = function(options) {
    var settings = $.extend({
      toggle: true,
      activeClass: 'active',
      collapseClass: 'collapse',
      collapseInClass: 'in',
      collapsingClass: 'collapsing',
      onActivated: null,
      onDeactivated: null
    }, options);
    
    return this.each(function() {
      var $menu = $(this);
      
      // Add initial classes
      $menu.find('ul').addClass(settings.collapseClass);
      $menu.find('.has-arrow').parent().addClass('has-submenu');
      
      // Handle menu item clicks
      $menu.on('click', 'a.has-arrow', function(e) {
        e.preventDefault();
        var $link = $(this);
        var $submenu = $link.siblings('ul');
        var $parent = $link.closest('li');
        
        if($submenu.length) {
          if(settings.toggle) {
            // Close other open menus at same level
            $parent.siblings('li').find('ul.' + settings.collapseInClass).each(function() {
              collapseMenu($(this));
            });
            $parent.siblings('li').removeClass(settings.activeClass);
          }
          
          // Toggle current menu
          if($submenu.hasClass(settings.collapseInClass)) {
            collapseMenu($submenu);
            $parent.removeClass(settings.activeClass);
            if(settings.onDeactivated) {
              settings.onDeactivated($link);
            }
          } else {
            expandMenu($submenu);
            $parent.addClass(settings.activeClass);
            if(settings.onActivated) {
              settings.onActivated($link);
            }
          }
        }
      });
      
      function expandMenu($menu) {
        $menu.addClass(settings.collapsingClass);
        $menu.height(0);
        
        setTimeout(function() {
          $menu.addClass(settings.collapseInClass);
          var fullHeight = 0;
          $menu.children('li').each(function() {
            fullHeight += $(this).outerHeight();
          });
          $menu.height(fullHeight);
          
          setTimeout(function() {
            $menu.removeClass(settings.collapsingClass);
            $menu.height('auto');
          }, 350);
        }, 0);
      }
      
      function collapseMenu($menu) {
        $menu.addClass(settings.collapsingClass);
        $menu.height($menu.height());
        
        setTimeout(function() {
          $menu.removeClass(settings.collapseInClass);
          $menu.height(0);
          
          setTimeout(function() {
            $menu.removeClass(settings.collapsingClass);
          }, 350);
        }, 0);
      }
      
      // Highlight active menu item
      $menu.on('click', 'a:not(.has-arrow)', function() {
        $menu.find('a').removeClass(settings.activeClass);
        $(this).addClass(settings.activeClass);
        $(this).closest('li').addClass(settings.activeClass);
        $(this).closest('li').parents('li').addClass(settings.activeClass);
      });
    });
  };
  
})(jQuery);
