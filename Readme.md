Ninja
=====

Ninja is a *sneaky* HTML form mailer. Like any good Ninja, it operates inconspicously, is adaptable, and requires little instruction. After some simple configuration you can point any HTML for at Ninja and it will automatically start collecting submissions and notifying you by email of each submission.

Edit `private/parameters.php`

    <?php

    $parameters = [
        'mailto' => ['your@address.com'],
        'uriPrefix' => '/path/to/ninja',
        'formDir' => 'private/cache'
    ];

Now update your `form` tag's `action` and `method` attributes. Ninja only likes POST requests.

    <form action="/path/to/ninja/index.php?q=/submit" method="post">

Each form is given an ID which is derived from the `sha1` hash of the form URL. All form data and configuration is stored in `private/cache/<id>` by default. The first time a form is submitted to Ninja, it creates the form's directory and drops a `settings.php` file in the directory which includes a PHP comment containing the form's URL. Submissions are stored in `private/cache/<id>/submissions.tsv`.

Friendly Field Names
--------------------

The fields in each submission are named after the fields names submitted in the POST request. If you want to make these field names a little more human-friendly, you can assign them in the `settings.php` file for the form. Add a `$fields` array with the submitted field name as the array key and the friendly name as the key's value.

    $fields = [
        'field1_firstName' => 'First name',
        'street_addr' => 'Street address',
        'zip_postal' => 'Zip/Postal code'
    ];

Keep in mind that once `$fields` is defined, Ninja will only collect information for the fields that have been defined. In this way, you can also use `$fields` to filter out fields you don't want to collect.

Say Thank You
-------------

By default users are redirected to Ninja's included "thank you" page `/path/to/ninja/public/thanks.html'. Ninja's don't care much for fancy things, and you may find Ninja's thank-you page a little too ausetre. Fortunately, you can change this using `$nexturl` in `settings.php`.

    $nexturl = 'http://example.com/super-awesome-thanks.html';

Ninja will redirect your users to your super awesome thank-you page now.

Submission Receipts
-------------------

Ninja's like to leave their calling card. You can too by using *submission receipts* for your forms. Two things need to be present for this to happen. The first is that your form must send a field named `email` containing the email address to send the receipt to, and the second is a `receipt.tpl` file in the forms directory containing the email template to be used for the receipt.

The receipt template replaces `%key%` with the field values where `key` is the name of a submitted field (or internal field). `key` always refers to the unfriendly name of the field. The first two lines of `receipt.tpl` contain the receipt subject and from address respectively.

    Your submission was received
    Support <support@example.com>
    Hi %firstName%,

    Thanks for taking the time to fill out our form. We have received your submission and will respond accordingly. For your records, we've included your information below.

    %firstName% %lastName%
    %streetAddress%
    %city%, %province%, %postal%

    Please reply to this email with any updates for changes.

    Cheers,

    Support

Once the `email` field is included with the submission and the `receipt.tpl` is created for the form, Ninja will automatically start sending submission receipts.

Contribute
----------

Ninja is still in training and likely isn't ready to be set loose on your production forms just yet. If you know of a way to further Ninja's abilities, feel free to send a pull request.
