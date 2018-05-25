import $ from 'jquery';
import 'lib/backbone/backbone.jsonApi';
import 'lib/jquery.i18n/jquery.i18n.extend.js';
import 'lib/saito/underscore.extend';

window.$ = $;

// prevent appending of ?_<timestamp> requested urls
$.ajaxSetup({cache: true});

// make empty dict available for test cases
$.i18n.setDict({});

window.redirect = function (destination) {
  document.location.replace(destination);
};

const testsContext = require.context(".", true, /Spec$/);
testsContext.keys().forEach(testsContext);
