/*
 Smooth Scroll Tidbit @contact brendan.metzger@gmail.com
 Queries document for possible anchors, ensures that there is
 a corresponding element with the proper ID, attaches event listener
 to said and animates the scroll of the document to proper position.
*/

var scroll = (function () {
  var timeout = 0;
  var skip = false;
  function scrollToElement(evt) {
    
    clearTimeout(timeout);
    // if something else triggers a scroll, do not do anything.
    // if (!evt.isTrusted) return;
    
    function tween(now) {
      var complete = (now - start) / duration;
      var ratio = Math.min(1, 1 - Math.pow(1 - complete, 3));
      
      box.scrollLeft = (ratio * offset.left) + position[0];
      box.scrollTop  = (ratio * offset.top) + position[1];

      // scroll(delta + position[0],  );
      if (ratio < 1) {
        requestAnimationFrame(tween)
      } else {
        if (evt.target.matches('[href^="#"]')) {
          window.location.href = evt.target.href;
        }
        skip = false;
        box.style.overflow = 'auto';
      }
    }
  
    var duration = 750;
    var start    = performance.now();
    var offset   = this.getBoundingClientRect();  
    var box      = this.parentNode;

    while (box.scrollHeight === box.clientHeight) box = box.parentNode;
    
    
    var position = [box.scrollLeft, box.scrollTop - box.offsetTop];
     skip = true;
    if (!evt.isTrusted) {
      evt.preventDefault();
      tween(start);
      box.style.overflow = 'hidden';
      timeout = setTimeout(function () {
        
        
      }, 10);
    } else {
     
      tween(start);
    }
    
  }
  
  return {
    monitor: function (elem, spots, callback, offset) {
      var current = 0;
      elem.addEventListener('scroll', function (evt) {
        var position = Math.round(this.scrollTop / this.scrollHeight * spots);
        if (current !== position && !skip) {
          current = position;
          callback(current);
        }
        
      }, {passive: true});
    },
    toElement: function (elem) {
      scrollToElement.call(elem);
    },
    automatic: function () {
      var links = Array.from(document.querySelectorAll('a:not([href^="http"])[href*="#"]'));
      links.filter(elem => Boolean(document.querySelector(elem.hash))).forEach(function (elem) {
        elem.addEventListener('click', scrollToElement.bind(document.querySelector(elem.hash)), false);
      });
    }
  };
})();


window.addEventListener('load', scroll.automatic);
