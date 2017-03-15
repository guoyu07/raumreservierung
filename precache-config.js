/**
 * Created by moritz on 07.03.17.
 */

module.exports = {
    importScripts: [
        /** Excluded, cause the function is not needed atm */
        // "js/sw-update.js",
        "js/offline-analytics.js"
    ],
    staticFileGlobs: [
        "favicon.ico",
        "favicon.png",
        "mstile-144.png",
        "apple-touch-icon.png",
        "img/*",
        "src/raumreservierung-admin/img/*",
        "js/*",

        /** NPM Node Modules */

        "node_modules/sw-offline-google-analytics/build/*"
    ],
    /** Fallback as replace for the .htaccess 404 redirect */
    navigateFallback: '/index.html'
};