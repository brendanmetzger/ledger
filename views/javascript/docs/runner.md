# Running with page scroll

![Muybridge-style running illustration](http://52.35.59.206/media/images/running-man.png)

Doing this work I ran into the ever-fascinating fact that [0.9999... = 1](https://en.wikipedia.org/wiki/0.999...) and even proved through a little animation exercise. 

##HTML

```html

<img src="/media/images/running-man.png" class="animate"/>

```

## CSS
```css

.animate {
  float:left;
  width: 76px;
  height:130px;
  margin:1em;

  /* as of this writing (2016) use the following properties with caution */
  mix-blend-mode: multiply;
  object-fit:cover;
  object-position:0 0;       
  shape-outside: polygon(30% 0, 100% 65%, 110% 90%, 0 100%); 
}
      
```

## JavaScript

The premise of this script is very simple; when a user scrolls, calculate a new `object-position` for the image

```javascript
/*
  Find the `<img>` Element that has a class of 'animate'
*/
var img_element = document.querySelector('img.animate');

/*
  Write a function to encapsulate the idea (and do all the work)
*/
var jogger = function () {

  /*
  There are 10 frames in the image, but we are going down an unintuitive road, so
  keep reading to understand how much '10' is manipulated into other numbers.
  NOTE: I have a 760 pixel wide img, but I've styled it to `width: 76px` in the
  css (see above)—were this not an example, I would calculate the frames with
  `var frames = img_element.naturalWidth / img_element.width;`
  */
  var frames = 10;

  /*
    Get the amount scrolled from the (global) window object, and assign to variable
  */
  var movement = window.scrollY;

  /*
    I want to slow the scroll down, so my runner moves a little slower. I'll use
    my frames var as a benchmark (totally arbitrary) and assign to a variable
    called delta, which in physics represents 'change' Note: `Math.floor` is a
    function that rounds any Number to the lowest integer value, ie 5.8 becomes 5
  */
  var delta = Math.floor(movement / frames);

  /*
    Capture a clock position using a modulus operator. Consider 10am + 4hours
    is 2pm. what you really did is find the remainder: 14 mod 12 = 2. Now
    picture a clock: 12 happens to be the same as 0, yes? So there are really
    11 positions on the clock, when you treat 12 and 0 as the same number; 12 /
    12 has a remainder of 0 after all. Generally you can put any number of
    positions on a clock-like interface from 0 to n. To convert to numerical
    position on the clock, we can use a modulus operator; and keep visualizing
    when using a modulus on a clock with n positions, the result can only ever
    have n-1 positions.
  */
  var position = (delta % frames);
  
  /*
    Now to convert the position to a percentage—this is tricky: I am in any one of
    the following positions on my clock [0(or 10), 1, 2, 3, 4, 5, 6, 7, 8, 9].
    Intuitively, position 0 should be 0% and position 9 should be 100%. That is
      `0 / factor = 0` and `9 / factor = 1`.
    You can see that 9 is the number we are looking for that solves both
    scenarios. This also corresponds nicely to our remainder problem: we only
    have `frames - 1` possible places to move to, so lets do the math and
    multiply by 100 to get a percentage.
  */   
  var step = (position / (frames - 1)) * 100;
  
  /*
    Finally, change the `<img>`'s style property by concatenating our step value
    into a string. Note, we only want the x position, but the y still needs to
    be specified. The following looks funny, but picture it hard coded
    as the following: `img_element.style.objectPosition = "88.88% 0"`
  */
  img_element.style.objectPosition = step + '% 0';
};

/*
 Add an event listener to the window object that is triggered on 'scroll' and
 supply our `jogger` function as the 2nd parameter to execute on each scroll event.
*/
window.addEventListener('scroll', jogger);
```

## Compacting Things
I don't recommend striving for this—especially when sharing work with others, but I want to show an alternate version as an example. The following script can, and often is, written in the following manner—a manner I get carried away with when I'm getting too clever in my instruction. When you see stuff like this, try to unpack it a bit: look for variables reused, create new variables when you figure out what certain operations are doing, rename variables into more meaningful terms. Note that I drop the `window` term—this is because it is in the global scope, so all of its methods and properties are available in other scopes (`addEventListener` and `scrollY`). I recommend being explicit as possible, but left it out here for example's sake.

```js
addEventListener('scroll', function jog (evt) {
  var n = this.naturalWidth / this.width;
  this.style.objectPosition = (((Math.floor(scrollY/n)%n)/(n-1))*100)+'% 0';
}.bind(document.querySelector('img.animate')));
```