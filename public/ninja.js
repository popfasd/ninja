/*
 * Ninja is a *sneaky* HTML for processor
 */

var ninja = (function () {
    var that = {};

    var validationKey = '__nv';

    /**
     * Decodes validation results from URL to an array
     *
     * @param {string} key Optional validation key
     * @return {Array} Validation results
     */
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

    /**
     * Loads validation results for the form, allowing a callback to be defined
     * that can modify the fields that failed validation.
     *
     * @param {Function} highlightCallback Callback to run for each failed field
     */
    that.loadValidationResults = function (highlightCallback) {
        highlightCallback = highlightCallback || that.highlightField;

        // load form data from cookie if available
        if (document.cookie) {
            var cookies = document.cookie.split(';');

            // parse cookie for form data JSON string
            for (var i=0; i<cookies.length; i++) {
                var c = cookies[i];

                if (c.charAt(0) == ' ') c = c.substring(1, c.length);

                // parse JSON
                if (c.indexOf('formdata') == 0) {
                    var data = JSON.parse(c.substring(9, c.length));

                    // populate form fields
                    for (var j in data) {
                        var e = document.getElementById(j);
                        if (e) {
                            if (e.type == 'radio') {
                                e.checked = data[j];
                            } else {
                                e.value = data[j];
                            }
                        }
                    }

                    break;
                }
            }

        }


        // highlight fields that failed validation
        var results = ninja.getValidationResults();
        var c = 0;
        for (var i in results) {
            // jump to first failed field in page
            if (c == 0) document.location.href='#'+i;

            var field = document.getElementById(i);
            if (!field) continue;

            highlightCallback(field, results[i]);

            c++;
        }
    };

    /**
     * Highlight fields that failed validation
     *
     * @param {Object} field An HTML DOM element
     * @param {string} reason The reason for validation failure
     */
    that.highlightField = function (field, reason) {
        field.style.background = 'rgba(255,0,0,0.05)';
        field.style.borderColor = '#f00';

        var label;
        if (field.type == 'text') {
            label = field.parentElement.getElementsByTagName('label')[0];
        }

        label.style.color = '#f00';
        label.style.fontWeight = 'bold';

        if (reason == 'empty') {
            field.parentElement.appendChild(that.makeValidationAlert(
                'field cannot be left blank'
            ));
        } else if (reason == 'failed') {
            field.parentElement.appendChild(that.makeValidationAlert(
                'field contains invalid data'
            ));
        }
    };

    /**
     * Generate a validation alert element for insertion into the DOM
     *
     * @param {string} msg The contents of the alert
     */
    that.makeValidationAlert = function (msg) {
        var alert = document.createElement('DIV');
        alert.className = 'ninja-validation-alert';
        alert.appendChild(document.createTextNode(msg));
        return alert;
    };

    /**
     * Capture form data and save to a cookie before submitting the form
     *
     * @param {string} formId The ID of the form to submit
     */
    that.doFormSubmit = function (formId) {
        var form = document.getElementById(formId);
        var fields = form.getElementsByTagName('input');
        var values = {};
        for (var i=0; i<fields.length; i++) {
            var id = fields[i].id;
            if (fields[i].type == 'radio') {
                values[id] = fields[i].checked;
            } else {
                values[id] = fields[i].value;
            }
        }

        var date = new Date();
        document.cookie = 'formdata='+JSON.stringify(values)+'; expiry= '+date.toGMTString()+'; path=/';
        form.submit();
    };

    return that;
})();
