(function (size) {
  var panel = new SVG(document.querySelector('#drawing'), size, size);

  for(var i = 0; i <= size; i += 10) {
    panel.createElement('line', {
      x1: i,
      y1: 0,
      x2: size,
      y2: i
    });
    panel.createElement('circle', {
      cx: i,
      cy: size * Math.cos(i),
      r: Math.pow(i, 0.65)
    })
  }


  console.log(panel);
})(400)
