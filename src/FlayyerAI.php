<?php

declare(strict_types=1);

final class FlayyerAI
{
  /**
   * Your project slug.
   */
  public $project;
  /**
   * Optional. Requested path on your website, supports query parameters. Root `/` by default.
   */
  public $path;
  /**
   * Optional. Variables for customizing your templates from your code.
   */
  public $variables;
  /**
   * Optional. `id` is suggested as your product/post identifier. {id, v, width, height, resolution, agent}
   */
  public $meta;
  /**
   * Optional. Your HMAC secret you can find on your dashboard under Advanced settings for signed requests.
   */
  public $secret;
  /**
   * Optional. `HMAC` or `JWT` depending on which signature strategy you want.
   */
  public $strategy;

  /**
   * Construct a FLAYYER AI helper object.
   */
  public function __construct(
    $project,
    $path = '/',
    $variables = null,
    $meta = null,
    $secret = null,
    $strategy = null
  ) {
    $this->project = $project;
    $this->path = $path;
    $this->variables = $variables;
    $this->meta = $meta;
    $this->secret = $secret;
    $this->strategy = $strategy;
  }

  /**
   * Stringify variables
   */
  public static function to_query($hash)
  {
    // TODO: add more tests and edge-cases.
    return http_build_query($hash);
  }

  /**
   * Get final querystring with added '__v' param to force crawlers to update the image.
   */
  public function querystring()
  {
    $defaults = ['__v' => round(microtime(true))];
    if (empty($this->variables)) {
      return FlayyerAI::to_query($defaults);
    } else {
      return FlayyerAI::to_query(array_merge($defaults, $this->variables));
    }
  }

  /**
   * Get final FLAYYER url. Use this as value (or content) of your <head> tags.
   */
  public function href()
  {
    if (empty($this->project)) throw new Exception('Missing \'project\' property');

    return "https://flayyer.ai/v2/{$this->project}/_/_{$this->path}";
  }
}
