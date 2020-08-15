# flayyer-php

This package is agnostic to any PHP framework and has zero dependencies.

To create a FLAYYER template please refer to: [flayyer.com](https://flayyer.com?ref=flayyer-php)

## Installation

This package supports **>= PHP 7.1**.

```sh
composer install flayyer/flayyer
```

## Usage

After installing this package you can format URLs just like this example:

```php
$flayyer = new Flayyer("tenant", "deck", "template");
$flayyer->variables = [
  "title" => "Hello world!"
];

// Use this image in your <head/> tags
$url = $flayyer->href();
// > https://flayyer.host/v2/tenant/deck/template.jpeg?__v=1596906866&title=Hello+world%21
```

Variables can be complex arrays and objects.

```php
$flayyer->variables = [
  "items" => [
    ["text" => "Oranges", "count" => 12],
    ["text" => "Apples", "count" => 14]
  ]
];
```

**IMPORTANT: variables must be serializable.**

To decode the URL for debugging purposes:

```php
print(urldecode($url));
// > https://flayyer.host/v2/tenant/deck/template.jpeg?title=Hello+world!&__v=123
```

## Development

Prepare the local environment:

```sh
composer install
```

## Test

Run PHPUnit with:

```sh
composer dump-autoload
composer test
```
