require.config({
	shim: {
		underscore: {
			exports: '_'
		},
		backbone: {
			deps: ["underscore", "jquery"],
			exports: "Backbone"
		},
		backboneLocalStorage: {
			deps:["backbone"],
			exports: "Store"
		}
	},
	paths: {
		jquery: 'lib/jquery/jquery-require',
		jqueryhelpers: 'lib/jqueryhelpers',
		underscore: 'lib/underscore/underscore',
		backbone: 'lib/backbone/backbone',
		backboneLocalStorage: 'lib/backbone/backbone.localStorage',
		bootstrap: 'bootstrap/bootstrap',
		domReady: 'lib/domReady',
		text: 'lib/require/text'
		// @td include scrollTo after _app.js is gone
	}
});

require(['domReady', 'views/app', 'bootstrap', 'jqueryhelpers'], function(domReady, AppView){
	// fallback if dom does not get ready for some reason to show the content eventually
	var contentTimer = {
		show: function() {
			$('#content').show();
			console.log('Dom ready timed out: show content fallback used.');
			delete this.timeoutID;
		},

		setup: function() {
			this.cancel();
			var self = this;
			this.timeoutID = window.setTimeout(function() {self.show();}, 5000, "Wake up!");
		},

		cancel: function() {
			if(typeof this.timeoutID == "number") {
				window.clearTimeout(this.timeoutID);
				delete this.timeoutID;
			}
		}
	};
	contentTimer.setup();

	domReady(function () {
		var App = new AppView({
			SaitoApp: SaitoApp,
			contentTimer: contentTimer
		});
	});


});
