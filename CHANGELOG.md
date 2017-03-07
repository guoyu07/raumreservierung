# < Changelog >

## Latest Changes (07.03.2017)

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