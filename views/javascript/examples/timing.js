var timers = {
basic: function (elem) {
/* basic timeout */

elem.dataset.text = elem.innerHTML;
elem.innerHTML    = 'wait for it...';

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
// declare variables
var elem = document.querySelector('textarea[name="limerick"]');
var tID  = 0; // keeps track of the timeout id
var size = 0;

// store a version of the value, set to blank
elem.dataset.text = elem.value;
elem.value = '';

function tap() {
  if (size > elem.dataset.text.length) size = 0;
  elem.value = elem.dataset.text.substring(0, size++);
  var delay = /[A-Z\W]$/.test(elem.value) ? 2 : Math.random();
  tID = setTimeout(tap, 150 * delay);
}

function stop() {
  clearTimeout(tID);
}

elem.addEventListener('focus', tap);
elem.addEventListener('blur', stop)

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
