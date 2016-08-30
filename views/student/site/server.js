/* Establish a localhost connection for quicker work
 * This stript assumes you have node.js installed; install if necessary
	1. Open up your node terminal and navigate to the folder this file lives in.
	2. Type `npm` install connect serve-static`.
  3. In your command line, navigate back to your working folder.
	4. at the prompt, type `node server.js`
 *
 */

try {
  var connect = require('connect');
  var serveStatic = require('serve-static');
  connect().use(serveStatic(__dirname)).listen(8080, function(){
      console.log('Server running on 8080...');
  });

} catch (e) {
  if (e.code === 'MODULE_NOT_FOUND') {
    console.error('You need to install some modules. Type "npm install connect serve-static"');
  }
}
