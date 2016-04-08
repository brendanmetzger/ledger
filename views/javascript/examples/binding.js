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


/* currying */

function styler(elem, prop, unit, value) {
  elem.style[prop] = value + unit;
}

function Cat(elem) {
  this.radius = Math.random() * 100;
  this.top  = styler.bind(null, elem, 'top', 'px');
  this.left = styler.bind(null, elem, 'left', 'px');
}

var james = new Cat(document.querySelector("img"));


var angle = 0, lastTime = null;

function animate(time) {
  if (lastTime != null)
    angle += (time - lastTime) * 0.001;
  lastTime = time;
  james.top(Math.sin(angle) * james.radius / 2);
  james.left(Math.cos(angle) * james.radius);
  requestAnimationFrame(animate);
}

document.body.addEventListener('click', animate.bind(null, performance.now()));



/* end currying */
