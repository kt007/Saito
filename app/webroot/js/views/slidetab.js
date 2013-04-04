define([
    'jquery',
    'underscore',
    'backbone',
    'views/shouts'
], function($, _, Backbone, ShoutsView) {

    "use strict";

    var SlidetabView = Backbone.View.extend({

        events: {
            "click .slidetab-tab": "clickSlidetab"
        },

        initialize: function(options) {
            this.collection = options.collection;
            this.model.set('isOpen', this.isOpen());

            this.listenTo(this.model, 'change', this.toggleSlidetab);

            if (this.model.get('id') === 'shoutbox') {
                this.initShoutbox('#shoutbox', this.webroot);
            }

        },

        isOpen: function() {
            return this.$el.find(".slidetab-content").is(":visible");
        },

        clickSlidetab: function(model) {
            this.model.save('isOpen', !this.model.get('isOpen'));
        },

        toggleSlidetab: function() {
            if (this.model.get('isOpen')) {
                this.show();
            } else {
                this.hide();
            }
            this.toggleSlidetabTabInfo();
        },

        show: function() {
            this.$el.animate({
                'width': 250
            });
            this.$el.find('.slidetab-content').css('display','block');
        },

        hide: function() {
            this.$el.animate(
                {
                    'width': 28
                },
                _.bind(function() {
                    this.$el.find('.slidetab-content').css('display', 'none');
                }, this)
            );
        },

        toggleSlidetabTabInfo: function() {
            this.$el.find('.slidetab-tab-info').toggle();
        },

        initShoutbox: function(element_n, webroot) {
            new ShoutsView({
                el: this.$el,
                slidetabModel: this.model
            });
        }
    });

    return SlidetabView;

});