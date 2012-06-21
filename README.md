PHP Curl Queue Class
====================

A queue based curl wrapping class. Processes curl GET and POST requests from a queue in batches. Output is handled by callback functions which can be set globally for all requests or individually for each request. NOT READY FOR PRODUCTION USE, STILL BEING DEVELOPED.

Features
--------

- Efficient queue based processing of HTTP requests.
- Easy to use interface.
- Customizeable window size defines number of requests that are processed in parallel at any one time.
- Ability to define per request or global output callback functions.
- Ability to customize the curl options for each request.
- Ability to choose either GET or POST for each request.

Requirements
------------

(coming soon)


Configurable Options
--------------------

- window: sets how many requests will be processed at the same time
- timeout: maximum timeout in seconds
- callback: the function to call when a request finishes, must be of type function($output,$info)

Adding a Request
----------------

(coming soon)

Custom Headers
--------------

(coming soon)

Custom Curl Options
-------------------

(coming soon)

Custom Callback
---------------

(coming soon)

Legal
-----

Copyright (c) 2011, Matt Colf

Permission to use, copy, modify, and/or distribute this software for any
purpose with or without fee is hereby granted, provided that the above
copyright notice and this permission notice appear in all copies.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.

