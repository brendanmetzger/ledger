/*the dataset*/
// Here is a dataset, All times gathered from http://www.planetsforkids.org/
var planet_table = {
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
/*  end the dataset */

/* simple functions */

function isNumber(item) {
  return typeof item === 'number';
}

function add(first, second) {
  return first + second;
}

function sum() {
  return [...arguments].filter(isNumber).reduce(add, 0);
}

/* end simple functions */

/* utility function */
// given toHours('30 minute'), returns 0.5
// given toHours('2 day'), returns 48
function toHours(time) {
  console.log(time);
  time = time.split(' ');
  var table = {
    day:    24,
    hour:   1,
    minute: 1 / 60,
    second: 1 / 60 / 60
  };
  return table[time[1]] * parseInt(time[0], 10);
}

/* end utility function */

/* closure */

// start position, number of hours in day, callback to execute
// when 'ticking'. Note of the return value of outermost function.
function clock(position, hours, callback) {
  return function () {
    return callback((position++) / hours,  position % hours);
  };
}

/* end closure */


function makeClock(DOMelem, planet, total_hours) {
  // round for good measure
  total_hours = Math.round(total_hours);
  
  // set the text to the planet name
  DOMelem.querySelector('text.title').textContent = planet;

  // find the radius, hour hand, and the field that tracks days in our clock elem
  // why am I parseInt'ing the r attribute?
  var radius = parseInt(DOMelem.querySelector('circle').getAttribute('r'), 10);
  var hand   = DOMelem.querySelector('line');
  var tally  = DOMelem.querySelector('text.total');

  // space dashes to reflect the amount of hours
  var dash_size = Math.abs(2 * radius * Math.PI - (total_hours * 1.5)) / total_hours;
  DOMelem.querySelector('circle').style.strokeDasharray = dash_size + ' 1.5';

  // moveHand func has acceess to radius, hand, and tally vars. This is called..?
  var moveHand = function (days, hours) {
    // some light trigonometry
    var angle = (hours / total_hours) * (2 * Math.PI);
    hand.setAttribute('x2', Math.cos(angle) * (radius - 5) + radius);
    hand.setAttribute('y2', Math.sin(angle) * (radius - 5) + radius);
    // keep track of how many days have gone by.
    tally.textContent = Math.floor(days) + ' d';
    return "find me!";
  };

  return clock(0, total_hours, moveHand);
}



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
    clearInterval(timer); // clear the old timeout, start new one
    timer = setInterval(tickAllClocks, parseInt(this.value, 10));
  });

  
  /* data parsing */
  
  var expression = /([0-9]+\.?[0-9]*)\s+(day|hour|minute|second)/g;
  
  // objects can be iterated. planet will hold the property (key)
  for(var planet in planet_table) {
    // We start with something like this "10 hours 39 minutes and 24 seconds"
    var timestamps = planet_table[planet].match(expression);
    // after matching, we have: ['10 hour', '39 minute', '24 second']
    // mapped `toHours` gives numbers [10, 0.65, 0.006666]
    // then using sum function to get total hours!
    var earth_hours = sum(...timestamps.map(toHours));
    
    

    // I'm using cloneNode to duplicate my node
    var ClockElement = DOMcontainer.appendChild(DOMsvgClock.cloneNode(true));
    var tickerFunc = makeClock(ClockElement, planet, earth_hours);
   
    clocks.push(tickerFunc);
  }
}

/* end data parsing */

window.addEventListener('load', setup);
