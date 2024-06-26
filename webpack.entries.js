/*
 * ENTRY CONFIG
 *
 * Add 1 entry for each "page" of your app
 * (including one that's included on every page - e.g. "app")
 *
 * Each entry will result in one JavaScript file (e.g. app.js)
 * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
 */
const path = require('path');

module.exports = {
//    app: "./app/assets/app.js",
    'SelfTerminate.page.account-settings': path.resolve(__dirname, './app/assets/account-settings.js')
};
