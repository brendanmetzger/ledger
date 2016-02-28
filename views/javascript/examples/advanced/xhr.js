/* simple request */

function Request(url, successCallback, failCallback, method) {
  // Declare our default variables
  var XHR;
  var defaultCallback = function(evt) {
    console.warn(evt.type  + ' event fired; you have not provided a callback');
  };

  // set up the XHR object.
  XHR = new XMLHttpRequest();
  XHR.open('GET', url);
  XHR.overrideMimeType('text/xml');
  XHR.addEventListener('load', successCallback || defaultCallback);
  XHR.addEventListener('error', failCallback || defaultCallback);
  XHR.send();
};

Request('/topics/glossary.html', function(evt) {
  console.log(this.responseXML.documentElement);
});


/* end simple request */
