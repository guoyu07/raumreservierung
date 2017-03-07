# How to Build

```
polymer build --sw-precache-config precache-config.js
```

Important: Keep precache-config.js up-to-date with polymer.json static dependencies.

Also, add following line to generated service-worker.js in /build/bundled
or /build/unbundled :

```JavaScript
/** Custom addition */
event.waitUntil(update(event.request, cacheName));
```

*To Line 253 / After evend.respondWith() finished*