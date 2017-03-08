# How to Build

## Last edited in version: v2.5.3-public

```
$ polymer build --sw-precache-config precache-config.js
```

## ServiceWorker - Update
1. The service worker will automatically update itself. 
<br>When loading the page, request headers are sent to indicate
that we don't want the browser cache to be used to cache files 
(which include the service-worker.js for example). Therefore, the 
service-worker.js will be fetched from the network if changed immediately.
2. The function included by importing the script "sw-update.js" is a function
to auto-refresh all files after being loaded from the cache. So after a file
is loaded from the cache, it gets re-downloaded asynchronously
from the network.

---

To 1: You should never change the request headers, because the service-worker.js
is being cached otherwise (we do **not** want that!!!).


**By the way:**<br>
The Request headers can be found the index.html (as meta tags with 
http-equiv=\["cache-control", "expires" and "pragma"\]) and as direct
response headers in the .htaccess (l. 5 - l. 9).

To 2: If you want to "activate" / implement this function, add the following
lines of code to the generated "service-worker.js" - file(s):

```JavaScript
/** Custom addition */
event.waitUntil(update(event.request, cacheName));
```

*(To Line 253 / After event.respondWith() finished)*

--> If you do not want to use this function and just update the files when
the service-worker.js file changes, leave the generated "service-worker.js"
as it is.

**Why would you not want to use this function? (e.g.)**

- to reduce data consumption of the website
- to free the network for asynchronous API - requests if the connection is expected to
be slow / bad

*(If you want to disable the function after implementing it, remove or comment
the line - which should be self-explaining though :D)*

## Additional Information

Last tested version containing these functions:<br>
v3.0.0-public