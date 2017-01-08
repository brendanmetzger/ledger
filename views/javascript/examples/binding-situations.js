var boundExample = {
  processItems: function() {
    this.items.each(function (item) {
      this.markItemAsProcessed(item);
    }.bind(this));
    // Notice trailing call to bind.
  }
};

var scopedExample = {
  processItems: function() {
    var that = this;
    this.items.each(function (item) {
      that.markItemAsProcessed(item);
      // Notice use of `that` instead of `this`
    });
  }
};

var simpleExample = {
  processItems: function () {
    this.items.each(this.markItemAsProcessed.bind(this));
  }
};

Array.prototype.forEach = function (callback, context) {
  for (var i = 0; i < this.length; i += 1) {
    // the scope where callback came from
    // doesn't matter b/c it's called HERE
    callback.call(context, this[i]);
  }
};

(function () {
  'use strict';

  function itemProcessor() {
    return {
      items: ['a', 'b', 'c'],
      processed: [],
      processItems: function () {
        var that = this;
        this.items.forEach(function (item) {
          // Process item
          that.markItemAsProcessed(item);
        });
      },
      markItemAsProcessed: function (item) {
        this.processed.push(item);
      }
    };
  }

  var obj = itemProcessor();
  obj.processItems();
  obj.markItemAsProcessed(obj.items[0]);
  console.log('that', obj.processed);

})();


(function () {
  'use strict';

  function itemProcessor() {
    return {
      items: ['a', 'b', 'c'],
      processed: [],
      processItems: function () {
        // Process item
        this.items.forEach(this.markItemAsProcessed.bind(this));
      },
      markItemAsProcessed: function (item) {
        this.processed.push(item);
      }
    };
  }

  var obj = itemProcessor();
  obj.processItems();
  obj.markItemAsProcessed(obj.items[0]);
  console.log('bind', obj.processed);

})();


var examples = {
  delayAddClass_v1: function ($node) {
    // `add` is a property of DOMTokenList; Which is
    // what `Node.classList` references

    var func = DOMTokenList.add.bind($node.classList, 'sub');
    setTimeout(func, 25);
  },
  delayAddClass_v2: function () {
    function delay() {
      $span.classList.add('highlight');
    }
    setTimeout(delay, 25);
  },
  poachFunctionPropError: function () {
    // say we have a nodelist (array-like thing) of all p Elements
    var elems = document.querySelectorAll('p');

    // we love `forEach`, but nodeList has no such property.
    elems.forEach(callback); // TypeError ... .forEach is not a function
  },
  poachFunctionProp: function () {
    // Array has a forEach method, let's poach it with `call`.
    [].forEach.call(elems, callback); // works!
  }
};

/* beer song */
function sing(index) {
  // index bottles of beer o.t.w,
  // index bottles of beer...
}

var bottles = [99, 98, 97, 96, 95, 94 /* ... */]; 
var verse   = bottles.length;
sing(bottles[verse - 1]);
sing(bottles[verse - 2]);
sing(bottles[verse - 3]);
// ...
sing(bottles[verse - 99]);
/* end beer song */