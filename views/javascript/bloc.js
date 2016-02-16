var cycle = function (index, limit) {
  return function () {
    return index++ % limit;
  }
};
