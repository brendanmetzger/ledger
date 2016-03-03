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
          request.send()
        } else if (! panel.classList.contains('prettyprinted')) {
          if (/\.html$/.test(file)) {
            Validator(evt.target.dataset.url, function (response) {
              var h3 = document.querySelector('.notes h3');
              var details = h3.parentNode.insertBefore(document.createElement('details'), h3.nextSibling);
              var summary = details.appendChild(document.createElement('summary'));
              var ol      = details.appendChild(document.createElement('ol'));
              summary.innerHTML = response.messages.length +  ' <abbr>HTML</abbr> errors';
              response.messages.forEach(function (obj) {
                var li = ol.appendChild(document.createElement('li'));
                li.textContent = 'Line ' + (obj.firstLine ? obj.firstLine + '-' : '') + obj.lastLine + ': ' + obj.message;
              });

            });
          }
          panel.classList.add('prettyprint');
          prettyPrint();
        }
      }
      panel.classList.add('visible');

    }
  });
});
