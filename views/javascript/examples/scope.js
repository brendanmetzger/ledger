/* global */
var jerry = 'A Comedian';

function who() {
  alert(jerry);
}

who();
/* end global */


/* local */
var truck = "F-350";

function drive() {
  var truck = 'Silverado';
  alert(truck);
}

drive();
/* end local */

/* arguments */
var coffee = 'Intellegentsia';

function brew(coffee) {
  alert(coffee);
}

brew('Peets');
/* end arguments */


/* nested */
var booty = "gold";

function sailTo(name) {

  function xMarksTheSpot(paces) {
    alert(`Arrived at: ${name}`);
    alert(`Take ${paces} paces to the ${booty}`);
  }

  xMarksTheSpot(10);
  // what items can we access here?
}

sailTo('Atlantis');
// what items can we access here?
/* end nested */
