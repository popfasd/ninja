Ninja
=====

Ninja is a *sneaky* HTML form processor. Like any good Ninja, it operates
inconspicously, is adaptable, and requires little instruction. After some simple
configuration you can point any HTML form at Ninja and it will automatically
start collecting submissions and notifying you by email of each submission.

Copy `private/config/app.dist.yaml` to `private/config/app.yaml` and make sure
the following values are set appropriately.

```yaml
mailto: [ 'your@address.com] ]
uriPrefix: '/path/to/ninja'
```

Now update your `form` tag's `action` and `method` attributes. Ninja only likes
POST requests.

```html
<form action="/path/to/ninja/index.php?q=/submit" method="post">
```

Each form is given an ID which is derived from the SHA1 hash of the form URL.
All form data and configuration is stored in `private/cache/<id>` by default.
The first time a form is submitted to Ninja, it creates the form's directory and
drops a `settings.yaml` file in the directory which includes a key called `url`
which containers the form's URL. Submissions are stored in
`private/cache/<formId>/submissions` as serialized arrays.

Friendly Field Names
--------------------

The fields in each submission are named after the field names submitted in the
POST request. If you want to make these field names a little more human-
friendly you can assign them in the `settings.yaml` file for the form. Add a
`fields` key with the submitted field name as the array key and the friendly
name as the key's value.

```yaml
fields:
    field1_firstName: 'First name'
    street_addr: 'Street address'
    zip_postal: 'Zip/Postal code'
```

Keep in mind that once `fields` is defined, Ninja will only collect information
for the fields that have been defined. In this way, you can also use `fields`
to filter out fields you don't want to collect.

Say Thank You
-------------

By default users are redirected to Ninja's included "thank you" page
`/path/to/ninja/public/thanks.html`. Ninja's don't care much for fancy things,
and you may find Ninja's thank-you page a little too ausetre. Fortunately, you
can change this using `nexturl` in `settings.yaml`.

```yaml
nexturl: 'http://example.com/super-awesome-thanks.html'
```

Ninja will redirect your users to your super-awesome thank-you page now.

Submission Receipts
-------------------

Ninja's like to leave their calling card (wait, can you be sneaky *and* leave a
calling card?). You can too by using *submission receipts* for your forms. Two
things need to be present for this to happen. The first is that your form must
send a field named `email` containing the email address to send the receipt to,
and the second is a `receipt.tpl` file in the forms directory containing the
email template to be used for the receipt.

The receipt template replaces `%key%` with the field values where `key` is the
name of a submitted field (or internal field). `key` always refers to the
unfriendly name of the field. The first two lines of `receipt.tpl` contain the
receipt subject and from address respectively.

```
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
```

Once the `email` field is included with the submission and the `receipt.tpl` is
created for the form, Ninja will automatically start sending submission
receipts.

Validation
----------

Just as a ninja must take care to strike at the right target, you too can ensure
that your forms collect the right data. Validation rules can be added to
`settings.yaml` to faciliate this. Rules consist of regular expressions which are
applied to the corresponding field.

```yaml
validationRules:

    # make sure firstName is included
    firstName: true

    # check for valid Canadian postal code (i.e. A1A 1A1)
    postal: '/[A-Z][0-9][A-Z]\s[0-9][A-Z][0-9]/'
```

If a submission fails validation, the user is redirect back to the form, with
the validation results passed in the query string in the `__nv` key. This data
is stored as a base64-encoded JSON string. The JSON object contains a key for
each field that failed validation with a reason for the failure. Reasons are one
of:

    - `not received` - the field wasn't included with the request
    - `empty` - the field was empty
    - `failed` - the field failed to match the validation rule

An example of a form URL that contains validation results:

```
http://example.com/myform.html?__nv=eyJmaWVsZEIiOiJmYWlsZWQifQ%3D%3D
```

If you're worried now that when the form is re-submitted your form's ID will
change (because the referer will include the validation results), don't worry.
Ninja is wise, and will strip the validation results from the URL before
processing.

If `__nv` conflicts with other keys in your query string, you can change this to
something else by changing `validationKey` in `private/config/app.yaml`.

Contribute
----------

Ninja is still in training and likely isn't ready to be set loose on your
production forms just yet. If you know of a way to further Ninja's abilities,
feel free to send a pull request.
