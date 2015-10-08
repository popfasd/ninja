/*
 * Ninja is a *sneaky* HTML for processor
 */

var ninja = (function () {
    var that = {};

    var validationKey = '__nv';

    that.getValidationResults = function (key) {
        key = key || validationKey;
        /*
         * Blatantly copied from:
         * http://stackoverflow.com/questions/2090551/parse-query-string-in-javascript
         */
        var query = window.location.search.substring(1);
        var vars = query.split('&');
        for (var i=0; i<vars.length; i++) {
            var pair = vars[i].split('=');
            if (decodeURIComponent(pair[0]) == key) {
                return JSON.parse(atob(decodeURIComponent(pair[1])));
            }
        }
    };

    return that;
})();
