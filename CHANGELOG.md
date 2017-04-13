# < Changelog >

## Latest Changes (13.04.2017)

#### v3.5.0 --> v3.5.1
Fixing styling & overflow issues (admin-view)

**Issues:**
1. elements were ABOVE the page-toolbar
2. main toolbar for admin view was folding into itself instead of hiding &
revealing with correct proportions

**Solutions:**
1. added "z-index: 1;" to app-header-layout - element; added
"z-index: 2;" to app-drawer - element; added "z-index: 1;" to
app-toolbar - element(s) for "admin-raumverwaltung"
2. added attribute "sticky" to bottom toolbar; added attribute "reveals"
too the effect list of the app-header - Element

#### v3.4.3 --> v3.5.0
Elementary visual bug fixes
- fixed a well known bug causing paper-dialogs to appear behind their
backdrops when used nested with app-drawer
- corrected app-drawer-backdrop in admin view which has been filled
with white before due to a css mistake I made

#### v3.4.2 --> v3.4.3
Fixing design & propotion - Issues (CSS)
- fixed background of paper-items in app-drawer (admin-view) being grey
(changed to white)
- fixed margins of admin-view-elements to be *below* the toolbar and not
*under* it (toolbar was hiding the content)
- fixed proportions of margins of the paper-cards for the imprint page

#### v3.4.0 --> 3.4.2
Reworked admin page
- Rebuilt admin page which is now based on app-layout instead of paper-elements
- Rebuild teacher page which is now based on app-layout instead of paper-elements

#### v3.3.1-public --> v3.4.0
Very much visual improvements
- Rebuilt all pages / views using app-layout instead of paper-elements
(Except userpages e.g. teacher & admin because of the known problems
with iron-overlay layers :D)
- Fixed a lot of bugs queueing unnecessarily many AJAX requests
- reworked grid for Account page in order to fix some bugs appearing
on iPhone - Devices where the grid could not be displayed accordingly
- removed the "-public" ending, because it was just for the visuals but
is redundant

#### v3.3.0-public --> v3.3.1-public
Some visual improvements
- Card proportions / width for imprint
- Replaced ugly buttons in admin-usermanagement with dynamic Floating
Action Buttons

#### v3.2.4-public --> v3.3.0-public
Bug fixes and functionality improvements
- Increased XHR-Timeouts for Admin-Pages
- Deactivated annoying loading dialogs to show up whenever the selected view
changes due to JavaScript / XHR loading the users's session info (loading
it in the background now :^] )
- Added checks to indicater whether an admin view has already been loaded. If
so, the dialog indicating the loading of the element won't show up everytime
the views change (which was damn fking annoying)

#### v3.2.3-public --> v3.2.4-public
Bug fixes and Service Worker Improvements
- Changed Service Worker File Paths to support absolute routing
- Fixed user not getting logged out when clicking the logout button when logged
in as teacher on the teacher page

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
Bug fixes (YES, THERE WERE !!!)
- Replaced old htmlentities() with preg_replace regex, which I wanted to
do earlier but forgot to do it
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
- Implemented an API which returns the reservations by teacher name (all possible variations) and just all reservations (independent from teacher names)
- Added a max-entry-value $max so you can limit the returned datarows

#### v2.1 --> v2.2
- Fixing visual problems (Dynamic Toolbar in admin view to prevent toolbars stacking on each other)
- Fixed a loading dialog to open even when not needed and therefore not being closed
