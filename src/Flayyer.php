<?php

declare(strict_types=1);

final class Flayyer
{
  /**
   * Visit https://app.flayyer.com to get this value for your project
   */
  public $tenant;
  /**
   * Visit https://app.flayyer.com to get this value for your project
   */
  public $deck;
  /**
   * Visit https://app.flayyer.com to get this value for your project
   */
  public $template;
  /**
   * Optional. Leave empty to always grab the latest version.
   */
  public $version;
  /**
   * "jpeg" | "png" | "webp"
   */
  public $extension;
  /**
   * JS serializable variables.
   */
  public $variables;

  /**
   * Construct a FLAYYER helper object.
   */
  public function __construct(
    $tenant,
    $deck,
    $template,
    $version = null,
    $extension = "jpeg",
    $variables = []
  ) {
    $this->tenant = $tenant;
    $this->deck = $deck;
    $this->template = $template;
    $this->version = $version;
    $this->extension = $extension;
    $this->variables = $variables;
  }

  /**
   * Stringify variables
   */
  public static function toQuery($hash)
  {
    // TODO: add more tests and edge-cases.
    return http_build_query($hash);
  }

  /**
   * Get final querystring with added "__v" param to force crawlers to update the image.
   */
  public function querystring()
  {
    $defaults = ["__v" => round(microtime(true))];
    if (is_null($this->variables)) {
      return Flayyer::toQuery($defaults);
    } else {
      return Flayyer::toQuery(array_merge($defaults, $this->variables));
    }
  }

  /**
   * Get final FLAYYER url. Use this as value (or content) of your <head> tags.
   */
  public function href()
  {
    if (is_null($this->tenant)) throw new Exception("Missing 'tenant' property");
    if (is_null($this->deck)) throw new Exception("Missing 'deck' property");
    if (is_null($this->template)) throw new Exception("Missing 'template' property");

    $query = $this->querystring();
    if ($this->version) {
      return "https://flayyer.host/v2/{$this->tenant}/{$this->deck}/{$this->template}.{$this->version}.{$this->extension}?{$query}";
    }
    return "https://flayyer.host/v2/{$this->tenant}/{$this->deck}/{$this->template}.{$this->extension}?{$query}";
  }
}
