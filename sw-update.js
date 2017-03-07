/**
 * Created by moritz on 07.03.17.
 */

function update(request, cacheName) {
    console.log("[service-worker.js] file-update called");
    return caches.open(cacheName).then(function(cache) {
        return fetch(request).then(function(response) {
            return cache.put(request, response.clone()).then(function() {
                return response;
            });
        });
    });
}