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

// Write a function to express do all the work
var jogger = function() {

  // there are 10 frames in the image (as counted with my eyeballs), but 
  // I found by trial and error that 1 is in view and 9
  // are hidden, so we only have total - 1 to move about.
  var frames = 9;

  // get the amount scrolled from the global window object, and assign to variable
  var movement = window.scrollY;

  // I want to slow the scroll down, so my runner moves a little slower. 
  // I'll use my frames var as a benchmark (totally arbitrary) and assign to 
  // a variable called delta, which in physics represents 'change'
  // Note: Math.floor is a function that rounds any Number to the lowest integer value, ie 5.8 becomes 5
  var delta = Math.floor(movement / frames / 1.5);
  
  // capture a clock position using a modulus operator. Think 10am + 4hours is 2pm.
  // what you really did is find the remainder:  14 mod 12 = 2. Once I have the position
  // from 0 - 10, I need to convert to a position
  var position = (delta % frames);
  
  // now I need to convert my position from a continuous variable to a discrete so that
  // the runner moves in steps opposed to smooth motion. Divide the position by the number
  // of positions possible to get a percentage. You will have a number less than 1, so
  // it will need to be multiplied by 100 to get a more normal looking percentage.
  // Visualize: position is a number between 1 and 9, frames is the number 9
  
  var step = (position / frames) * 100;
  
  // finally, change the runner's style property by concatenating our step value into
  // a string. Note, we only want the x position, but the y still needs to be specified
  // the following looks funny, but the totatl think if hard coded would look like:
  // runner.style.objectPosition = "88.88% 0" 
  img_element.style.objectPosition = step + '% 0';
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
addEventListener('scroll', function () {
  document.querySelector('img.animate').style.objectPosition = (((Math.floor(window.scrollY/6) % 9) / 9) * 100) + '% 0';
});
```