/* simple request */
function Request(url, success, failure, method = 'GET') {
  // Declare our default variables
  var XHR;
  var warn = function (evt) {
    console.warn(`${evt.type} event fired; you have not provided a callback`);
  };

  // set up the XHR object.
  XHR = new XMLHttpRequest();
  XHR.open(method, url);
  XHR.addEventListener('load',  success || warn);
  XHR.addEventListener('error', failure || warn);
  XHR.send();
  return "Sending Request...";
}

function processGists(evt) {
  // The respons is JSON — we need to parse it
  var responses = JSON.parse(evt.target.responseText);
  console.log(responses);
}

var gist_url = 'https://api.github.com/users/brendanmetzger/gists';

// run below this in console, see what happens!
// Request(gist_url, processGists);
/* end simple request */
