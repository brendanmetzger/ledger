# Running with page scroll

![Muybridge-style running illustration](http://52.35.59.206/media/images/running-man.png);

##HTML
`<img src="/media/images/running-man.png" class="animate"/>`

## CSS
```


.animate {
  float:left;
  width: 75px;
  height:130px;
  margin:1em;

  /* as of this writing (2016) use the following properties with caution */
  mix-blend-mode: multiply;  /* Will cause the white parts of img to blend with anything darken in background */
  object-fit:cover;          /* will scale image to fit the container, cropping it as necessary */
  object-position:0 0;       
  shape-outside: polygon(30% 0, 100% 65%, 110% 90%, 0 100%); 
}
      
```

## JavaScript

The premise of this script is very simple; when a user scrolls, calculate a new position for the image

```
// Find the <img> element that is animated
var runner = document.querySelector('img.animate');




// The Condensed Version
addEventListener('scroll', function () {
  runner.style.objectPosition = (((Math.floor(window.scrollY/6) % 9) / 9) * 100) + '% 0';
});