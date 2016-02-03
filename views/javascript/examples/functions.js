/* declaring functions */

// This is a named Function
function namedFunction(name) {
  console.log('hello ' + name);
}

// This is a Function assigned to a variable
var assignedFunction = function (name) {
  console.log('hello ' + name);
}

/* end declaring functions */


/* chicken or the egg */

var Chicken = function () {
  // As soon as a chicken is 'born', it tells you about itself
  console.log("Hello, I'm Chicken.");
  // A chicken can do ONE thing: once it exists, it can make an egg.
  return function () {
    console.log("..I produce an Egg.");
    return Egg;
  };
};

var Egg = function () {
  // As soon as an egg is 'born', it tells you about itself
  console.log("I'm an Egg.");
  // An egg can do one thing: once it is exists, it can make a Chicken.
  return function () {
    console.log("..I produce a Chicken");
    return Chicken;
  };
};


/* end chicken or the egg*/
