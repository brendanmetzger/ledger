var ns = function() {

/* functions as values */
// variable assigned to a string
var english_greeting = "hello";
// vs. assigned to a function
var englishGreeting = function() {
  return 'hello';
};
/* end functions as values */

/* loaf */
// DECLARE
function produceBread(ingredients) {
  return ingredients.join('+') + '=BREAD';
}
// APPLY
var loaf = produceBread(['flour', 'water', 'yeast']);
/* end loaf */


/* compose */
function produceBread(ingredents) {
  var dough = mix(ingredients);
  var bread = bake(dough);
  return bread;
}
/* end compose */



/* perform */
// Assume Lou is working, let's call him
var onDutyBaker = Lou();

// now that we have a Baker, we can do stuff
var dough = onDutyBaker(mix, ['flour', 'water']);
var bread = onDutyBaker(bake, dough);
/* end perform */

/* first class */
function produceBread(onDutyBaker, ingredients) {
  var dough = onDutyBaker(mix, ingredients);
  var bread = onDutyBaker(bake, dough);
  return bread;
}

var loaf = produceBread(Sue(), ['flour', 'water']);
/* end first class */

};

/* baker */
// Note the arguments of our declaration
function Baker(performAction, material) {
  // what we are doing to the first argument
  return performAction(material);
}
/* end baker */

/* people */
function Sue() {
  console.log("Sue on duty");
  return Baker;
}

function Lou() {
  console.log("Lou on duty");
  return Baker;
}

// what is activity, what can it do?
var activity = Sue();
/* end people */

/* purity */
function mix(ingredients) {
  return ingredients.join('+');
}
function bake(dough) {
  return dough.toUpperCase().concat(' = BREAD');
}
function produceBread(baker, ingredients) {
  return baker(bake, baker(mix, ingredients));
}

/* end purity */
