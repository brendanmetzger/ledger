function Validator(url, callback) {
  var url = 'https://validator.w3.org/nu/?level=error&doc='+encodeURIComponent(url)+'&out=json';
  JSONP(url, callback)
}

function Wikipedia(article, callback) {
  var url = 'https://en.wikipedia.org/w/api.php?action=parse&section=0&prop=text&format=json&page=' + article;
  JSONP(url, callback)
}

function JSONP(src, callback) {
  var key = 'JSONP_cb_'+Date.now().toString(36);
  window[key] = callback;
  document.head.appendChild(document.createElement('script')).src = src + '&callback=' + key;
}
