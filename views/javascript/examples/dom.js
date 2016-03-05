window.addEventListener('load', function() {
/* click to cut */
function clickToCut(elem) {
  elem.innerHTML = '<span>' + elem.innerHTML + '</span>';
  elem.classList.toggle('cutout');
}

// Assign all h2's click event to react to function above...
[].forEach.call(document.querySelectorAll('h2'), function(h2) {
  h2.addEventListener('click', clickToCut.bind(null, h2));
});
/* end click to cut */

});
