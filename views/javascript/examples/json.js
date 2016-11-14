/* jsonp */

// A function that takes some data as an argument
function callMeMaybe(data_obj) {
  // Check console...
  console.log(data_obj);
}

// Some data that I've created
var some_data = {"message": "here's my number"};


// Calling an function with same data
// callMeMaybe({"message": "here's my number"});

/* end jsonp */



/* practical jsonp */

// a simple method that will turn a JS object into a query string
function serialize(obj) {
  return Object.keys(obj).reduce(function(accumulator, key){
    accumulator.push(key + '=' + encodeURIComponent(obj[key]));
    return accumulator;
  },[]).join('&');
}

// Our Validator. Essentially, we are just embedding a script.
function W3CValidator(test_url) {
  var params = {
    out: 'json',
    callback: 'processResults',
    level: 'error',
    doc: test_url
  };
  var script = document.createElement('script');
  script.src = 'https://validator.nu/?' + serialize(params);
  document.head.appendChild(script);
}

// The results from our previously embedded script will be wrapped
// in a function called `processResults` — we specified that, it can
// be anything we'd like.
function processResults(json) {
  console.log(json);
}

/* end practical jsonp */

$ = function (query) {
  return document.querySelector(query);
};

$.make = function (elem, config) {
  elem =  document.createElement(elem);
  for (var prop in config) {
    elem.setAttribute(prop, config[prop]);
  }
  return elem;
};

/* Load Reddit*/
function loadRedditGallery(json) {
  var loaded = function () {
    this.parentNode.classList.remove('loading');
  };
  var figure = $.make('figure', {'class': 'loading'});
  var image  = figure.appendChild(document.createElement('img'));
  var title  = figure.appendChild(document.createElement('figcaption'));
  var viewer = $('.reddit.gallery');
  json.data.children.forEach(function (item) {
    if (item.data.preview) {
      var fig = viewer.appendChild(figure.cloneNode(true));
      fig.firstChild.src = item.data.preview.images[0].source.url;
      fig.firstChild.addEventListener('load', loaded);
      fig.lastChild.innerHTML = item.data.title;
      fig.addEventListener('click', showFullImage);
    }
  });
}

function showFullImage(evt) {
  var overlay = getOverlayElement();
  var figure = overlay.appendChild(this.cloneNode(true));
}

/* End Load Reddit*/

function getOverlayElement() {
  var div = $('.overlay');
  if (div === null) {
    div = document.body.appendChild(document.createElement('div'));
    div.className = 'overlay';
    div.addEventListener('click', function (evt) {
      this.innerHTML = '';
      document.body.classList.remove('presenting');
    });
  }
  setTimeout(function () {
    document.body.classList.add('presenting');
  }, 10);

  return div;
}
