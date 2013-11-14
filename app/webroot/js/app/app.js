define(['marionette', 'app/core', 'app/vent',
  'modules/html5-notification/html5-notification'],
    function(Marionette, Core, EventBus) {
    // @todo
    //noinspection JSHint
    var AppInitData = SaitoApp;

    //noinspection JSHint
    var whenReady = function(callback) {
        require(['jquery', 'domReady'], function($, domReady) {
            if ($.isReady) {
                callback();
            } else {
                domReady(function() {
                    callback();
                });
            }
        });
    };

    var app = {

      bootstrapShoutbox: function() {
        whenReady(function() {
          require(['modules/shoutbox/shoutbox'], function(ShoutboxModule) {
            ShoutboxModule.start();
          });
        });
      },

        bootstrapApp: function(options) {
            require([
                'domReady', 'views/app', 'backbone', 'jquery', 'models/app',
                'views/notification',
                'modules/html5-notification/html5-notification',

                'app/time', 'lib/Saito/isAppVisible',

                'lib/jquery.i18n/jquery.i18n.extend',
                'bootstrap', 'lib/saito/backbone.initHelper',
                'lib/saito/backbone.modelHelper', 'fastclick'
            ],
                function(domReady, AppView, Backbone, $, App, NotificationView,
                         Html5NotificationModule
                    ) {
                    var appView,
                        appReady;

                    App.settings.set(options.SaitoApp.app.settings);
                    App.currentUser.set(options.SaitoApp.currentUser);
                    App.request = options.SaitoApp.request;

                    Html5NotificationModule.start();

                    //noinspection JSHint
                    new NotificationView();

                    window.addEventListener('load', function() {
                        //noinspection JSHint
                        new FastClick(document.body);
                    }, false);

                    // init i18n
                    $.i18n.setUrl(App.settings.get('webroot') + "saitos/langJs");

                    appView = new AppView();

                    appReady = function() {
                        // we need the App object initialized
                        // @todo decouple
                        if ('shouts' in AppInitData) {
                          app.bootstrapShoutbox();
                        }
                        appView.initFromDom({
                            SaitoApp: options.SaitoApp,
                            contentTimer: options.contentTimer
                        });
                    };

                    whenReady(appReady);
                }
            );
        }
    };

      var Application = Core;

      Application.addInitializer(app.bootstrapApp);
      Application.start({
        contentTimer: contentTimer,
        SaitoApp: AppInitData
      });

      EventBus.reqres.setHandler('webroot', function() {
        return AppInitData.app.settings.webroot;
      });
      EventBus.reqres.setHandler('apiroot', function() {
        return AppInitData.app.settings.webroot + 'api/v1/';
      });

    return Application;

});