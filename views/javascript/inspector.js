bloc.init('viewer', function () {
  document.querySelector('.tabbed > ul.buttons').addEventListener('click', function (evt) {
    if (evt.target.matches('li[data-file]')) {

      var file = evt.target.dataset.file;
      var current = document.querySelector('.inspector li.active');
          current.classList.remove('active');

      document.querySelector('.panel.visible').classList.remove('visible');
      evt.target.classList.add('active');

      var panel = document.querySelector('.inspector .panel[data-file="'+file+'"]');
      if (panel.nodeName.toLowerCase() === 'pre') {
        if (evt.target.dataset.url) {
          var request = new XMLHttpRequest();
          request.open('GET', '/task/source/'+evt.target.dataset.url);
          delete evt.target.dataset.url;

          request.addEventListener('load', function (evt) {
            panel.textContent = evt.target.responseText;
            if (!panel.classList.contains('plain-text')) {
              panel.classList.add('prettyprint');
              prettyPrint();
            }
          });
          request.send()
        } else if (! panel.classList.contains('prettyprinted')) {
          panel.classList.add('prettyprint');
          prettyPrint();
        }
      }
      panel.classList.add('visible');

    }
  });
});
