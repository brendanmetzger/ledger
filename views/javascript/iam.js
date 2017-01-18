(function(){

  function getCSScheckup(CSS, re) {
    
    var current = Object.keys(CSS)
           .map(StyleSheetList.prototype.item.bind(CSS))
           .reduce((a, s) => s.rules ? a + Object.keys(s.rules)
           .map(i => s.rules[i].cssText.replace(re, '').length)
           .reduce((a, v) => a + v, 0) : a, 0);
    
    var key = 'CSS'+btoa(window.location.pathname);     
    var scores = (sessionStorage.getItem(key) || current.toString()).split(':').map(i => parseInt(i, 10));

    var delta = current - scores[scores.length - 1];
    
    if (delta !== 0) scores.push(current);
    
    
    sessionStorage.setItem(key, scores.join(':'));
    return scores;
  }
  
  function getHTMLcheckup() {
    var key = 'HTML'+btoa(window.location.pathname);     
    var current = document.documentElement.outerHTML.replace(/[^\<\>]/g, '').length / 2;
    var scores = (sessionStorage.getItem(key) || current.toString()).split(':').map(i => parseInt(i, 10));
    var delta = current - scores[scores.length - 1];
    if (delta !== 0) scores.push(current);
    
    sessionStorage.setItem(key, scores.join(':'));
    return scores;
  }
  
  function checkHTMLindentation() {
    // will give back an array of numbers corresponding to spaces/tabs used
    // if pattern starts odd, tabs are used, and it should osscilate between odd/even 
    // if spaces are used, everything should be even.
    var pattern = htm.match(/\n\s+/gm).map(item => item.match(/[^\n]/g).length);
  }

  function drawHelper() {
    var list = document.body.appendChild(document.createElement('ul'));
    list.addEventListener('click', evt => list.classList.toggle('open'));
    list.id = '_e_s_p_e_c_i_a_l_';
    list.classList.add('console');
    var item = list.appendChild(document.createElement('li'));
    item.innerHTML = 'open your console when developing';
    item.dataset.type  = 'console';
    item.classList.add('error');
    
  }


  function checkConsole(selector) {
    var element = new Image();
    Object.defineProperty(element, 'id', {
      get: function () {
        var item = document.querySelector(selector);
        item.parentNode.classList.remove('console');
        item.parentNode.removeChild(item);
      }
    });

    console.log('%cTodays secret code: ', element);
  }
  
  
  function validateHTML(text, callback) {
    var req  = new XMLHttpRequest();
    var data = new FormData();
    data.append('out', 'json');
    data.append('content', text);
    req.overrideMimeType('application/json');
    req.open('POST', 'https://validator.nu/');
    req.addEventListener('load', callback);
    req.send(data);
  }

  
  function validateCSS(text, callback) {
    var data = new FormData();
    data.append('profile', 'css3svg');
    data.append('output', 'json');
    data.append('warning', '1'); // can be 0
    data.append('text', text);
    
    var req  = new XMLHttpRequest();
    var url = btoa("https://jigsaw.w3.org/css-validator/validator");
    req.open('POST', 'http://pedagogy/task/validate/'+url+'.js', true);
    req.overrideMimeType('application/json');
    req.addEventListener('load', callback);
    req.send(data);
  }
  
  validateCSS('* {fart: none; }', function (evt) {
    console.log(JSON.parse(evt.target.responseText));
  });
  
  function getSource(url, callback) {
    var req = new XMLHttpRequest();
    req.open('GET', url, true);
    req.addEventListener('load', callback);
    req.send();
  }
  
  var current = window.location.href;
  getSource(current, function (evt) {
    // console.log(evt.target.responseText);
    validateHTML(evt.target.responseText, function (evt) {
      var messages = JSON.parse(evt.target.responseText).messages;
      messages.forEach(function (obj) {
        if (obj.type == 'error') {
          console.error('HTML Validation:', obj.message);
        } else {
          console.info(obj.message);
        }
      });
  });
  });
  
  addEventListener('load', function() {
    drawHelper('_e_s_p_e_c_i_a_l_');
    checkConsole('#_e_s_p_e_c_i_a_l_ [data-type=console]');
    console.log('css development', getCSScheckup(document.styleSheets, /[^;]/g));
    console.log('html development', getHTMLcheckup());
  });
}());
