# flayyer-php

The AI-powered preview system built from your website (no effort required).

[![Flayyer live image](https://github.com/flayyer/create-flayyer-app/blob/master/.github/assets/website-to-preview.png?raw=true&v=1)](https://flayyer.ai/v2/spikeball-cl/_/products/spikeball-hoodie)

**This package is agnostic to any PHP framework.**

## Index

- [Get started (5 minutes)](#get-started-5-minutes)
- [Advanced usage](#advanced-usage)
- [Flayyer.io - Take control of everything](#flayyerio)
- [Development](#development)
- [Test](#test)

## Get started (5 minutes)

Haven't registered your website yet? Go to [Flayyer.com](https://flayyer.com?ref=flayyer-php) and create a project (e.g. `website-com`).

### 1. Install the library

This package supports PHP >= 7.1.

```sh
composer require flayyer/flayyer
```

### 2. Get your Flayyer.ai smart image link

In your website code (e.g. your landing or product/post view file), set the following:

```php
$flayyer = new FlayyerAI(
  // Your project slug
  'website-com',
  // The current path of your website
  '/path/to/product', // in Laravel 6 you can use `Route::getCurrentRoute()->getName()`
);

// Check:
print($flayyer->href());
// > https://flayyer.ai/v2/website-com/_/__v=1618281823/path/to/product
```

### 3. Put your smart image link in your `<head>` tags

You'll get the best results like this:

```php
<meta property="og:image" content="{{ $flayyer->href() }} ">
<meta name="twitter:image" content="{{ $flayyer->href() }} ">
<meta name="twitter:card" content="summary_large_image">
```

### 4. Create a `rule` for your project

Login at [Flayyer.com](https://flayyer.com?ref=flayyer-php) > Go to your Dashboard > Manage rules and create a rule like the following:

[![Flayyer basic rule example](https://github.com/flayyer/create-flayyer-app/blob/master/.github/assets/rule-example.png?raw=true&v=1)](https://flayyer.com/dashboard)

Voilà!

## Advanced usage

Here you have a detailed full example for project `website-com` and path `/path/to/product`.

Advanced features include:

- Custom variables: additional information for your preview that is not present in your website.
- Custom metadata: set custom width, height, resolution, and more (see example).
- Signed URLs.

```php
$flayyer = new FlayyerAI(
  // [Required] Your project slug, find it in your dashboard https://flayyer.com/dashboard/.
  'website-com',
  // [Recommended] The current path of your website (by default it's `/`).
  '/path/to/product',
  // [Optional] In case you want to provide information that is not present in your page set it here.
  [
    'title' => 'Product name',
    'img' => 'https://flayyer.com/img/marketplace/flayyer-banner.png'
  ],
  // [Optional] Custom metadata for rendering the image. ID is recommended so we provide you with better statistics.
  [
    'id' => 'jeans-123', // recommended for better stats
    'v' => '12369420123', // specific handler version, by default it's a random number to circumvent platforms' cache,
    'width' => 1200,
    'height' => 600,
    'resolution' => 0.9,
    'agent' => 'whatsapp', // this would force a square image
  ]);

// Use this image in your <head/> tags (og:image & twitter:image)
print($flayyer->href());
// > https://flayyer.ai/v2/website-com/_/__id=jeans-123&__v=1618281823&img=https%3A%2F%2Fflayyer.com%2Fimg%2Fmarketplace%2Fflayyer-banner.png&title=Product+name/path/to/product
```

For signed URLs, just provide your secret (find it in Dashboard > Project > Advanced settings) and choose a strategy (`HMAC` or `JWT`).

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

### Flayyer.io - Take control of everything

As you probably realized, Flayyer.ai uses the [rules defined on your dashboard](https://flayyer.com/dashboard/_/projects) to decide how to handle every image based on path patterns, then fetches and analyse the website for variables and information to render the image. Let's say _"render images based on the content of this route"_.

Flayyer.io instead requires you to explicitly declare template and variables for the images to render, giving you more control for customization. Let's say _"render an image with using this template and these explicit variables"_.


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
