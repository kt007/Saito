$(document).ready(function() 
    { 
        $("table").tablesorter({sortList: [[2,1], [1,1]]})
          .addClass('tablesorter table table-striped');
    } 
);

$(window).load($(function()
{
    var elem = $("#sidebar");
    var top = elem.offset().top-20;
    var maxTop = $("footer").offset().top - elem.height();
    var scrollHandler = function()
    {
      var scrollTop = $(window).scrollTop();
      if (scrollTop<top) {
        elem.css({position:"relative",top:""})//should be "static" I think
        $('section').css({marginLeft:"0px"});
//      } else if (scrollTop>maxTop) {
//        elem.css({position:"absolute",top:(maxTop+"px")})
      } else {
        elem.css({position:"fixed",top:"0px"});
        $('section').css({marginLeft:"320px", width: "568px"});
      }
    }
    $(window).scroll(scrollHandler);scrollHandler();

}));
    
