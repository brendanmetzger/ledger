/* order of evaluation */
var likeable = false;
var knows = {
  jQuery: true,
  javascript: false
};

console.log("Condition 1")
if ((knows.jQuery || knows.javascript) && likeable) {
  console.log("Let's hire");
} else {
  console.log("We'll Pass");
}

console.log("Condition 2");
if (likeable || (knows.javascript && knows.jQuery)) {
  console.log("Let's hire.");
} else {
  console.log("We'll Pass");
}
/* end order of evaluation */
