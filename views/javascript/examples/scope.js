// Global scope is what you have by default.
console.log("call track();");

var instructions = ["call track(); again", "type tracker = 3; and run track()` again.", "type previous or next"];
var index = 0;

function track (which) {
  var messages = {
    old: "You ran: " + instructions[index],
    now: "Now this: " + instructions[index++] + "... but before you do, what do you expect."
  }
  return which ? messages[which] : messages;
}
