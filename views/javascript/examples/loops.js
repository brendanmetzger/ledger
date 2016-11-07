
/* for loop */
var folk = ['Ned', 'Jeb', 'Deb']; // folk[0] is Ned

var total = folk.length; // total = 3

for (var i = 0; i < total; i = i + 1) {
  // i, by convention, stands for index
  console.log("hello " + folk[i]);
}

// output is:
// hello Ned
// hello Jeb
// hello Deb

/* end for loop */


/* forEach */
var folk = ['Ned', 'Jeb', 'Deb'];

var sayHelloToPerson = function (name) {
  console.log("hello " + name);
};
// forEach expects a callback function
folk.forEach(sayHelloToPerson); 

// output is:
// hello Ned
// hello Jeb
// hello Deb

/* end forEach */