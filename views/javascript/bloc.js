var cycle = function (current, limit) {
  return function () {
    return current >= limit ? current = 0 : ++current;
  }
}
