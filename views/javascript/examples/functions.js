"use strict";

/* declaring functions */
// This is a function declaration
function namedFunction() {
  console.log('hello ');
}

// This is a Function assigned to a variable
var assignedFunction = function () {
  console.log('hello');
}; // note the semicolon, this is an expression!

// A function is a value; it can be anonymous
(function() {
  console.log("Does anybody care about me?");
  // no..
});

// But an anonymous function can be called immediately
(function () {
  console.info("Hello! I'm here, now I'm gone!");
})();
/* end declaring functions */



/* chicken or the egg */
var Chicken = function () {
  // As soon as a chicken is 'born', it tells you about itself
  console.log("Hello, I'm Chicken.");
  // A chicken can do ONE thing: once executed, it gives you an egg.
  return function () {
    console.log("..I produce an Egg.");
    return Egg;
  };
};
var Egg = function () {
  // As soon as an egg is 'born', it tells you about itself
  console.log("I'm an Egg.");
  // An egg can do one thing: once executed, it gives you Chicken.
  return function () {
    console.log("..I produce a Chicken");
    return Chicken;
  };
};
/* end chicken or the egg*/




/* arguments */

// simple; add two numbers
function add(foo, bar) {
  return foo + bar;  
}

// arguments is an 'array-like' object
function howManyArgs() {
  var total = arguments.length;
  return `You supplied ${total}`;
}

// a more robust addder, though many (myself included)
// argue that using `arguments` feels weird.
function addAll() {
  var sum = 0;
  for (var i = 0; i < arguments.length; i++) {
    sum += arguments[i];
  }
  return sum;
}

//  As a point of reference, I'm doing the same thing in
// the *most* esoteric way on earth and somehow it feels better.
function adder(...nums) {
  return nums.reduce(add, 0);
}

/* end arguments */




window.addEventListener('load', function (evt) {
/* callbacks */
  function flashColor (evt) {
    evt.preventDefault(); // what does this do
    var old_bg = getComputedStyle(document.body).backgroundColor;
    document.body.style.transition      = 'background-color 0.5s';
    document.body.style.backgroundColor = button.dataset.color;
    setTimeout(function () {
      document.body.style.backgroundColor = old_bg;
    }, 1500);
  }
  
  var button = document.querySelector('button[data-color].trigger');
  button.addEventListener('click', flashColor);
  
/* end callbacks */
  
});

/* scopes */
var everywhere = "This variable is declared in the global scope";

var greeting = (function () { // (this is an IIFE)
  console.log(everywhere, "but it is available in a nested scope");
  var closing = 'Well, goodbye!';
  var messanger = function(message) {
    console.log(message, closing);
  };
  return messanger;
})();

greeting('What a nice day.');

try {
  // can we get that closing defined in our IIFE?
  console.log(closing);
} catch (e) {
  console.warn(e.message, "...that's because it's a closure");
}
/* end scopes */


/* pure vs impure */

// a pure function
function square(x) {
  return x * x;
}

// an impure function
var a;
function multiply(x) {
  return x * a;
}


/* end pure vs impure */

/* recursion */

function factorial(n) {
  return n === 0 ? 1 : n * factorial(n - 1);
};

/* end recursion */

/* map reduce */
function even(n) {
  return n % 2 === 0;
}

[1,2,3,4,5].filter(even).map(square);
// returns [4,16]

/* end map reduce */

/* exercises */
var exercise = {
  triangle: function (size) {
    var line = "\n";
    while (line.length < size) {
      line = '#' + line;
      console.log(line);
    }
  },
  triangleOut: function (size, letter) {
    // We are going to push characters onto the string
    // - if curious about ||, look up 'null coalescing operator'
    var stack = [letter || '#'];
    while (stack.length < size) {
      stack.push(stack[stack.length-1] + stack[0]);
    }
    return stack.join("\n");
  },
  fizzBuzz: function (size) {
    for (var index = 1; index <= size; index++) {
      // declare a variable to hold our STRING output.
      var output = '';

      // If the index number is divisible by 3, append a Fizz
      if (index % 3 === 0) output += 'Fizz';

      // If the index number is divisible by 5, append a Buzz
      if (index % 5 === 0) output += 'Buzz';

      // Note that 3 and 5 are factors of 15, so if the index has a
      // remainder 0 on 3 and 5, it certainly does on 15. We're done!
      console.log(index, output);
    }
  },
  chessBoard: function (size) {
    var output = '';
    for (var outer = 0; outer < size; outer++) {
      output += (outer % 2) ? ' ' : '';
      for (var inner = 0; inner < size; inner++) {
        output += (inner % 2) ? '#' : ' ';
      }
      output += "\n";
    }
    return output;
  }
}
/* end exercises */
