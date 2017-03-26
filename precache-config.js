/**
 * Created by moritz on 07.03.17.
 */

module.exports = {
    importScripts: [
        /** Excluded, cause the function is not needed atm */
        // "./js/sw-update.js",
        "./js/offline-analytics.js"
    ],
    staticFileGlobs: [

        // Precache resources apart from Polymer fragments
        "./favicon.ico",
        "./favicon.png",
        "./mstile-144.png",
        "./apple-touch-icon.png",
        "./img/*",
        "./js/*",
        "./LICENSE.txt",

        // NPM Node Modules
        "./node_modules/sw-offline-google-analytics/build/*",

        /* UPDATE FOR SCHOOL WEBSERVER */
        // Precache of resources with absolute paths
        //
        "./index.html",
        // FRAGMENTS
        "./src/raumreservierung-admin/raumreservierung-admin.html",
        "./src/raumreservierung-admin/admin-error-reports.html",
        "./src/raumreservierung-admin/admin-nutzerverwaltung.html",
        "./src/raumreservierung-admin/admin-raumverwaltung.html",
        "./src/raumreservierung-email-confirmation/raumreservierung-email-confirmation.html",
        "./src/raumreservierung-landingpage/raumreservierung-landingpage.html",
        "./src/raumreservierung-login/raumreservierung-login.html",
        "./src/reporting-form/reporting-form.html",
        "./src/raumreservierung-privacy/raumreservierung-privacy.html",
        "./src/raumreservierung-activate-account/raumreservierung-activate-account.html",
        "./src/raumreservierung-imprint/raumreservierung-imprint.html",
        "./src/raumreservierung-reset-password/raumreservierung-reset-password.html",
        "./src/raumreservierung-account-page/raumreservierung-account-page.html",
        "./src/raumreservierung-admin/admin-cronjobs.html",
        "./src/raumreservierung-teacher/raumreservierung-teacher.html",
        "./src/raumreservierung-teacher/teacher-datepicker.html",
        "./src/raumreservierung-teacher/teacher-raumreservierung.html",
        "./src/raumreservierung-teacher/teacher-raumreservierung-roomlist.html",
        "./src/raumreservierung-teacher/teacher-reservation-request.html",
        "./src/raumreservierung-teacher/teacher-reservations.html",
        "./src/raumreservierung-teacher/teacher-timelist.html",
        "./src/raumreservierung-admin/admin-reservation-control.html",
        // APP SHELL
        "./src/raumreservierung-main/raumreservierung-main.html"
    ],
    /** Fallback as replace for the .htaccess 404 redirect */
    navigateFallback: 'index.html',
    directoryIndex: 'index.html'
};