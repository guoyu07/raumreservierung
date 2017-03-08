/**
 * Created by moritz on 07.03.17.
 */

function update(request, cacheName) {
    if('onLine' in navigator) {
        if(navigator.onLine) {
            return caches.open(cacheName).then(function(cache) {
                return fetch(request).then(function(response) {
                    return cache.put(request, response.clone()).then(function() {
                        return response;
                    });
                });
            });
        }
    } else {
        return caches.open(cacheName).then(function(cache) {
            return fetch(request).then(function(response) {
                return cache.put(request, response.clone()).then(function() {
                    return response;
                });
            });
        });
    }
}