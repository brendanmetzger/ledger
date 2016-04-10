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
  alert(coffee)
}

brew('Peets');
/* end arguments */


/* nested */
var booty = "Gold Ingot";

function sailTo(name) {
  alert("Arrived at: " + name);

  function xMarksTheSpot(paces) {
    alert("Paces: " + paces);
    alert("Collect: " + booty);
  }

  xMarksTheSpot(10);
  alert(paces); // paces undefined in current scope
}

sailTo('Atlantis');
alert(name); // name is undefined in global scope
/* end nested */
