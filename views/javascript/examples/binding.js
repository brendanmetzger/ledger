/* new operations */

function SomethingNew(argument) {
  this.storage = argument;
}


function callTwoWays() {
  // plain old function call
  console.info('NO NEW');
  console.dir(SomethingNew(5));

  // same function, using `new`
  console.info('WITH NEW');
  console.dir(new SomethingNew(5));
}


/* end new operations */

(function(){
/* eloquent cat */

var cat = document.querySelector("img");
var angle = 0, lastTime = null;
function animate(time) {
  if (lastTime != null)
    angle += (time - lastTime) * 0.001;
  lastTime = time;
  cat.style.top = (Math.sin(angle) * 20) + "px";
  cat.style.left = (Math.cos(angle) * 200) + "px";
  requestAnimationFrame(animate);
}
requestAnimationFrame(animate);
/* end eloquent cat*/
});

/* currying */
function styler(elem, prop, unit, value) {
  elem.style[prop] = value + unit;
}

function Cat(elem) {
  this.top   = styler.bind(null, elem, 'top', 'px');
  this.left  = styler.bind(null, elem, 'left', 'px');
}

function animate(theta, previously, now) {
  theta += (now - previously) * 0.001;
  this.top( Math.sin(theta) * 50);
  this.left(Math.cos(theta) * 100);
  requestAnimationFrame(animate.bind(this, theta, now));
}

addEventListener('click', animate.bind(new Cat(document.querySelector("img")), 0, 0, 0));



/* end currying */


/* binding default  */
var taco = {
  flavor: 'delicious'
};

function eat() {
  console.log(taco === this.taco);
  console.log(this.taco === window.taco);
  console.log(window.taco === taco);
}
/* end binding default */


/* binding implicit  */
function logger() {
  console.log(this)
}

var wrap = {
  text: 'Wrapped method call',
  log: logger,
};
/* end binding implicit */


/* binding explicit  */
var cities = [
  { name: 'Chicago', status: 'the best'},
  { name: 'Los Angeles', status: '...fine' }
];

function output(verb) {
  console.log(this.name, verb, this.status);
}

// output.call(cities[0], 'is');
// output.call(cities[1], 'is');
/* end binding explicit */


/* binding new  */
function Puppet(name) {
  this.name = name;
  this.poke = function(msg) {
    console.log(this.name, 'says', msg);
    return this;
  }
}

var pills = new Puppet('Pilsbury Doughboy');
// pills.poke('WooHoo!');
/* end binding new */


/* hard binding */

var obj = {
  msg: 'I have exeucted this many times: ',
  count: 0
}

function count() {
  console.log(this.msg, this.count++);
}

// var timeout = setInterval(count.bind(obj), 1000);
/* end hard binding */
