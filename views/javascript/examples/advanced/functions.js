// partial application
var add = function(x) {
  return function (y) {
    return x + y;
  };
};
