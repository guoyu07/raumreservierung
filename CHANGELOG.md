# < Changelog >

## Latest Changes (15.03.2017)

#### v3.2.2-public --> v3.2.3-public
Various bug fixes
- Only reload session data when necessary and not on every view change
- Fixed a bug where the privacy element was registered a second time
because it wasn't loaded asynchronously in the account-page

#### v3.2.1-public --> v3.2.2-public
Changed E-Mail - Templates
- changed email - template - URLs to match the new URL-Routing-System

#### v3.1.0-public --> v3.2.1-public
Reworked Page Routing
- Completely redesigned the Page routing
- removed hash routing
- added URL routing
- added 404 fallbacks in SW as well has HTACCESS for URL routing
to work properly

#### v3.0.0-public --> v3.1.0-public
Bug fixes

**(YES, THERE WERE)**
- Replaced old htmlentities() with preg_replace regex, which I wanted to
do early but forgot to do it
- Added fallback toast to open in case the hash routing doenst work
when confirming the account / email address

#### v2.6.3-public --> v3.0.0-public
Offline Improvements
- Added checks for validating the connection status
- Added custom error messages to show the user that a request failed 
because of a missing internet connection

#### v2.6.2-public --> v2.6.3-public
Mobile optimizations
- Added media queries to privacy-dialog and imprint to "un-justify" the 
text on mobile devices

#### v2.6.0-public --> v2.6.2-public
Improvements in user friendliness
- Increased some important XHR timeouts (e.g. login: 5000ms to 15000ms)

**Bug Fixing**
- Added a "reload data" - button for the "roomlist" - element
- "roomlist" - element's own "reload" - property now generates a new request
as intended

#### v2.5.4-public --> v2.6.0-public
Service Worker additions
- Initialized NPM for this directory
- Added sw-google-offline-analytics (node_modules)
- Implemented sw-google-offline-analytics to service worker

#### v2.5.4-public --> v2.5.5-public
Service Worker additions
- Added images and image directories to service-worker caching 
sources (for the images of this page to be cached aswell in order to)

#### v2.5.3-public --> v2.5.4-public
Fixed an encoding bug
- htmlentities didnt allow vowels to be encoded correctly; replaced it with preg_replace regex

#### v2.5.0-public --> v2.5.3-public
Improved Caching and SW-Functionality:
- Added request headers to avoid the service-worker(.js) to be cached
- Improved sw-update.js to only reload if network is available
- Updated "BUILD.md" for better understanding of my choices (to add the
function to force the file-refresh e.g.)

#### v2.4.0-public --> v2.5.0-public
Improved Service Workers:
- Added precache-config for service worker builds
- Added sw-update.js which will have to be called after the caches are
returned in order to refresh the files after being loaded from the cache
(see "BUILD.md" for further details on building
and service-worker - options)
- Added build instructions for service workers

#### v2.3.2-public --> v2.4.0-public
- Added Polymer CLI's Service Worker to Project (SWImport to /index.html)

#### v2.3.1-public --> v2.3.2-public
- Added toast to app shell to indicate changes in connectivity (if the internet connection aborts)

#### v2.2 --> v2.3.1-public
- Added /backend/public/
- Added "reservation-api.php" to /backend/public/
- Implemented an API which returns the reservations by teacher name (all possible variations) and just all reservations (independant from teacher names)
- Added a max-entry-value $max so you can limit the returned datarows

#### v2.1 --> v2.2
- Fixing visual problems (Dynamic Toolbar in admin view to prevent toolbars stacking on each other)
- Fixed a loading dialog to open even when not needed and therefore not being closed