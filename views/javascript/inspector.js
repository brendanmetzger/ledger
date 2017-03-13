bloc.init('viewer', function () {
  document.querySelector('.tabbed > ul.buttons').addEventListener('click', function (evt) {
    if (! evt.target.matches('li a[data-file]')) return;
    document.querySelector('.visible').classList.remove('visible');
    document.querySelector(`section[data-file='${evt.target.dataset.file}']`).classList.add('visible');
    document.querySelector('.inspector .active').classList.remove('active');
    evt.target.classList.add('active');
  });
  
  var replacePlain = function (evt) {
    this.classList.remove('prettyprinted');
    this.replaceChild(evt.target.responseXML.documentElement.firstChild, this.firstChild);
    this.parentNode.style.opacity = 1;
    prettyPrint();
  };
  
  var replaceHTML = function (evt) {
    this.querySelector('article').innerHTML = evt.target.responseXML.documentElement.innerHTML;
    this.parentNode.style.opacity = 1;
  };
  
  
  var updatePanel = function (evt) {
    var request = new XMLHttpRequest();
    var url  = this.value;
    var elem = document.querySelector(`section[data-path='${url.split('/')[4]}'] pre`);
    var callback = replacePlain;

    if (elem == null) {
      elem = document.querySelector(`section[data-path='${url.split('/')[4]}']`);
      callback = replaceHTML;
    } else {
      elem.parentNode.style.opacity = 0;
    }
    
    request.open('GET', url);
    request.addEventListener('load', callback.bind(elem));
    request.send();
  };
  
  document.querySelectorAll('select').forEach(select => select.addEventListener('change', updatePanel));
});
