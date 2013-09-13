NegotiationServiceProvider
==========================

A [Negotiation](https://github.com/willdurand/Negotiation/) service provider for [Silex](http://silex.sensiolabs.org/).


## Usage

Initialize the service provider using `register()` method:

```php
<?php

use KPhoen\Provider\NegotiationServiceProvider;

$app->register(new NegotiationServiceProvider());
// or with custom formats, which will be injected in the format negotiator and
// in the request
$app->register(new NegotiationServiceProvider(array(
    'gpx' => array('application/gpx+xml'),
    'kml' => array('application/vnd.google-earth.kml+xml', 'application/vnd.google-earth.kmz'),
)));
```

Then use it in your controllers:

```php
<?php

$app->get('/hello', function() use ($app) {
    $negotiator = $app['negotiator'];

    // do your stuff
});
```


## Configuration

The service provider creates the following services:

  * `negotiator`: a Negotiator instance ;
  * `format.negotiator`: a FormatNegotiator instance ;
  * `language.negotiator`: a LanguageNegotiator instance.


## Installation

Install the NegotiationServiceProvider adding `kphoen/negotiation-service-provider` to your composer.json or from CLI:

```
$ php composer.phar require 'kphoen/negotiation-service-provider:~1.0'
```


## Licence

This provider is released under the MIT license.
