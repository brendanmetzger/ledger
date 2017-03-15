/* taylor */
// unlike us, math folk prefer radians over degrees,
// so let's make a function that converts them for us.
function deg2rad(deg) {
  return deg / 180 * Math.PI;
}

// notice this function calls itself
function power(n, x) {
  return x === 0 ? 1 : n * power(n, x - 1);
}

// this one too (it is called recursion)
function factorial(n) {
  return n === 0 ? 1 : n * factorial(n - 1);
}

// this is a loop and a combitation of functions
function cosine(angle) {
  var sum = 0;
  for (var n = 0; n < 15; n++) {
    sum += power(-1, n) * power(angle, 2 * n) / factorial(2 * n);
  }
  return sum;
}
/* end taylor */
