/* Bootstrap JS - Responsive Framework JavaScript */

(function($) {
  'use strict';
  
  // Modal Component
  $.fn.modal = function(option) {
    return this.each(function() {
      var $modal = $(this);
      var data = $modal.data('bs.modal');
      var options = typeof option === 'object' && option;
      
      if(!data) {
        data = {
          isShown: false
        };
        $modal.data('bs.modal', data);
      }
      
      if(option === 'toggle') {
        data.isShown ? close() : show();
      } else if(option === 'show') {
        show();
      } else if(option === 'hide') {
        close();
      }
      
      function show() {
        $modal.css('display', 'block');
        $modal.addClass('in');
        $('body').addClass('modal-open');
        data.isShown = true;
      }
      
      function close() {
        $modal.css('display', 'none');
        $modal.removeClass('in');
        $('body').removeClass('modal-open');
        data.isShown = false;
      }
    });
  };
  
  // Close modal on background click
  $(document).on('click', '.modal.in', function(e) {
    if(e.target === this) {
      $(this).modal('hide');
    }
  });
  
  // Close modal on close button click
  $(document).on('click', '[data-dismiss="modal"]', function() {
    $(this).closest('.modal').modal('hide');
  });
  
  // Dropdown Component
  $.fn.dropdown = function() {
    return this.each(function() {
      var $dropdown = $(this);
      $dropdown.on('click', function(e) {
        e.stopPropagation();
        $(this).siblings('.dropdown-menu').toggleClass('show');
      });
    });
  };
  
  $(document).on('click', function() {
    $('.dropdown-menu.show').removeClass('show');
  });
  
  // Alert Dismissal
  $(document).on('click', '[data-dismiss="alert"]', function() {
    $(this).closest('.alert').fadeOut(function() {
      $(this).remove();
    });
  });
  
  // Collapse Component
  $.fn.collapse = function(option) {
    return this.each(function() {
      var $toggle = $(this);
      var target = $toggle.attr('data-target') || $toggle.attr('href');
      var $target = $(target);
      var data = $target.data('bs.collapse');
      
      if(!data) {
        data = {
          transitioning: false,
          show: true
        };
        $target.data('bs.collapse', data);
      }
      
      if(option === 'toggle' || !option) {
        data.show ? hide() : show();
      }
      
      function show() {
        if(data.transitioning) return;
        
        data.transitioning = true;
        $target.addClass('in').slideDown(function() {
          data.transitioning = false;
          data.show = true;
          $toggle.removeClass('collapsed');
        });
      }
      
      function hide() {
        if(data.transitioning) return;
        
        data.transitioning = true;
        $target.removeClass('in').slideUp(function() {
          data.transitioning = false;
          data.show = false;
          $toggle.addClass('collapsed');
        });
      }
    });
  };
  
  // Tooltip Component
  $.fn.tooltip = function(options) {
    var settings = $.extend({
      placement: 'top',
      title: '',
      trigger: 'hover',
      delay: 0
    }, options);
    
    return this.each(function() {
      var $element = $(this);
      var title = $element.attr('title') || settings.title;
      
      if(settings.trigger === 'hover') {
        $element.on('mouseenter', function() {
          showTooltip(title, $(this), settings.placement);
        });
        
        $element.on('mouseleave', function() {
          $('[data-tooltip="active"]').remove();
        });
      }
    });
    
    function showTooltip(text, $element, placement) {
      var $tooltip = $('<div class="tooltip" data-tooltip="active">' + text + '</div>');
      var offset = $element.offset();
      var top = offset.top;
      var left = offset.left;
      
      $('body').append($tooltip);
      
      switch(placement) {
        case 'top':
          top -= $tooltip.outerHeight() + 10;
          left += ($element.width() - $tooltip.width()) / 2;
          break;
        case 'bottom':
          top += $element.height() + 10;
          left += ($element.width() - $tooltip.width()) / 2;
          break;
        case 'left':
          left -= $tooltip.width() + 10;
          top += ($element.height() - $tooltip.height()) / 2;
          break;
        case 'right':
          left += $element.width() + 10;
          top += ($element.height() - $tooltip.height()) / 2;
          break;
      }
      
      $tooltip.css({ top: top, left: left });
    }
  };
  
  // Popover Component
  $.fn.popover = function(options) {
    var settings = $.extend({
      placement: 'right',
      title: '',
      content: '',
      trigger: 'click'
    }, options);
    
    return this.each(function() {
      var $element = $(this);
      
      if(settings.trigger === 'click') {
        $element.on('click', function(e) {
          e.preventDefault();
          togglePopover($(this), settings);
        });
      }
    });
    
    function togglePopover($element, settings) {
      var $existing = $('[data-popover="active"]');
      if($existing.length) {
        $existing.remove();
        return;
      }
      
      var html = '<div class="popover" data-popover="active">' +
                 '<div class="popover-title">' + settings.title + '</div>' +
                 '<div class="popover-content">' + settings.content + '</div>' +
                 '</div>';
      
      var $popover = $(html);
      $('body').append($popover);
      
      var offset = $element.offset();
      var top = offset.top;
      var left = offset.left;
      
      if(settings.placement === 'right') {
        left += $element.width() + 10;
      }
      
      $popover.css({ top: top, left: left });
    }
  };
  
  // Tab Component
  $.fn.tab = function(option) {
    return this.each(function() {
      var $tab = $(this);
      
      if(option === 'show') {
        var target = $tab.attr('data-target') || $tab.attr('href');
        var $target = $(target);
        
        $tab.closest('ul').find('li').removeClass('active');
        $tab.closest('li').addClass('active');
        
        $target.closest('.tab-content').find('.tab-pane').removeClass('active');
        $target.addClass('active');
      }
    });
  };
  
  $(document).on('click', '[role="tab"]', function() {
    $(this).tab('show');
  });
  
  // Carousel Component
  $.fn.carousel = function(option) {
    var settings = $.extend({
      interval: 5000,
      pause: 'hover',
      wrap: true
    }, option);
    
    return this.each(function() {
      var $carousel = $(this);
      var $items = $carousel.find('.carousel-inner .item');
      var currentIndex = 0;
      var interval;
      
      function showSlide(index) {
        $items.removeClass('active');
        $items.eq(index).addClass('active');
        currentIndex = index;
      }
      
      function nextSlide() {
        var nextIndex = (currentIndex + 1) % $items.length;
        showSlide(nextIndex);
      }
      
      function prevSlide() {
        var prevIndex = (currentIndex - 1 + $items.length) % $items.length;
        showSlide(prevIndex);
      }
      
      function startAutoPlay() {
        interval = setInterval(nextSlide, settings.interval);
      }
      
      function stopAutoPlay() {
        clearInterval(interval);
      }
      
      // Auto-play
      startAutoPlay();
      
      // Pause on hover
      if(settings.pause === 'hover') {
        $carousel.on('mouseenter', stopAutoPlay);
        $carousel.on('mouseleave', startAutoPlay);
      }
      
      // Controls
      $carousel.find('.carousel-control.prev').on('click', prevSlide);
      $carousel.find('.carousel-control.next').on('click', nextSlide);
    });
  };
  
})(jQuery);
