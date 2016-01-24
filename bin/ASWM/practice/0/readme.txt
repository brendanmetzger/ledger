
# Planning and describing projects

## Your Folder Structure
When it comes to scripting and using advanced markup, directory structure as well as a fundamental understanding of filesystems and urls is imperative. Beyond loading media, script files, and cascading stylesheets, protocols and server policies become very important as we move to higher level concepts like AJAX and use of 3rd party APIs

## Readme File
Readme files are where you can attribute others' work, save links, explain your project, etc. Every practice assignment and project has one by default, so it is up to you to fill it out further.

[Read on Wikipedia](https://en.wikipedia.org/wiki/README)

### index.html
You will find an index.html inside of every practice assignment folder, project folder, and at the top level of your course site. Index files are loaded by default by most web servers as a [Directory Index](https://en.wikipedia.org/wiki/Webserver_directory_index), that means, you don't need to type it into the url and it will show up in the page.


## styles
Stylesheets go here, and by default I have included a stylesheet with the project name ready to go. Make sure this stylesheet is linked properly in all of your html files, starting with your index.html file right away. In order to learn about code reuse, I'm asking that you also link to a global css file that will be located in the top level of your directory in the styles folder.

## scripts
All of your JavaScript files go in this directory. In the head of your document you must correctly provide a link to the correct project .js file, as well as a link to the global.js file located in your document root.

## media
This folder can be used to store images, videos, audio, PDFs and any other type of media asset. If you use a lot of media, do not hesitate to further organize the media folder with sub-directories.

## data
Data is where we will put items that represent large data structures that we can use in our projects, generally JSON or XML. In your top level data directory, I'm including a profile.js to begin with, and you should make an effort to fill it out. We will continue to add files to this directory throughout the semester, some provided by me, and some that you will offer.
