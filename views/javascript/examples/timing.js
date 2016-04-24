var timers = {
basic: function(elem) {
/* basic timeout */

elem.dataset.text = elem.innerHTML;
elem.innerHTML = 'wait for it...';

setTimeout(function () {
  alert('hello!');
}, 1000);

setTimeout(function () {
  elem.innerHTML = elem.dataset.text;
}, 1000);

/* end basic timeout */
},
typewriter: function () {
/* clearing timers */

/* end clearing timers */
},
interval: function () {
/* basic interval */
var cycle = 0;
var delay = 1000;
var elem  = document.querySelector('#color');

elem.style.transition = 'background-color '+ delay +'ms';

function changeBackgroundColor() {
  var hue = (cycle += 15) % 360;
  var color = 'hsl('+hue+', 75%, 90%)';
  elem.style.backgroundColor = color;
}

setInterval(changeBackgroundColor, delay)
/* end basic interval */
}
};
