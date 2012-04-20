$(document).ready(function() { 
  $("table").tablesorter({
    sortList: [[2,1], [1,1]],
    textExtraction: function(node) { 
      // extract data from markup and return it  
      console.log(node.innerHTML.replace(/[^\d]/g, '')); 
      return node.innerHTML.replace(/€/g, ''); 
    }
  })
    .addClass('tablesorter table table-striped');
});