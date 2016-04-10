function createTrail(num, appendToElem) {
  var ul = (appendToElem || document.body).appendChild(document.createElement('ul'));
  ul.className = 'mouse-trail';
  var items = [];
  for (var i = num; i > 0; i--) {
    items.push(ul.appendChild(document.createElement('li')));
  }

  (appendToElem || window).addEventListener('mousemove', function (evt) {
    var idx = i++ % num;
    items[idx].style.top = evt.pageY + 'px';
    items[idx].style.left = evt.pageX + 'px';
  });
}

function createAnimatedTrail(num, appendToElem) {
  var ul = (appendToElem || document.body).appendChild(document.createElement('ul'));
  ul.className = 'mouse-trail';
  var items = [];
  for (var i = num; i > 0; i--) {
    items.push(ul.appendChild(document.createElement('li')));
  }
  var coords = [0,0];

  (appendToElem || window).addEventListener('mousemove', function (evt) {
    coords = [evt.pageX, evt.pageY];
  });

  function moveToCoords(time) {
    for (var i = 0; i < items.length; i++) {

      var x = items[i].offsetLeft;
      var y = items[i].offsetTop;
      var delta = [coords[0] - x,  coords[1] - y];

      items[i].style.left =  (x + (delta[0] * Math.random() / 5)) + 'px';
      items[i].style.top  =  (y + (delta[1] * Math.random() / 5)) + 'px';
      items[i].style.transform = 'rotate('+Math.random() * 360+'deg)';
      // items[i].style.width = (5 + Math.abs(delta[0])) + 'px';
      // items[i].style.height = (5 + Math.abs(delta[1])) + 'px';
    }
    requestAnimationFrame(moveToCoords);
  }
  requestAnimationFrame(moveToCoords);
}
