/**
 * Class Thread
 *
 * A complete thread tree
 */
function Thread(tid) {
	this.tid = tid;

	this.btns_show_new 	= '.btn_show_thread_tid.' + this.tid + '.new';
	this.btns_show 			= '.btn_show_thread_tid.' + this.tid ;
	this.toolbox 				= '.thread_tools.' + this.tid ;
	this.threadbox 			= '.thread_box.' + this.tid ;
};

/**
 * Toggles all threads marked as unread/new in a thread tree
 */
Thread.prototype.showNew = function () {
		var p = this;
		var new_postings = $(p.threadbox + ' .thread_line.new')

		new_postings.each(
			function () {
				var id = $(this).data('id');
				threadLines.get(id).set({
						isInlineOpened: true
					});
			}
		);
};

/**
 * Opens all threads
 */
Thread.prototype.showAll = function () {
		var p = this;
		var closed_postings = $(p.threadbox + ' .thread_line:visible');

		closed_postings.each(
			function () {
				var id = $(this).data('id');
				threadLines.get(id).set({
						isInlineOpened: true
					});
			}
		);
};

/**
 * Closes all threads
 */
Thread.prototype.closeAll = function () {
		var p = this;
		var open_postings = $(p.threadbox + ' .thread_line:hidden');

		open_postings.each(
			function () {
				var id = $(this).data('id');
				threadLines.get(id).set({
						isInlineOpened: false
					});
			}
		);
};

Thread.init = function() {
	// highlight for Toolbar
	Thread.initHighlightTools();
};

Thread.initHighlightTools =  function () {
		$('.thread_box').each(
			function() {
				var elem = $(this);
				elem.hoverIntent(
					function () {
						$('.thread_tools', elem).delay(50).fadeTo(200, 1) ;
					},
					function () {
						$('.thread_tools', elem).delay(400).fadeTo(1000, 0.2);
					}
				);
			}
		);
};

Thread.scrollTo = function(tid) {
	var toolbox	= '.thread_box.' + tid ;
	scrollToTop($(toolbox));
};
