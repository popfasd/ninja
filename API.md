API
===

Ninja provides a JSON-based API for accessing form and submission data. The
only endpoint accessible without authentication is `/api/auth`, which allows
clients to authenticate and obtain an authentication token. This token must be
proided when accessing all other endpoints.

To authenticate, clients must send a POST request with a `user` and `pass`
parameter.

```
POST /api/auth

user=username&pass=password
```

If successful, the response body will simply contain a JSON Web Token (JWT) for
use with subsequent requests.

If authentication is unsuccesful, a 401 Unauthorized response will be returned.
The 401 response will also be returned when accessing and endpoint without a
valid authentication token.

Get a list of forms
-------------------

```
GET /api/forms
```

Key    | Type   | Contents
-------|--------|---------
status | string | `success` or `failure`
href   | string | The current endpoint URI
forms  | object | An object containing keys for each form

Items in `forms` will be keyed with the form ID (SHA1 hash of the form URI), 
and contain a URI of the API enpoint for accessing form submissions.

Get details for a specific form
-------------------------------

```
GET /api/forms/{formId}
```

Key    | Type   | Contents
-------|--------|---------
status | string | `success` or `failure`
href   | string | The current endpoint URI
form   | object | An object containing all the form attributes

Form attributes:

Key         | Type   | Contents
------------|--------|---------
id          | string | The form ID
url         | string | The form URI
submissions | string | The API URI for accessing the form's submissions

Get a list of form submissions
------------------------------

```
GET /api/forms/{formId}/submissions
```

Key         | Type   | Contents
------------|--------|---------
status      | string | `success` or `failure`
href        | string | The current endpoint URI
form        | string | The API URI for accessing form details
submissions | object | An object containing keys for each submission for the form

Items in `submissions` will be keyed with the submission ID and contain an object
with keys for each field in the form.

Export form submissions
-----------------------

```
GET /api/forms/{formId}/export
```

Export the form's submissions as a CSV file.

