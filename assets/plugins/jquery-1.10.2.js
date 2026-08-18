/* jQuery v1.10.2 - JavaScript Library */

(function(global) {
  'use strict';
  
  var jQuery = function(selector, context) {
    return new jQuery.fn.init(selector, context);
  };
  
  jQuery.fn = jQuery.prototype = {
    constructor: jQuery,
    
    init: function(selector, context) {
      context = context || document;
      
      if(!selector) return this;
      
      // String selector
      if(typeof selector === 'string') {
        var elements = context.querySelectorAll(selector);
        for(var i = 0; i < elements.length; i++) {
          this[i] = elements[i];
        }
        this.length = elements.length;
      } 
      // Single element
      else if(selector.nodeType) {
        this[0] = selector;
        this.length = 1;
      }
      // Already a jQuery object
      else if(selector.length) {
        for(var i = 0; i < selector.length; i++) {
          this[i] = selector[i];
        }
        this.length = selector.length;
      }
      
      return this;
    },
    
    // DOM Manipulation
    html: function(content) {
      if(content !== undefined) {
        return this.each(function() {
          this.innerHTML = content;
        });
      }
      return this.length > 0 ? this[0].innerHTML : '';
    },
    
    text: function(content) {
      if(content !== undefined) {
        return this.each(function() {
          this.textContent = content;
        });
      }
      return this.length > 0 ? this[0].textContent : '';
    },
    
    append: function(content) {
      return this.each(function() {
        if(typeof content === 'string') {
          this.innerHTML += content;
        } else {
          this.appendChild(content);
        }
      });
    },
    
    prepend: function(content) {
      return this.each(function() {
        if(typeof content === 'string') {
          this.innerHTML = content + this.innerHTML;
        } else {
          this.insertBefore(content, this.firstChild);
        }
      });
    },
    
    remove: function() {
      return this.each(function() {
        this.parentNode.removeChild(this);
      });
    },
    
    addClass: function(className) {
      return this.each(function() {
        var classes = className.split(' ');
        for(var i = 0; i < classes.length; i++) {
          this.classList.add(classes[i]);
        }
      });
    },
    
    removeClass: function(className) {
      return this.each(function() {
        var classes = className.split(' ');
        for(var i = 0; i < classes.length; i++) {
          this.classList.remove(classes[i]);
        }
      });
    },
    
    hasClass: function(className) {
      return this.length > 0 ? this[0].classList.contains(className) : false;
    },
    
    toggleClass: function(className) {
      return this.each(function() {
        this.classList.toggle(className);
      });
    },
    
    // Attributes
    attr: function(name, value) {
      if(value !== undefined) {
        return this.each(function() {
          this.setAttribute(name, value);
        });
      }
      return this.length > 0 ? this[0].getAttribute(name) : null;
    },
    
    data: function(key, value) {
      if(value !== undefined) {
        return this.each(function() {
          this.dataset[key] = value;
        });
      }
      return this.length > 0 ? this[0].dataset[key] : null;
    },
    
    val: function(value) {
      if(value !== undefined) {
        return this.each(function() {
          this.value = value;
        });
      }
      return this.length > 0 ? this[0].value : '';
    },
    
    // CSS
    css: function(prop, value) {
      if(typeof prop === 'object') {
        return this.each(function() {
          for(var p in prop) {
            this.style[p] = prop[p];
          }
        });
      } else if(value !== undefined) {
        return this.each(function() {
          this.style[prop] = value;
        });
      }
      return this.length > 0 ? window.getComputedStyle(this[0])[prop] : null;
    },
    
    // Dimensions
    width: function(value) {
      if(value !== undefined) {
        return this.each(function() {
          this.style.width = (typeof value === 'number' ? value + 'px' : value);
        });
      }
      return this.length > 0 ? this[0].offsetWidth : 0;
    },
    
    height: function(value) {
      if(value !== undefined) {
        return this.each(function() {
          this.style.height = (typeof value === 'number' ? value + 'px' : value);
        });
      }
      return this.length > 0 ? this[0].offsetHeight : 0;
    },
    
    // Traversing
    find: function(selector) {
      var result = jQuery();
      this.each(function() {
        var elements = this.querySelectorAll(selector);
        for(var i = 0; i < elements.length; i++) {
          result[result.length++] = elements[i];
        }
      });
      return result;
    },
    
    parent: function() {
      return jQuery(this.length > 0 ? this[0].parentNode : null);
    },
    
    children: function(selector) {
      var result = jQuery();
      this.each(function() {
        var children = this.children;
        for(var i = 0; i < children.length; i++) {
          if(!selector || children[i].matches(selector)) {
            result[result.length++] = children[i];
          }
        }
      });
      return result;
    },
    
    siblings: function(selector) {
      var result = jQuery();
      this.each(function() {
        var siblings = this.parentNode.children;
        for(var i = 0; i < siblings.length; i++) {
          if(siblings[i] !== this && (!selector || siblings[i].matches(selector))) {
            result[result.length++] = siblings[i];
          }
        }
      });
      return result;
    },
    
    closest: function(selector) {
      if(this.length === 0) return jQuery();
      
      var element = this[0];
      while(element && !element.matches(selector)) {
        element = element.parentElement;
      }
      return jQuery(element);
    },
    
    // Events
    on: function(event, handler) {
      return this.each(function() {
        this.addEventListener(event, handler);
      });
    },
    
    off: function(event, handler) {
      return this.each(function() {
        this.removeEventListener(event, handler);
      });
    },
    
    click: function(handler) {
      if(handler) {
        return this.on('click', handler);
      }
      if(this.length > 0) {
        this[0].click();
      }
      return this;
    },
    
    // Iteration
    each: function(callback) {
      for(var i = 0; i < this.length; i++) {
        callback.call(this[i], i, this[i]);
      }
      return this;
    },
    
    // Utilities
    filter: function(selector) {
      var result = jQuery();
      this.each(function() {
        if(this.matches(selector)) {
          result[result.length++] = this;
        }
      });
      return result;
    },
    
    first: function() {
      return jQuery(this.length > 0 ? this[0] : null);
    },
    
    last: function() {
      return jQuery(this.length > 0 ? this[this.length - 1] : null);
    },
    
    eq: function(index) {
      return jQuery(this.length > index ? this[index] : null);
    },
    
    get: function(index) {
      if(index === undefined) {
        return Array.prototype.slice.call(this);
      }
      return this[index];
    },
    
    show: function() {
      return this.each(function() {
        this.style.display = '';
      });
    },
    
    hide: function() {
      return this.each(function() {
        this.style.display = 'none';
      });
    },
    
    toggle: function() {
      return this.each(function() {
        this.style.display = this.style.display === 'none' ? '' : 'none';
      });
    },
    
    before: function(content) {
      return this.each(function() {
        this.insertAdjacentHTML('beforebegin', content);
      });
    },
    
    after: function(content) {
      return this.each(function() {
        this.insertAdjacentHTML('afterend', content);
      });
    }
  };
  
  jQuery.fn.init.prototype = jQuery.fn;
  
  // Utility methods
  jQuery.extend = jQuery.fn.extend = function(obj) {
    for(var prop in obj) {
      this[prop] = obj[prop];
    }
    return this;
  };
  
  jQuery.ajax = function(options) {
    var xhr = new XMLHttpRequest();
    var method = (options.type || 'GET').toUpperCase();
    
    xhr.open(method, options.url, true);
    
    xhr.onload = function() {
      if(xhr.status >= 200 && xhr.status < 300) {
        if(options.success) options.success(xhr.responseText);
      } else {
        if(options.error) options.error(xhr);
      }
    };
    
    xhr.onerror = function() {
      if(options.error) options.error(xhr);
    };
    
    xhr.send(options.data || null);
  };
  
  // Global jQuery
  global.jQuery = global.$ = jQuery;
  
})(window);
