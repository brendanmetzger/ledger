String.prototype.format = function() {
  var args = typeof arguments[0] === 'object' ? arguments[0] : arguments;
  return this.replace(/{((?:\d+)|(?:[a-z]+))}/g, function(match, key) {
    return typeof args[key] != 'undefined' ? args[key] : match;
  });
};

var cycle = function (index, limit) {
  return function () {
    return index++ % limit;
  }
};

/* SVG */

/* Quick way to create an SVG element with and a prototypal method
 * for creating children elements. (c) brendan.metzger@gmail.com
 */
var SVG = function (node, options) {
  options['xmlns:xlink'] = 'http://www.w3.org/1999/xlink';
  options.xmlns = 'http://www.w3.org/2000/svg';
  options.version = 1.1;
  this.element = this.createElement('svg', options, node);
};

SVG.prototype.createElement = function(name, opt, parent) {
  var node = document.createElementNS('http://www.w3.org/2000/svg', name);
  for (var key in opt) node.setAttribute(key, opt[key]);
  return parent === null ? node : (parent || this.element).appendChild(node);
};

/* end SVG */

function JSONP(src, callback) {
  var key = 'JSONP_cb_'+Date.now().toString(36);
  window[key] = callback;
  document.head.appendChild(document.createElement('script')).src = src + '&callback=' + key;
}


function Validator(url, callback) {
  var url = 'https://validator.nu/?level=error&doc='+encodeURIComponent(url)+'&out=json';
  JSONP(url, callback)
}

function Wikipedia(article, callback) {
  var url = 'https://en.wikipedia.org/w/api.php?action=parse&prop=text&format=json&page=' + article;
  JSONP(url, callback)
}

Wikipedia.viewer = function (object) {
  console.log(object.parse.title);
  var drawer = document.body.appendChild(document.createElement('article'));
  var button = drawer.appendChild(document.createElement('button'));

  drawer.classList.add('drawer');
  drawer.appendChild(document.createElement('h1')).innerHTML = object.parse.title;
  drawer.appendChild(document.createElement('section')).innerHTML = object.parse.text['*'];
  button.innerHTML = '☓';
  button.title = "Close Drawer";
  button.addEventListener('click', function (evt) {
    document.body.removeChild(this.parentNode);
  });
  [].forEach.call(drawer.querySelectorAll('section a'), function (a) {
    // don't have access to wikipedia, so just make links regular texn
    var span = document.createElement('span')
    span.innerHTML = a.innerHTML;
    a.parentNode.replaceChild(span, a);
  });
};

bloc.init('manage-links', function () {
  document.body.addEventListener('click', function (evt) {
    if (evt.target.matches('a[href^=http]')) {
      evt.preventDefault();
      var wiki = evt.target.href.match(/.*wikipedia.*\/(.*)$/)
      if (wiki) {
        Wikipedia(wiki.pop(), Wikipedia.viewer);
      } else {
        window.open(evt.target.href);
      }
    }
  });
});
