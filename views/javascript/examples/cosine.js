 function deg2rad(deg) {
  return deg / 180 * 3.141592653589793;
}

function power(n, x) {
  return x === 0 ? 1 : n * power(n, x - 1);
}

function factorial(n) {
  return n === 0 ? 1 : n * factorial(n - 1);
}


function cosine(angle) {
  var sum = 0;
  for (var n = 0; n < 15; n++) {
    sum += power(-1, n) * power(angle, 2 * n) / factorial(2 * n);
  }
  return sum;
}

var rad = deg2rad(45);
console.log(cosine(rad));
