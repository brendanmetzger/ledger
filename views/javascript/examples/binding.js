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
