# Open-Access
A webproject using the Catless framework to create a publication uploading and viewing system.

This system is open source and allows administrators to dynamically create publications and configure them as necessary with a fully integrated framework provided by [http://catless.ncl.ac.uk/framework/view](Catless). Viewing uploaded source code is available using highlight.js and individual file access or downloading publications published under one branch may be downoaded as a .zip file. Furthermore, bookmarking your favourite publications and browsing all uploaded publications under a paginated environment is possible. 

To run this system you will need a working AMP stack (Apache, MySQL, PHP) with a version of PHP which is 5.5 or greater. Rewrites must be enabled within your Apache server to provide a RESTful interface. Database configuration may be done via `classes/config.php`. 

Simply download or clone this project, and then run `composer install` in the main directory to pull in the required dependancies. You should be able to setup the site from there.
