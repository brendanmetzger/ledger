/*
 Smooth Scroll Tidbit @contact brendan.metzger@gmail.com
 Queries document for possible anchors, ensures that there is
 a corresponding element with the proper ID, attaches event listener
 to said and animates the scroll of the document to proper position.
*/

function scrollToElement(evt) {
  // evt.preventDefault();
  function tween(now) {
    var complete = (now - start) / duration;
    var ratio = Math.min(1, 1 - Math.pow(1 - complete, 5));

    scroll.scrollTop  = ratio * offset.top + position[1];
    scroll.scrollLeft = ratio * offset.left + position[0];
    // scroll(delta + position[0],  );
    if (ratio < 1) requestAnimationFrame(tween);
  }
  
  var duration = 1000;
  var start    = performance.now();
  var offset  = this.getBoundingClientRect();  
  var scroll = this.parentNode;

  while (scroll.scrollHeight === scroll.clientHeight) scroll = scroll.parentNode;
  
  var position = [scroll.scrollLeft, scroll.scrollTop];

  tween(start);
}

window.addEventListener('load', function () {
  var links = Array.from(document.querySelectorAll('a:not([href^="http"])[href*="#"]'));
  links.filter(elem => Boolean(document.querySelector(elem.hash))).forEach(function (elem) {
    elem.addEventListener('click', scrollToElement.bind(document.querySelector(elem.hash)), false);
  });
});
