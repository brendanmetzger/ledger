/* callback */

  function callMeMaybe(object) {
    console.log(object);
  }

/* end callback */


(
/* json */
{"message": "here's my number"}
/* end json */
);

/* jsonp */

callMeMaybe({"message": "here's my number"});

/* end jsonp */
