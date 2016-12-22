# Running with page scroll

![Muybridge-style running illustration](http://52.35.59.206/media/images/running-man.png)

##HTML

```html

<img src="/media/images/running-man.png" class="animate"/>

```

## CSS
```css

.animate {
  float:left;
  width: 75px;
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
// Find the <img> Element that has a class of 'animate'
var img_element = document.querySelector('img.animate');

// Write a function to encapsulate the idea (and do all the work)
var jogger = function() {

  // there are 10 frames in the image, but we are going down an unintuitive road,
  // so read the following carefully to understand that 10 is a human-way of counting
  var frames = 10;

  // get the amount scrolled from the global window object, and assign to variable
  var movement = window.scrollY;

  // I want to slow the scroll down, so my runner moves a little slower. 
  // I'll use my frames var as a benchmark (totally arbitrary) and assign to 
  // a variable called delta, which in physics represents 'change'
  // Note: Math.floor is a function that rounds any Number to the lowest integer value, ie 5.8 becomes 5
  var delta = Math.floor(movement / frames);

  // capture a clock position using a modulus operator. Think 10am + 4hours is 2pm.
  // what you really did is find the remainder:  14 mod 12 = 2. Now picture a clock:
  // 12 happens to be the same as 0, yes? So there are really 11 positions on the clock, 
  // if you look at it that way, because 12 mod 12 = 0 after all. Generally you can put any 
  // number of positions on a clock-like interface from 0 to n. To convert to numerical 
  // position on the clock, we can use a modulus operator; and keep visualizing when using a
  //  modulus on a clock with n positions, the result can only ever be 0 through n-1.
  var position = (delta % frames);

  // now I need to convert my position to a percentage. This is tricky: I am in any one of
  // the following positions on my clock [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]. Intuitively, position
  // 0 should be 0% and position 9 should be 100%. That is, 0 / factor = 0, and 9 / factor = 1. You
  // can see that 9 is the number we are looing for so solve both scenarios. This also corresponds 
  // nicely to our remainder problem: we only have frames - 1 possible places to move to, so lets 
  // do the math and multiply by 100 to get a percentage.   
  var step = (position / (frames - 1)) * 100;
  
  // finally, change the runner's style property by concatenating our step value into
  // a string. Note, we only want the x position, but the y still needs to be specified
  // the following looks funny, but the totatl think if hard coded would look like:
  // runner.style.objectPosition = "88.88% 0" 
  img_element.style.objectPosition = step + '% 0';
  
  // Lastly, doing this work I ran into the ever-fascinating fact that 
  // 0.9999... = 1 (see here!)[https://en.wikipedia.org/wiki/0.999...]
};

// add an event listener to the window object that is triggered on 'scroll' and 
// use our run function as the callback to execute

addEventListener('scroll', jogger);
```

Not recommended—especially for learning purposes, but just as a word of caution, the following script can and usually would be written in the following manner (something I myself get carried away with under the guise of 'efficiency')

```
var img_element = document.querySelector('img.animate');
addEventListener('scroll', function () {
  img_element.style.objectPosition = (((Math.floor(window.scrollY/6) % 9) / 9) * 100) + '% 0';
});

// For the curious: I condensed a lot but why did I declare an img_element var instead of writing:
addEventListener('scroll', evt => document.querySelector('img.animate').style.objectPosition = (((Math.floor(window.scrollY/6) % 9) / 9) * 100) + '% 0');
```