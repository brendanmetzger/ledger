/*
  TODO Log most recent errors file, line, message
*/

window.validate = (function(identity, domain) {
  if (! navigator.onLine) {
    console.info("you are not online, validator is turned off");
    return;
  }
  
  if (window.location.hostname === 'iam.colum.edu') return false;
  
  function getCSScheckup(CSS, re, force) {
           
    
    CSS.map(function (sheet) {
      let file = btoa(sheet.href.substring(sheet.href.indexOf('/', 7)));
      let size = Array.from(sheet.rules)
                      .map( r => r.cssText.replace(re, '').length)
                      .reduce((a, v) => a + v, 0) ;
                      
      let scores = (sessionStorage.getItem(file) || size.toString()).split(':').map(i => parseInt(i, 10));
      let delta  = size - scores[scores.length - 1];
      scores.push(size);
      if (Math.abs(delta) > 1 || force) {
        getSource(atob(file), function (evt) {
          validateCSS(evt.target.responseText, function (evt) {
            var messages = JSON.parse(evt.target.responseText).cssvalidation;
            if (messages.result.errorcount == 0) {
              console.debug('No errors in ', atob(file));
              return;
            };
            console.group('CSS Validation');
            if (messages.result.errorcount > 0) {
              messages.errors.forEach(function (error) {
                console.debug(`${error.message} at line ${error.line} of ${atob(file)}`);
              });
            }
            console.groupEnd();
          });
        });
      }
      
      sessionStorage.setItem(file, scores.join(':'));
      return scores;
    });
    return "Validating CSS..."
  }
  
  function getHTMLcheckup(force) {
    var key = 'HTML'+btoa(window.location.pathname);     
    var size = document.documentElement.outerHTML.replace(/[^\<\>]/g, '').length / 2;
    var scores = (sessionStorage.getItem(key) || size.toString()).split(':').map(i => parseInt(i, 10));
    var delta = size - scores[scores.length - 1];
    scores.push(size)
    if (Math.abs(delta) > 1 || force) {
      getSource(window.location.href, function (evt) {
        // console.log(evt.target.responseText);
        validateHTML(evt.target.responseText, function (evt) {
          var messages = JSON.parse(evt.target.responseText).messages;
          console.group('HTML Validation');
          messages.forEach(function (obj) {
            if (obj.type == 'error') {
              console.debug(obj.message, 'at line', obj.lastLine);
            } else {
              console.info(obj.message);
            }
          });
          console.groupEnd();
        });
      });
    }
    
    sessionStorage.setItem(key, scores.join(':'));
    return 'Validating HTML...';
  }
  
  function checkHTMLindentation() {
    // will give back an array of numbers corresponding to spaces/tabs used
    // if pattern starts odd, tabs are used, and it should osscilate between odd/even 
    // if spaces are used, everything should be even.
    var pattern = htm.match(/\n\s+/gm).map(item => item.match(/[^\n]/g).length);
  }


  function checkConsole() {
    var element = new Image();
    Object.defineProperty(element, 'id', {
      get: function () {
        document.documentElement.removeAttribute('data-warn');
      }
    });
    console.debug('%cto debug, run: validate.html() or validate.css()  ', element);
  }
  
  
  function validateHTML(text, callback) {
    var data = new FormData();
    data.append('out', 'json');
    data.append('content', text);
    var req  = new XMLHttpRequest();
    var url = btoa('https://validator.nu/');
    var file = btoa(window.location.pathname);
    req.overrideMimeType('application/json');
    req.open('POST', domain+identity+'/html/'+url+'/'+file+'.js', true);
    req.addEventListener('load', callback);
    
    req.send(data);
  }

  
  function validateCSS(text, callback) {
    var data = new FormData();
    data.append('profile', 'css3svg');
    data.append('output', 'json');
    data.append('showsource', 'yes');
    data.append('warning', '1'); // can be 0
    data.append('text', text);
    
    var req  = new XMLHttpRequest();
    var file = btoa(window.location.pathname);
    var url = btoa("https://jigsaw.w3.org/css-validator/validator");
    req.open('POST', domain+identity+'/css/'+url+'/'+file+'.js', true);
    req.overrideMimeType('application/json');
    req.addEventListener('load', callback);
    req.send(data);
  }
  
  // needs work.
  var getMatchedCSSRules = function (elem, css = document.styleSheets) {
    return Array.from(css).map( function (stylesheet) {
      return (stylesheet.cssRules ? Array.from(stylesheet.cssRules) : []).filter(function (item) {
        return elem.matches(item.selectorText);
      }).reduce( (a ,b) => a.concat(b), []);
    });
  };
  
  // validateCSS('* {fart: none; }', function (evt) {
  //   console.log(JSON.parse(evt.target.responseText));
  // });
  
  function getSource(url, callback) {
    var req = new XMLHttpRequest();
    req.open('GET', url, true);
    req.addEventListener('load', callback);
    req.send();
  }
  
  var validate = {
    html: function (auto) {
      return getHTMLcheckup(!auto);
    },
    css: function(auto) {
      return getCSScheckup(Array.from(document.styleSheets).filter(item => item.href && item.href.includes(window.location.hostname)), /[^;]/g, !auto);
    },
    density: function(elem) {
      if (!elem || (! elem instanceof Element)) {
        console.info("Checking entire document. Pass another element to check otherwise. Target density is > 1, results below:");
        elem = document.documentElement;
      }
      var re  = /\s+/g;
      var map = ['textContent','outerHTML'].map(m => elem[m].replace(re, ' ').trim().length);
      return Math.round(map[0] / (map[1] - map[0]) * 100) / 100;
    }
  };
  
  addEventListener('load', function() {
    document.documentElement.dataset.warn = "open console ↑";
    checkConsole();
    validate.html(true);
    validate.css(true);
  });
  
  return validate;
});