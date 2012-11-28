define([
	'jquery',
	'underscore',
	'backbone',
	], function($, _, Backbone) {

		var ThreadView = Backbone.View.extend({

			className: 'thread_box',

			events: {
				"click .btn-threadCollapse":  "collapseThread",
				"click .js-btn-openAllThreadlines": "openAllThreadlines",
				"click .js-btn-closeAllThreadlines": "closeAllThreadlines",
				"click .js-btn-showAllNewThreadlines": "showAllNewThreadlines"
			},

			initialize: function(){
				this.model.on('change:isThreadCollapsed', this.toggleCollapseThread, this);

				if (this.model.get('isThreadCollapsed')) {
					this.hide();
				}
			},

			/**
			 * Opens all threadlines
			 */
			openAllThreadlines: function(event) {
				event.preventDefault();
				_.each(
					this.model.threadlines.where({
						isInlineOpened: false
					}), function(model) {
						model.set({
							isInlineOpened: true
						})
					}, this);

			},

			/**
			 * Closes all threadlines
			 */
			closeAllThreadlines: function(event) {
				event.preventDefault();
				_.each(
					this.model.threadlines.where({
						isInlineOpened: true
					}), function(model) {
						model.set({
							isInlineOpened: false
						})
					}, this);
			},

			/**
			 * Toggles all threads marked as unread/new in a thread tree
			 */
			showAllNewThreadlines: function(event) {
				event.preventDefault();
				_.each(
					this.model.threadlines.where({
						isInlineOpened: false,
						isNewToUser: true
					}), function(model) {
						model.set({
							isInlineOpened: true
						})
					}, this);
			},

			collapseThread: function(event) {
				event.preventDefault();
				this.model.toggleCollapseThread();
				this.model.save();
			},

			toggleCollapseThread: function(model, isThreadCollapsed) {
				if(isThreadCollapsed) {
					this.slideUp();
				} else {
					this.slideDown();
				}
			},

			slideUp: function() {
				$(this.el).find('.tree_thread > ul > li:not(:first-child)').slideUp('100');
				this.markHidden();
			},

			slideDown: function() {
				$(this.el).find('.tree_thread > ul > li:not(:first-child)').slideDown('100');
//				$(this.el).find('.ico-threadOpen').removeClass('ico-threadOpen').addClass('ico-threadCollapse');
//				$(this.el).find('.btn-threadCollapse').html(this.l18n_threadCollapse);
			},

			hide: function() {
				$(this.el).find('.tree_thread > ul > li:not(:first-child)').hide();
//				$(this.el).find('.ico-threadCollapse').removeClass('ico-threadCollapse').addClass('ico-threadOpen');
				this.markHidden();
			},

			markHidden: function() {
//				this.l18n_threadCollapse = $(this.el).find('.btn-threadCollapse').html();
//				$(this.el).find('.ico-threadCollapse').removeClass('ico-threadCollapse').addClass('ico-threadOpen');
//				$(this.el).find('.btn-threadCollapse').prepend('&bull;');
			}

		});

		return ThreadView;

	});