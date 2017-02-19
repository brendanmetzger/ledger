bloc.init('viewer', function () {

  document.querySelector('.tabbed > ul.buttons').addEventListener('click', function (evt) {
    if (! evt.target.matches('li a[data-file]')) return;

    document.querySelector('.visible').classList.remove('visible');
    document.querySelector(`section[data-file='${evt.target.dataset.file}']`).classList.add('visible');
    document.querySelector('.inspector .active').classList.remove('active');
    evt.target.classList.add('active');


  });



});
