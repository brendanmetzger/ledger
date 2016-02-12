'use strict';



function closureTest($node) {
  /* closure test */
  var scope = "Outie";
  function checkScope() {
    var scope = "Innie";
    function f() {
      return scope;
    }
    return f();
  }
  /* end closure test */
}
