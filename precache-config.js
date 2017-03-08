/**
 * Created by moritz on 07.03.17.
 */

module.exports = {
    importScripts: [
        "js/sw-update.js",
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
    ]
};