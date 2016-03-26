/*
 Smooth Scroll Tidbit @contact brendan.metzger@gmail.com
 Queries document for possible anchors, ensures that there is
 a corresponding element with the proper ID, attaches event listener
 to said and animates the scroll of the document to proper position.
*/
window.addEventListener('load', function () {
  function tween(timestamp) {
    if (!this.start) this.start = timestamp;
    var ratio = Math.min(1, 1 - Math.pow(1 - (timestamp - this.start) / this.duration, 5));
    window.scroll(0, ( ratio * ( this.y[1] - this.y[0] ) ) + this.y[0]);
    if (ratio < 1) window.requestAnimationFrame(tween.bind(this));
  }
  function goTo(evt) {
    tween.call({
      y: [window.pageYOffset, window.pageYOffset + this.getBoundingClientRect().top],
      duration: 1000
    }, performance.now());
  };
  [].filter.call(document.querySelectorAll('a[href*="#"]'), function (elem) {
    return Boolean(document.querySelector(elem.hash));
  }).forEach(function (elem) {
    elem.addEventListener('click', goTo.bind(document.querySelector(elem.hash)), false);
  });
});
