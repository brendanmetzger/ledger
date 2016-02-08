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
function funcA(param) {
  // do we have a second argument?
  var options = arguments[1] ? true : false;
  console.log(param, options);
}

function funcB(param) {
  // same outcome as above
  return Boolean(arguments[1]) ? arguments[1] : param;
}

function funcC(param, options) {
  // same as above, options is falsy, what does that mean?
  var message = typeof param;
  return arguments.length + ' arguments given: ' + 'first was a ' + message;
}

// A useful example of the arguments object
function adder() {
  var sum = 0;
  for (var i = 0; i < arguments.length; i++) {
    sum += arguments[i];
  }
  return sum;
}

/* end arguments */





/* callbacks */
window.addEventListener('load', function (evt) {
  // <button data-color="cornflowerblue" class="trigger">Exec...
  var button = document.querySelector('button[data-color].trigger');
  if (button) {
    button.addEventListener('click', function (evt) { // anonymous`
      evt.preventDefault(); // investigate this.
      var old_bg = getComputedStyle(document.body).backgroundColor;
      document.body.style.transition = 'background-color 0.5s';
      document.body.style.backgroundColor = button.dataset.color;
      setTimeout(function () {
        document.body.style.backgroundColor = old_bg;
      }, 500);
    });
  }
});
/* end callbacks */


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
