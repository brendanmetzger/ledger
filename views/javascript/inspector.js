bloc.init('viewer', function () {

  document.querySelector('.tabbed > ul.buttons').addEventListener('click', function (evt) {
    if (! evt.target.matches('li[data-file]')) return;

    scrollToElement.call(document.querySelector(evt.target.dataset.rel));
    document.querySelector('.visible').classList.remove('visible');
    document.querySelector(`section[data-file='${evt.target.dataset.file}']`).classList.add('visible');
    document.querySelector('.inspector li.active').classList.remove('active');
    evt.target.classList.add('active');


  });



});
