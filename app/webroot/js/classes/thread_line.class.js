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
 * loads a posting inline via ajax and shows it
 */
ThreadLine.prototype.load_inline_view = function (scroll) {
	if (typeof scroll == 'undefined' ) scroll = true;
	var id = this.id;
	var p = this;

	jQuery.ajax(
	{
		beforeSend:function(request) {
			request.setRequestHeader('X-Update', 't_s_' + id );
			threadLines.get(id).set({
				isInlineOpened: true
			});
		},
		complete:function(request, textStatus) {
		// show inline posting
		// @td the scroll from p.showInlineView(scroll);
		},
		success:function(data, textStatus) {
			jQuery( p.id_thread_slider ).html(data);
			postings.add([{
				id: id
			}]);
			new PostingView({
				el: $('.js-entry-view-core[data-id=' + id + ']'),
				model: postings.get(id)
			});

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
};

/**
 * Adds an new thread as answer after the current and fills it with `data`
 */
ThreadLine.prototype.insertNewLineAfter = function (data) {
	threadLines.get(this.id).set({isInlineOpened: false});
	postings.get(this.id).set({isAnsweringFormShown: false});
  var el = $('<li>'+data+'</li>').insertAfter('#ul_thread_' + this.id + ' > li:last-child');

	// add to backbone model
	var threadLineId = $(data).find('.js-thread_line').data('id');
	threadLines.add([{
				id: threadLineId,
				isAlwaysShownInline: User_Settings_user_show_inline
			}]);
	new ThreadLineView({
		el: $(el).find('.js-thread_line'),
		model: threadLines.get(threadLineId)
	});
};