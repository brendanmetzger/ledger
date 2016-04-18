/* keyboard */
  // assume some input event that we are typing in
  var input = document.querySelector('input');

  input.addEventListener('keydown', function (evt) {
    if (evt.charCode == 87) { // ??
      evt.preventDefault();
    }
  });
/* end keyboard */
