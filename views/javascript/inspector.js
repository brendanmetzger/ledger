bloc.init('viewer', function () {
  document.querySelector('.tabbed > ul.buttons').addEventListener('click', function (evt) {
    

    if (evt.target.matches('li[data-file]')) {
      document.querySelector('.visible').classList.remove('visible');
      document.querySelector(`section[data-file='${evt.target.dataset.file}']`).classList.add('visible');
      document.querySelector('.inspector li.active').classList.remove('active');
      evt.target.classList.add('active');

      return;

      var panel = document.querySelector('.inspector .panel > *[data-file="'+file+'"]');

      if (panel.nodeName.toLowerCase() === 'pre') {
        if (!Boolean(panel.textContent)) {

          var request = new XMLHttpRequest();
          request.open('GET', '/task/source/'+evt.target.dataset.url);
          delete evt.target.dataset.url;

          request.addEventListener('load', function (evt) {
            panel.textContent = evt.target.responseText;
            if (!panel.classList.contains('plain-text')) {
              panel.classList.add('prettyprint');
              prettyPrint();
            } else {
              panel.classList.add('prettyprinted');
            }
          });
          request.send();
        } else if (! panel.classList.contains('prettyprinted')) {

          panel.classList.add('prettyprint');
          prettyPrint();
        }
        setTimeout(function () {
          [].forEach.call(panel.parentNode.querySelectorAll('li[data-line]'), function (li) {
            var idx = parseInt(li.dataset.line, 10) - 1;
            var line = li.parentNode.parentNode.nextElementSibling.querySelector('ol').children[idx];
            if (li.className == 'lang-css' && /\-\-|var\(/.test(line.textContent)) {
              li.textContent = 'Line ' + li.dataset.line + ': limited browser support';
              line.classList.add('info');
            } else {
              line.classList.add(li.dataset.type || 'error');
            }
            
          });
        }, 1000);
      }
      panel.parentNode.classList.add('visible');


    }
  });



});
