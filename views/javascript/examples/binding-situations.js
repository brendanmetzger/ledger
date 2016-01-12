var boundExample = {
  processItems: function() {
    this.items.each(function (item) {
      // Process item
      this.markItemAsProcessed(item);
    }.bind(this));
  }
};

var scopedExample = {
  processItems: function() {
    var that = this;
    this.items.each(function (item) {
      // Process item
      that.markItemAsProcessed(item);
    });
  }
};

var simpleExample = {
  processItems: function () {
    this.items.each(this.markItemAsProcessed.bind(this));
  }
};

Array.prototype.forEach = function (callback, context) {
  for (var i = 0; i < this.length; i+=1) {
    // the scope where callback came from
    // doesn't matter b/c it's called HERE
    callback.call(context, this[i]);
  }
};

(function () {
  'use strict';

  function ItemProcessor() {
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
    }
  }

  var obj = ItemProcessor();
  obj.processItems();
  obj.markItemAsProcessed(obj.items[0]);
  console.log('that', obj.processed);

})();


(function () {
  'use strict';

  function ItemProcessor() {
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
    }
  }

  var obj = ItemProcessor();
  obj.processItems();
  obj.markItemAsProcessed(obj.items[0]);
  console.log('bind', obj.processed);

})();


var examples = {
  delayClass: function ($node) {
    // `add` is a property of DOMTokenList; Which is
    // what `Node.classList` references

    var func = DOMTokenList.add.bind($node.classList, 'sub');
    setTimeout(func, 25);
  }
}
