function delegateClicks() {
/* event delegation */

var board = document.querySelector('div.delegate');

function delegate(evt) {
  var name = evt.target.nodeName.toLowerCase();

  if (name == 'span') {
    // 'this' is 'board' - it has the listener attached
    var dupe = this.appendChild(evt.target.cloneNode());
    var mod = (parseInt(evt.target.textContent) * 2);
    var color = 'hsl('+(mod % 360)+', 70%, 80%)';
    dupe.style.backgroundColor = color;
    dupe.textContent = mod;
  }
}

board.addEventListener('click', delegate);


/* end event delegation */
}
