/*
 * Some JS to power the API browser demo
 */

var ninja = {};

/*
 * Get the API base URI
 */
ninja.getApiBase = function () {
    var href = document.location.href;
    href = href.replace('public/api-browser.html', '?q=/api');
    return href;
};

/*
 * Render a flash message
 */
ninja.flash = function (msg, type) {
    var flash = $('.flash');
    var bag = flash.find('p');

    bag.html(msg).addClass(type);
    flash.show();

    window.setTimeout(function () {
        flash.hide();
        bag.empty().removeClass(type);
    }, 2000);
};

/*
 * Render the login view
 */
ninja.login = function (view, args) {

    var successFn = function (data) {
        document.cookie = '_jwt=' + data + '; path=/';
        ninja.do(args.target);
    };

    var failureFn = function (resp) {
        if (resp.status == 401) {
            ninja.flash('Authentication failed', 'error');
        } else {
            ninja.flash('An error occured', 'error');
        }
    };

    var apiuri = ninja.getApiBase() + '/auth';
    ninja.endpoint('POST ' + apiuri);

    var submitFn = function (e) {
        $.post(apiuri, view.find('form').serialize())
            .done(successFn)
            .fail(failureFn);
    };

    view.find('form').submit(function (e) {
        e.stopPropagation();

        $.post(ninja.getApiBase() + '/auth', view.find('form').serialize())
            .done(successFn)
            .fail(failureFn);

        return false;
    });

    view.show();

};

/*
 * Display the API endpoint on the page
 */
ninja.endpoint = function (uri) {
    $('#api-endpoint').html(uri);
};

/*
 * Render the dashboard view
 */
ninja.dashboard = function(view) {
    view.show();
};

/*
 * Render the domain key generation view
 */
ninja.gendomkey = function(view) {

    var successFn = function (data) {
        view.find('#domkey').text(data.token);
    };

    var failureFn = function (resp) {
        if (resp.status == 400) {
            ninja.flash(resp.responseJSON.message, 'error');
        } else {
            ninja.flash('An error occured', 'error');
        }
    };

    view.find('form').submit(function (e) {
        e.stopPropagation();

        $.post(ninja.getApiBase() + '/key', view.find('form').serialize())
            .done(successFn)
            .fail(failureFn);

        return false;
    });

    var apiuri = ninja.getApiBase() + '/key';
    ninja.endpoint('GET ' + apiuri);

    view.show();
};

/*
 * Render the forms view
 */
ninja.forms = function (view) {

    var successFn = function (data) {
        var list = view.find('#forms-list');
        list.empty();

        for (var id in data.forms) {
            var url = data.forms[id];
            list.append('<li><a data-form-id="' + id + '" href="submissions">' + url + '</a></li>');
        }

        view.show();
    };

    var failureFn = function (resp) {
        if (resp.status == 401) {
            ninja.do('login', 'forms');
        }
    };

    var apiuri = ninja.getApiBase() + '/forms';
    ninja.endpoint('GET ' + apiuri);

    $.get(apiuri)
        .done(successFn)
        .fail(failureFn);

};

/*
 * Render the submissions view
 */
ninja.submissions = function (view, args) {

    var successFn = function (data) {
        var table = view.find('#submissions-list');

        var head = table.find('thead > tr');
        head.empty();

        var first = Object.keys(data.submissions).shift();
        for (var key in data.submissions[first]) {
            head.append('<th>' + key + '</th>');
        }

        var body = table.find('tbody');
        body.empty();

        for (var id in data.submissions) {
            var sub = data.submissions[id];

            var cols = [];
            for (var key in sub) {
                var val = sub[key];

                // convert unit timestamp to date object
                if (key == '__ts') {
                    val = new Date(val * 1000);
                }

                cols.push(val);
            }

            body.append('<tr><td>' + cols.join('</td><td>') + '</td></tr>');
        }

        view.show();
    };

    var failureFn = function (resp) {

    };


    var apiuri = ninja.getApiBase() + '/forms/' + args.formId + '/submissions';
    ninja.endpoint('GET ' + apiuri);

    $.get(apiuri)
        .done(successFn)
        .fail(failureFn);

    view.find('a[href="export"]')
        .attr('href', ninja.getApiBase() + '/forms/' + args.formId + '/export')
        .attr('target', '_blank')
        .click(function (e) {
            e.stopPropagation();
        });
};

/*
 * Determine which view to load
 */
ninja.do = function (id, args) {

    args = args || {};

    $('section.page').hide();

    switch (id) {

        case "login":
            ninja.login($('#'+id), args);
            break;

        case "gendomkey":
            ninja.gendomkey($('#'+id), args);
            break;

        case "forms":
            ninja.forms($('#'+id), args);
            break;

        case "submissions":
            ninja.submissions($('#'+id), args);
            break;

        default:
            ninja.dashboard($('#dashboard'), args);
            break;
    }

};

/*
 * Kick things off
 */
$(document).ready(function () {

    ninja.do('dashboard');

    $('section.page').on('click', 'a', function (e) {
        e.preventDefault();
        ninja.do($(this).attr('href'), $(this).data());
    });

});

