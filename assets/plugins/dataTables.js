/* DataTables - Advanced Table Plugin */

(function($) {
  'use strict';
  
  $.fn.DataTable = function(options) {
    var settings = $.extend({
      sorting: true,
      filtering: true,
      pagination: true,
      pageLength: 10,
      columnDefs: [],
      onRowClick: null,
      serverSide: false,
      ajax: null,
      columns: []
    }, options);
    
    return this.each(function() {
      var $table = $(this);
      var tbody = $table.find('tbody');
      var thead = $table.find('thead');
      var rows = tbody.find('tr');
      var currentPage = 1;
      var itemsPerPage = settings.pageLength;
      
      // Add DataTable classes
      $table.addClass('dataTable');
      
      // Create controls container
      var controlsHtml = '<div class="dataTables_controls">';
      
      if(settings.filtering) {
        controlsHtml += '<div class="dataTables_filter">';
        controlsHtml += '<input type="text" id="dataTables_filter_input" placeholder="Search..." class="form-control">';
        controlsHtml += '</div>';
      }
      
      controlsHtml += '</div>';
      $table.before(controlsHtml);
      
      // Filter functionality
      if(settings.filtering) {
        $('#dataTables_filter_input').on('keyup', function() {
          var filterValue = $(this).val().toLowerCase();
          rows.each(function() {
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(filterValue) > -1);
          });
          currentPage = 1;
          updatePagination();
        });
      }
      
      // Sorting functionality
      if(settings.sorting) {
        thead.find('th').each(function(index) {
          var $th = $(this);
          $th.css('cursor', 'pointer');
          $th.on('click', function() {
            sortTable(index);
          });
        });
      }
      
      function sortTable(columnIndex) {
        var isAsc = true;
        var sortRows = rows.get().sort(function(a, b) {
          var aVal = $(a).find('td').eq(columnIndex).text();
          var bVal = $(b).find('td').eq(columnIndex).text();
          
          // Try numeric sort
          var aNum = parseFloat(aVal);
          var bNum = parseFloat(bVal);
          
          if(!isNaN(aNum) && !isNaN(bNum)) {
            return isAsc ? aNum - bNum : bNum - aNum;
          }
          
          // String sort
          return isAsc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
        });
        
        tbody.html('');
        $(sortRows).each(function() {
          tbody.append(this);
        });
      }
      
      // Pagination
      if(settings.pagination) {
        var paginationHtml = '<div class="dataTables_paginate">';
        var totalPages = Math.ceil(rows.length / itemsPerPage);
        
        for(var i = 1; i <= totalPages; i++) {
          var activeClass = i === 1 ? 'active' : '';
          paginationHtml += '<a href="#" class="paginate_button ' + activeClass + '" data-page="' + i + '">' + i + '</a>';
        }
        
        paginationHtml += '</div>';
        $table.after(paginationHtml);
        
        $table.after(paginationHtml);
        
        $('.paginate_button').on('click', function(e) {
          e.preventDefault();
          currentPage = parseInt($(this).data('page'));
          updatePagination();
          $('.paginate_button').removeClass('active');
          $(this).addClass('active');
        });
      }
      
      function updatePagination() {
        var visibleRows = rows.filter(':visible');
        var totalPages = Math.ceil(visibleRows.length / itemsPerPage);
        var start = (currentPage - 1) * itemsPerPage;
        var end = start + itemsPerPage;
        
        visibleRows.hide();
        visibleRows.slice(start, end).show();
      }
      
      // Row click functionality
      if(settings.onRowClick) {
        tbody.on('click', 'tr', function() {
          settings.onRowClick($(this));
        });
      }
      
      // Initialize
      updatePagination();
    });
  };
  
})(jQuery);
