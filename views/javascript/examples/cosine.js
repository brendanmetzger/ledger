var power     = (n, x) => x === 0 ? 1 : n * power(n, x - 1);
var factorial = n => n === 0 ? 1 : n * factorial(n - 1);
var deg2rad   = deg => deg / 180 * 3.141592653589793;

function cosine(angle) {
  var precision = 15;
  var n = 0;
  var sum = 0;
  do {
    sum += power(-1, n) * power(angle, 2 * n) / factorial(2 * n);
  } while (n++ < precision);
  return sum;
}

var rad = deg2rad(45);
console.log(cosine(rad));
