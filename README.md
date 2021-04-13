# flayyer-php

This package is agnostic to any PHP framework.

This package helps you with the Flayyer integration. We assume you already have a Flayyer template or project. If you don't have one, please refer to: [flayyer.com](https://flayyer.com?ref=flayyer-php).

## Installation

This package supports **>= PHP 7.1**.

```sh
composer require flayyer/flayyer

# or with a specific version
composer require flayyer/flayyer:0.1.2
```

## Usage

### Flayyer.ai

After installing this package you can create image URLs like the following:

```php
$flayyer = new FlayyerAI(
  // [Required] Your project slug, find it in your dashboard https://flayyer.com/dashboard/.
  'website-com',
  // [Recommended] The current path of your website (root is taken by default).
  '/path/to/product',
  // [Optional] In case you want to provide information that is not present in your page set it here, otherwise just leave it empty `[]` (our AI system gets the info present in your page for your preview).
  [
    'title' => 'Product name',
    'img' => 'https://flayyer.com/img/marketplace/flayyer-banner.png'
  ],
  // [Recommended] You can use your post/product SKU or any identifier you want. We use this for providing you better statistics.
  [
    'id' => 'jeans-123',
  ]);

// Use this image in your <head/> tags (og:image, twitter:image, and others)
$url = $flayyer->href();
// > https://flayyer.ai/v2/website-com/_/__id=jeans-123&__v=1618281823&img=https%3A%2F%2Fflayyer.com%2Fimg%2Fmarketplace%2Fflayyer-banner.png&title=Product+name/path/to/product
```

If you want signed URLs, just provide your secret (find it in Dashboard > Project > Advanced settings) and choose a strategy (`HMAC` or `JWT`).

```php
$flayyer = new FlayyerAI(
  'website-com',
  '/path/to/product',
  [],
  [ 'id' => 'jeans-123' ],
  'your-secret-key',
  'JWT', // or 'HMAC'
);

$url = $flayyer->href();
// > https://flayyer.ai/v2/website-com/jwt-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJwYXJhbXMiOnsiX19pZCI6ImplYW5zLTEyMyJ9LCJwYXRoIjoiXC9wYXRoXC90b1wvcHJvZHVjdCJ9.X8Vs5SGEA1-3M6bH-h24jhQnbwH95V_G0f-gPhTBTzE?__v=1618283086
```

### Flayyer.io

After installing this package you can format URLs just like this example:

```php
$flayyer = new Flayyer("tenant", "deck", "template");
$flayyer->variables = [
  "title" => "Hello world!"
];

// Use this image in your <head/> tags
$url = $flayyer->href();
// > https://flayyer.io/v2/tenant/deck/template.jpeg?__v=1596906866&title=Hello+world%21
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
// > https://flayyer.io/v2/tenant/deck/template.jpeg?title=Hello+world!&__v=123
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
