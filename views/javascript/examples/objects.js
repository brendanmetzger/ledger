(function() {
  
/* basic object */
var bureau = {
  make: 'Ikea',
  finish: 'melamine',
  shelves: [
    {
      name: 'Top',
      contents: ['briefs', 'mustard'],
    }
  ],
  drawers: [
    {
      name: 'Left Drawer',
      contents: ['briefs'],
    },
    {
      name: 'Right Drawer',
      contents: []
    }
  ]
};

/* end basic object */

/* object state */
// declare an object
var fido = {};
// declare a property
fido.happy = true;
// declare a method
fido.scold = function () {
  fido.happy = false;
};

// introspect and test  our program
console.log(fido.happy); // ?
fido.scold();
console.log(fido.happy); // ?

// control of flow is an important concept
if (! fido.happy) {
  console.log('Tail between the legs.');
} else {
  console.log('Look at that tail wag!');
}
/* end object state */


/* basic dom */
// we can 'find' any element /w a query
var moi = document.querySelector('section > h2');

// we can find it's siblings
var sœur  = moi.previousElementSibling;
var frère = moi.nextElementSibling;

// we can find it's parent
var mère = moi.parentNode;

// we can swap the order
mère.insertBefore(moi, sœur);

// we could introspect the new order
console.log(mère.children);

/* end basic dom */
  
  
})()

