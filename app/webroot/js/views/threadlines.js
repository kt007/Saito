define([
	'jquery',
	'underscore',
	'backbone',
	], function($, _, Backbone) {
		// @td if everything is migrated to require/bb set var again
		ThreadLineView = Backbone.View.extend({

			className: 'thread_line',

			events: {
				'click .btn_show_thread': 'toggleInlineOpen',
				'click .link_show_thread': 'toggleInlineOpenFromLink'
			},

			initialize: function(){
				this.model.on('change:isInlineOpened', this._toggleInlineOpened, this);

				if (typeof this.scroll == 'undefined' ) this.scroll = true;
			},

			toggleInlineOpenFromLink: function(event) {
				if (this.model.get('isAlwaysShownInline')) {
					this.toggleInlineOpen(event);
				}
			},

			toggleInlineOpen: function(event) {
				event.preventDefault();
				if (!this.model.get('isInlineOpened')) {
					if (!this.model.get('isContentLoaded')) {
						this.model.loadContent();
					}
					this.model.set({
						isInlineOpened: true
					});
				} else {
					this.model.set({
						isInlineOpened: false
					});
				}
			},

			_toggleInlineOpened: function(model, isInlineOpened) {
				if(isInlineOpened) {
					this._showInlineView();
				} else {
					this._closeInlineView();
				}
			},

			_showInlineView: function () {
				var scroll = this.scroll;
				var id = this.model.id;

				$('.thread_line.' + id).fadeOut(
					100,
					function() {
						// performance: show instead slide
						// $(p.id_thread_inline).slideDown(null,

						$($('.thread_inline.' + id)).show(0,
							function() {
								// @td
//								if (scroll && !_isScrolledIntoView(p.id_bottom)) {
//									if(_isHeigherThanView(this)) {
//										scrollToTop(this);
//									}
//									else {
//										scrollToBottom(p.id_bottom);
//									}
//								}
							}
							);
					}
					);
			},

			_closeInlineView: function() {
				var scroll = this.scroll;
				var id = this.model.id;
				$('.thread_inline.' + id).slideUp(
					'fast',
					function() {
						$('.thread_line.' + id).slideDown();
						if (scroll) {
						// @td
						//					p.scrollLineIntoView();
						}
					}
					);
			}

		});

		return ThreadLineView;

	});