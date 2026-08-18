/* Pace.js - Automatic Page Load Progress Bar */

(function() {
  'use strict';
  
  var Pace = {
    running: false,
    progress: 0,
    settings: {
      maxProgressPercentage: 90,
      initialRate: 0.08,
      minTime: 500,
      ghostTime: 100,
      maxTime: 5000
    },
    
    init: function() {
      this.createProgressBar();
      this.attachListeners();
      this.startProgress();
    },
    
    createProgressBar: function() {
      var barStyle = document.createElement('style');
      barStyle.textContent = `
        .pace {
          position: fixed;
          top: 0;
          left: 0;
          height: 3px;
          width: 100%;
          z-index: 9999;
          background: #007bff;
          box-shadow: 0 0 10px #007bff;
        }
        .pace.hide {
          display: none;
        }
      `;
      document.head.appendChild(barStyle);
      
      var paceBar = document.createElement('div');
      paceBar.className = 'pace hide';
      document.body.appendChild(paceBar);
      this.bar = paceBar;
    },
    
    attachListeners: function() {
      var self = this;
      
      // Listen to AJAX requests
      if(typeof jQuery !== 'undefined') {
        jQuery(document).on('ajaxStart', function() {
          self.startProgress();
        });
        jQuery(document).on('ajaxStop', function() {
          self.done();
        });
      }
      
      // Listen to page load
      window.addEventListener('load', function() {
        self.done();
      });
      
      // Listen to page visibility
      document.addEventListener('visibilitychange', function() {
        if(document.hidden) {
          self.pause();
        } else {
          self.resume();
        }
      });
    },
    
    startProgress: function() {
      if(this.running) return;
      this.running = true;
      this.progress = 0;
      this.bar.classList.remove('hide');
      this.animate();
    },
    
    animate: function() {
      var self = this;
      if(!this.running) return;
      
      this.progress += Math.random() * this.settings.initialRate;
      
      if(this.progress > this.settings.maxProgressPercentage) {
        this.progress = this.settings.maxProgressPercentage;
      }
      
      this.updateBar();
      
      setTimeout(function() {
        self.animate();
      }, this.settings.ghostTime);
    },
    
    updateBar: function() {
      var width = (this.progress * 100) + '%';
      this.bar.style.width = width;
    },
    
    done: function(force) {
      if(!this.running && !force) return;
      
      this.progress = 100;
      this.updateBar();
      this.running = false;
      
      var self = this;
      setTimeout(function() {
        self.bar.classList.add('hide');
        self.bar.style.width = '0%';
      }, 300);
    },
    
    pause: function() {
      this.running = false;
    },
    
    resume: function() {
      this.startProgress();
    }
  };
  
  // Initialize when DOM is ready
  if(document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      Pace.init();
    });
  } else {
    Pace.init();
  }
  
  window.Pace = Pace;
})();
