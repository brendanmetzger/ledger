/* jsonp */

// A function that takes some data as an argument
function callMeMaybe(data_obj) {
  // Check console...
  console.log(data_obj);
}

// Some data that I've created
var some_data = {"message": "here's my number"};


// Calling an function with same data
callMeMaybe({"message": "here's my number"});

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
