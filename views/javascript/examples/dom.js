window.addEventListener('load', function() {
/* click to cut */
var clickToCut = function (elem) {
  elem.innerHTML = '<span>' + elem.textContent + '</span>';
  elem.classList.toggle('cutout');
}

// find all h2 elements
var h2s = document.querySelectorAll('h2');

// Loop through h2's and assign click event to apply function above...
for (var i = 0; i < h2s.length; i++) {
  h2s[i].addEventListener('click', clickToCut.bind(null, h2s[i]))
}
/* end click to cut */

});
