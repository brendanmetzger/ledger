/* Establish a localhost connection for quicker work
 * This stript assumes you have node.js installed; install if necessary
	1. Open up your node terminal and navigate to the folder this file lives in.
	2. Type `npm` install connect serve-static`.
  3. In your command line, navigate back to your working folder.
	4. at the prompt, type `node server.js`
 *
 */


var Connect = require('connect');
var Server  = require('serve-static');
var port    = 8000;

Connect().use(Server(__dirname)).listen(port);

console.log('Go to browser and type localhost:'+port+'/ in the address bar'); 
