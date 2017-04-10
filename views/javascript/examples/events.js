'use strict';


(function () {
var keyboard = document.querySelector('textarea.censor');
/* censored keyboard */
var banned = ['q', 'w', 'x'];

function censor(evt) {
  // get the key, make lowercase, and assign to var
  var letter = evt.key.toLowerCase();
 
  if (banned.indexOf(letter) >= 0) {
    evt.preventDefault(); 
  }
}

// assume keyboard is a refererce to the textarea element 
keyboard.addEventListener('keydown', censor);
/* end censored keyboard */


document.querySelector('textarea.replace').addEventListener('keyup', function (evt) {
  this.value = this.value.replace(/[aeiou]/ig, ['😯', '😣', '☹️', '😦', '🙄', '🤔'][Math.floor(Math.random() * 6)]);
});
})();

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
