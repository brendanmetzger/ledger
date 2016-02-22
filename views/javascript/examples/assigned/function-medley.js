/* function examples */

function isNumber(item) {
  return typeof item === 'number';
}

function add(first, second) {
  return first + second;
}

function sum() {
  return [].filter.call(arguments, isNumber).reduce(add, 0);
}

// start position, number of hours in day, callback to execute
// when 'ticking'. Note of the return value of outermost function.
function clock(position, size, onTick) {
  return function () {
    return onTick(position++ / size,  position % size);
  }
}

// given convertToHours(30, 'minute'), returns 0.5
// given convertToHours(2, 'day'), returns 48
function convertToHours(quantity, unit) {
  var hours_in = {
    day:    24,
    hour:   1,
    minute: 1 / 60,
    second: 1 / 60 / 60
  }
  return hours_in[unit] * quantity;
}

function makeClock(DOMelem, planet, total_hours) {
  // set the text to the planet name
  DOMelem.querySelector('text.title').textContent = planet;

  // find the radius, hour hand, and the field that tracks days in our clock elem
  // why am I parseInt'ing the r attribute?
  var radius = parseInt(DOMelem.querySelector('circle').getAttribute('r'), 10);
  var hand   = DOMelem.querySelector('line');
  var tally  = DOMelem.querySelector('text.total');

  // space dashes to reflect the amount of hours
  var dash_size = Math.abs(2 * radius * Math.PI - (total_hours * 1.5)) / total_hours
  DOMelem.querySelector('circle').style.strokeDasharray = dash_size + ' 1.5';

  // moveHand func has acceess to radius, hand, and tally vars. This is called..?
  var moveHand = function (days, hours) {
    // some light trigonometry (soh cah toa)!
    var angle = (hours / total_hours) * (2 * Math.PI);
    hand.setAttribute('x2', Math.cos(angle) * (radius - 5) + radius);
    hand.setAttribute('y2', Math.sin(angle) * (radius - 5) + radius);
    // keep track of how many days have gone by.
    tally.textContent = Math.floor(days) + ' d';
    return "find me!";
  };

  return clock(0, total_hours, moveHand);
}

// Here is a dataset, All times gathered from http://www.planetsforkids.org/
var planets = {
  Mercury: "58 days and 15 hours",
  Venus: "243 days",
  Earth: "23 hours and 56 minutes",
  Mars: "24 hours 39 minutes and 35 seconds",
  Jupiter: "9.9 hours",
  Saturn: "10 hours 39 minutes and 24 seconds",
  Uranus: "17 hours 14 minutes and 24 seconds",
  Neptune: "16 hours 6 minutes and 36 seconds",
  Pluto: "6.39 days"
};

// Here is a Regular Expression (RegEx) to convert the string to a
// more consistent array. Reading from the left, the expression indicates:
// "match 1 or more digits, maybe a period and 0 or more digits followed
//  by spaces and one of the following words: day, hour, minute or second"
// Mars' string converts to ['24 hour', '39 minute', '35 second']
var expression = /([0-9]+\.?[0-9]*)\s+(day|hour|minute|second)/g;


// My setup function will be called when the window finishes loading
function setup(evt) {
  // declare a variable to hold our clocks, a timer to hold our timeout
  var clocks = [], timer;

  var tickAllClocks = function () {
    // our clocks array will have what type of things in it? (check loop below)
    clocks.forEach(function (tickerFunc) {
      tickerFunc();
    });
  };

  // find the svg clock element
  var DOMsvgClock  = document.querySelector('svg.clock');
  var DOMcontainer = DOMsvgClock.parentNode;
  var DOMtimeInput = DOMcontainer.appendChild(document.createElement('input'));

  // What's going on here?
  DOMcontainer.removeChild(DOMsvgClock);

  // can you think of a different (not better) way to write the following?
  DOMtimeInput.setAttribute('type', "range");
  DOMtimeInput.setAttribute('min', 10);
  DOMtimeInput.setAttribute('max', 500);
  DOMtimeInput.setAttribute('value', 10);
  DOMtimeInput.addEventListener('change', function () {
    clearTimeout(timer); // clear the old timeout, start new one
    timer = setInterval(tickAllClocks, parseInt(this.value, 10));
  });


  // objects can be iterated. planet will hold the property (key)
  for(var planet in planets) {
    // object values can be accessed with brackets, not just the dot operator
    var time_array = planets[planet].match(expression);
    var converted  = time_array.map(function (time_str) {
      return convertToHours.apply(null, time_str.split(' '));
    });
    // days, minutes, seconds are all hours: sum, round result to integer
    var total_earth_hours = Math.round(sum.apply(null, converted));

    // I'm using cloneNode to duplicate my node
    var ClockElement = DOMcontainer.appendChild(DOMsvgClock.cloneNode(true));
    var tickerFunc = makeClock(ClockElement, planet, total_earth_hours);
    clocks.push(tickerFunc);
  }
}

/* end function examples */

window.addEventListener('load', setup);
