/*jslint browser: true */
/*jslint esversion: 6 */


var bloc = window.bloc || {};

window.Event.prototype.theta = function () {
  var rect  = this.target.getBoundingClientRect(), x, y, theta;

  if (this.type.substring(0, 5) === 'touch') {
    x = (this.touches[0].clientX - rect.left) - (rect.width / 2);
    y = (rect.height / 2) - (this.touches[0].clientY - rect.top);
  } else {
    x = (this.offsetX || this.layerX) - (rect.width / 2);
    y = (rect.height / 2) - (this.offsetY || this.layerY);
  }
  theta = Math.atan2(x, y) * (180 / Math.PI);
  return theta < 0 ? 360 + theta : theta;
};

String.prototype.format = function (o) {
  var args = typeof o === 'object' ? o : arguments;
  return this.replace(/\{((?:\d+)|(?:[a-z]+))\}/g, function (match, key) {
    return typeof args[key] !== 'undefined' ? args[key] : match;
  });
};

var cycle = function (index, limit) {
  return function () {
    return index++ % limit;
  };
};

/* SVG */

/* Quick way to create an SVG element with and a prototypal method
 * for creating children elements. (c) brendan.metzger@gmail.com
 */
var SVG = function (node, width, height) {
  this.width = width;
  this.height = height;
  this.element = this.createElement('svg', {
    'xmlns:xlink': 'http://www.w3.org/1999/xlink',
    'xmlns': 'http://www.w3.org/2000/svg',
    'version': 1.1,
    'viewBox': '0 0 ' + width + ' ' + height
  }, node);
  this.point = this.element.createSVGPoint();
};

SVG.prototype.createElement = function (name, opt, parent) {
  var node = document.createElementNS('http://www.w3.org/2000/svg', name);
  for (var key in opt) node.setAttribute(key, opt[key]);
  return parent === null ? node : (parent || this.element).appendChild(node);
};


// Get point in global SVG space
SVG.prototype.cursorPoint = function(evt){
  this.point.x = evt.clientX; this.point.y = evt.clientY;
  return this.point.matrixTransform(this.element.getScreenCTM().inverse());
};

/* end SVG */

function JSONP(src, callback, listener) {
  var script = document.createElement('script');
  if (! callback && listener) {
    script.onload = listener;
  } else {
    var key = 'JSONP_cb_'+Date.now().toString(36);
    window[key] = callback;
    script.src = src + '&callback=' + key;
  }
  document.head.appendChild(script);
}


function decHex(decimal, pad) {
  
  return (pad + parseInt(decimal, 10).toString(16).toUpperCase()).slice(-(pad || '00').length);
}

function decBin(decimal, pad = '00000000') {
  return (pad + parseInt(decimal, 10).toString(2)).slice(-pad.length);
}


function Validator(url, callback) {
  JSONP('https://validator.nu/?level=error&doc='+encodeURIComponent(url)+'&out=json', callback);
}

function Wikipedia(article, callback) {
  JSONP('https://en.wikipedia.org/w/api.php?action=parse&prop=text&format=json&page=' + article, callback);
}

function cssValidator(stylesheet, callback) {
  JSONP("https://jigsaw.w3.org/css-validator/validator?output=json&warning=0&profile=css3&uri=" + stylesheet, callback);
}

Wikipedia.viewer = function (object) {
  var drawer = document.body.appendChild(document.createElement('article'));
  var button = drawer.appendChild(document.createElement('button'));
  var lockBody = function (evt) {
    var method = evt.type.substring(5) == 'over' ? 'add' : 'remove';
    document.body.classList[method]('locked');
  };
  drawer.classList.add('drawer');
  drawer.appendChild(document.createElement('h1')).innerHTML = object.parse.title;
  drawer.appendChild(document.createElement('section')).innerHTML = object.parse.text['*'];
  drawer.addEventListener('mouseover', lockBody);
  drawer.addEventListener('mouseout', lockBody);
  button.innerHTML = '☓';
  button.title = "Close Drawer";
  button.addEventListener('click', function (evt) {
    document.body.removeChild(this.parentNode);
    document.body.classList.remove('locked');
  });
  [].forEach.call(drawer.querySelectorAll('section a'), function (a) {
    // don't have access to wikipedia, so just make links regular texn
    var span = document.createElement('span');
    span.innerHTML = a.innerHTML;
    a.parentNode.replaceChild(span, a);
  });
};

bloc.init('manage-links', function () {
  document.body.addEventListener('click', function (evt) {
    if (evt.target.matches('a[href^=http]')) {
      evt.preventDefault();
      var wiki = evt.target.href.match(/.*wikipedia.*\/(.*)$/);
      if (wiki) {
        Wikipedia(wiki.pop(), Wikipedia.viewer);
      } else {
        window.open(evt.target.href);
      }
    }
    if (evt.target.matches('a.copy')) {
      evt.preventDefault();
      var xhr = new XMLHttpRequest();
      xhr.open('GET', evt.target.href);
      xhr.addEventListener('load', function(evt) {
        window.prompt("Copy to clipboard: Ctrl+C, Enter", evt.target.responseText);
      });
      xhr.send();
    }
  });
  
  Array.from(document.querySelectorAll('*[data-path]')).forEach(function (item) {
    var a = item.insertBefore(document.createElement('a'), item.firstChild);
    a.href = "txmt://open?url=file://" + item.dataset.path;
    a.innerHTML = '<img src="/css/media/file-code.svg"/>';
    a.className = 'pin link';
  });
});



