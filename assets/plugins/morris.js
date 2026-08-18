/* Morris.js - Charts Library */

(function($) {
  'use strict';
  
  window.Morris = {
    
    // Line Chart
    Line: function(options) {
      var settings = $.extend({
        element: null,
        data: [],
        xkey: 'x',
        ykeys: ['y'],
        labels: ['Value'],
        lineColors: ['#0b62a4'],
        pointSize: 4,
        lineWidth: 2,
        resize: true
      }, options);
      
      var $container = $(settings.element);
      var width = $container.width();
      var height = $container.height() || 300;
      
      var svg = createSVG(width, height);
      $container.html(svg);
      
      drawLineChart(svg, settings);
      
      if(settings.resize) {
        $(window).resize(function() {
          var newWidth = $container.width();
          $container.html('');
          var newSvg = createSVG(newWidth, height);
          $container.html(newSvg);
          drawLineChart(newSvg, settings);
        });
      }
      
      return this;
    },
    
    // Bar Chart
    Bar: function(options) {
      var settings = $.extend({
        element: null,
        data: [],
        xkey: 'x',
        ykeys: ['y'],
        labels: ['Value'],
        barColors: ['#0b62a4'],
        resize: true
      }, options);
      
      var $container = $(settings.element);
      var width = $container.width();
      var height = $container.height() || 300;
      
      var svg = createSVG(width, height);
      $container.html(svg);
      
      drawBarChart(svg, settings);
      
      if(settings.resize) {
        $(window).resize(function() {
          var newWidth = $container.width();
          $container.html('');
          var newSvg = createSVG(newWidth, height);
          $container.html(newSvg);
          drawBarChart(newSvg, settings);
        });
      }
      
      return this;
    },
    
    // Donut Chart
    Donut: function(options) {
      var settings = $.extend({
        element: null,
        data: [],
        colors: ['#0b62a4', '#7cb5ec', '#f7a35c', '#e63d6f'],
        resize: true
      }, options);
      
      var $container = $(settings.element);
      var width = $container.width();
      var height = $container.height() || 300;
      
      var svg = createSVG(width, height);
      $container.html(svg);
      
      drawDonutChart(svg, settings);
      
      return this;
    }
  };
  
  function createSVG(width, height) {
    return '<svg class="morris-chart" width="' + width + '" height="' + height + '"></svg>';
  }
  
  function drawLineChart(svg, settings) {
    var $svg = $(svg);
    var data = settings.data;
    var padding = 40;
    var chartWidth = $svg.width() - (padding * 2);
    var chartHeight = $svg.height() - (padding * 2);
    
    if(data.length > 0) {
      var maxY = Math.max.apply(Math, data.map(function(d) {
        return Math.max.apply(Math, settings.ykeys.map(function(k) { return d[k]; }));
      }));
      
      var xStep = chartWidth / (data.length - 1 || 1);
      var yScale = chartHeight / maxY;
      
      // Draw grid lines
      for(var i = 0; i <= 4; i++) {
        var y = padding + (chartHeight - (chartHeight / 4) * i);
        $svg.append('<line x1="' + padding + '" y1="' + y + '" x2="' + ($svg.width() - padding) + '" y2="' + y + '" stroke="#ccc" stroke-dasharray="5,5"/>');
      }
      
      // Draw line
      var points = [];
      data.forEach(function(item, index) {
        var x = padding + (xStep * index);
        var y = padding + chartHeight - (item[settings.ykeys[0]] * yScale);
        points.push(x + ',' + y);
      });
      
      if(points.length > 0) {
        $svg.append('<polyline points="' + points.join(' ') + '" fill="none" stroke="' + settings.lineColors[0] + '" stroke-width="' + settings.lineWidth + '"/>');
      }
    }
  }
  
  function drawBarChart(svg, settings) {
    var $svg = $(svg);
    var data = settings.data;
    var padding = 40;
    var chartWidth = $svg.width() - (padding * 2);
    var chartHeight = $svg.height() - (padding * 2);
    
    if(data.length > 0) {
      var maxY = Math.max.apply(Math, data.map(function(d) { return d[settings.ykeys[0]]; }));
      var barWidth = (chartWidth / data.length) * 0.8;
      var barSpacing = (chartWidth / data.length) * 0.2;
      var yScale = chartHeight / maxY;
      
      data.forEach(function(item, index) {
        var x = padding + (index * (barWidth + barSpacing)) + (barSpacing / 2);
        var barHeight = item[settings.ykeys[0]] * yScale;
        var y = padding + chartHeight - barHeight;
        
        $svg.append('<rect x="' + x + '" y="' + y + '" width="' + barWidth + '" height="' + barHeight + '" fill="' + settings.barColors[0] + '" opacity="0.8"/>');
      });
    }
  }
  
  function drawDonutChart(svg, settings) {
    var $svg = $(svg);
    var data = settings.data;
    var total = data.reduce(function(sum, item) { return sum + item.value; }, 0);
    
    var centerX = $svg.width() / 2;
    var centerY = $svg.height() / 2;
    var radius = Math.min(centerX, centerY) - 20;
    var innerRadius = radius * 0.6;
    
    var currentAngle = -Math.PI / 2;
    
    data.forEach(function(item, index) {
      var sliceAngle = (item.value / total) * Math.PI * 2;
      var endAngle = currentAngle + sliceAngle;
      
      var startX = centerX + Math.cos(currentAngle) * radius;
      var startY = centerY + Math.sin(currentAngle) * radius;
      var endX = centerX + Math.cos(endAngle) * radius;
      var endY = centerY + Math.sin(endAngle) * radius;
      
      var largeArc = sliceAngle > Math.PI ? 1 : 0;
      
      var path = 'M ' + centerX + ' ' + centerY +
                 ' L ' + startX + ' ' + startY +
                 ' A ' + radius + ' ' + radius + ' 0 ' + largeArc + ' 1 ' + endX + ' ' + endY +
                 ' Z';
      
      $svg.append('<path d="' + path + '" fill="' + settings.colors[index] + '" opacity="0.8"/>');
      
      currentAngle = endAngle;
    });
  }
  
})(jQuery);
