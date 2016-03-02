function Validator(url, callback) {
  var script = document.createElement('script');
  var key = 'validator_cb_'+Date.now().toString(36);
  window[key] = callback;
  script.src = 'https://validator.nu/?level=error&doc='+encodeURIComponent(url)+'&out=json&callback='+key;
  document.head.appendChild(script);
}
