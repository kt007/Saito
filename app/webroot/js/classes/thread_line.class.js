/**
 * Class ThreadLine
 *
 * A single thread/line in a thread tree
 */
function ThreadLine(id) {
  this.id = id;

  this.id_thread_line			= '.thread_line.' + id;
  this.id_thread_inline		= '.thread_inline.' 	+ id;
  this.id_thread_slider		=	'#t_s_' + id;
  this.id_bottom					= '#posting_formular_slider_bottom_' + id;
};

/**
 * if the line is not in the browser windows at the moment
 * scroll to that line and highlight it
 */
ThreadLine.prototype.scrollLineIntoView = function () {
  var p = this;
  if (!_isScrolledIntoView(this.id_thread_line)) {
    $(window).scrollTo(
      this.id_thread_line,
      400,
      {
        'offset': -40,
        easing: 'swing',
        onAfter: function() {
          $(p.id_thread_line).effect(
            "highlight",
            {
              times: 1
            },
            3000);
        } //end onAfter
      }
      );
  }
};

/**
 * shows and hides the element that contains an inline posting
 */
ThreadLine.prototype.toggle_inline_view = function (scroll) {
  if (typeof scroll == 'undefined' ) scroll = true;
  var id = this.id;
  var p  = this;
  if ($(p.id_thread_inline).css('display') != 'none') {
    // hide inline posting
		threadLines.get(id).set({isInlineOpened: false});
  }
  else {
    // show inline posting
		threadLines.get(id).set({isInlineOpened: true});
  }
};

/**
 * loads a posting inline via ajax and shows it
 */
ThreadLine.prototype.load_inline_view = function (scroll) {
  if (typeof scroll == 'undefined' ) scroll = true;
  var id = this.id;
  var p = this;

  if ($(p.id_thread_inline).length === 0) {
    var spinner = '<div class="thread_inline '+id+'"> <div data-id="'+id+'" class="btn-strip btn-strip-top pointer">&nbsp;</div><div id="t_s_'+id+'" class="t_s"><div class="spinner"></div></div> </div>';
    $(p.id_thread_line).after(spinner);
    jQuery.ajax(
    {
      beforeSend:function(request) {
        request.setRequestHeader('X-Update', 't_s_' + id );
        p.toggle_inline_view(scroll);
      },
      complete:function(request, textStatus) {
        // show inline posting
        // @td the scroll from p.showInlineView(scroll);
				threadLines.get(id).set({isInlineOpened: true});
      },
      success:function(data, textStatus) {
        jQuery( p.id_thread_slider ).html(data);
				postings.add([{ id: id }]);
				new PostingView({ el: $('.js-entry-view-core[data-id=' + id + ']'), model: postings.get(id) });

        initViewPosting(id);
				/*
				var here = document.URL;
				history.replaceState(null, '', $(p.id_thread_line).find('a.thread_line-content').attr('href'));
				history.replaceState(null, '', here);
				*/
      },
      async:true,
      type:'post',
      url: webroot + 'entries/view/'  + id
    }
    );
  }
  else {
    p.toggle_inline_view(scroll);
  }
};

/**
 * Adds an new thread as answer after the current and fills it with `data`
 */
ThreadLine.prototype.insertNewLineAfter = function (data) {
  this.toggle_inline_view();
  $('<li>'+data+'</li>').insertAfter('#ul_thread_' + this.id + ' > li:last-child');

	// add to backbone model
	var threadLineId = $(data).find('.thread_line').data('id');
	threadLines.add([{
				id: threadLineId,
				isAlwaysShownInline: User_Settings_user_show_inline
			}]);
	new ThreadLineView({
		el: $('.thread_line.' + threadLineId),
		model: threadLines.get(threadLineId),
		isAlwaysShownInline: User_Settings_user_show_inline
	});
};