/* simple drawing */
(function (size) {
  var panel = new SVG(document.querySelector('#drawing'), size, size);
  var group = panel.createElement('g');
  for(var i = 0; i <= size; i += 10) {
    panel.createElement('line', {
      x1: i,
      y1: 0,
      x2: size,
      y2: i
    }, group);
  }

  group.addEventListener('mousemove', function (evt) {
    var mouse = panel.cursorPoint(evt);
    [].forEach.call(this.querySelectorAll('line'), function (line) {
      line.setAttribute('x2', mouse.x);
      line.setAttribute('y1', mouse.y);
    })
  });
})(400)
/* end simple drawing */
