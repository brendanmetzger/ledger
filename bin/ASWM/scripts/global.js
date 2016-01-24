window.addEventListener('load', function (evt) {
  if (/Helvetica/.test(window.getComputedStyle(document.body).fontFamily)) {
    document.body.classList.add('correct');
  }
});
