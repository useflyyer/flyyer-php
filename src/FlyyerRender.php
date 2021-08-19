<?php

declare(strict_types=1);

use \Firebase\JWT\JWT;

final class FlyyerRender
{
  /**
   * Visit https://app.flyyer.io to get this value for your project
   */
  public $tenant;
  /**
   * Visit https://app.flyyer.io to get this value for your project
   */
  public $deck;
  /**
   * Visit https://app.flyyer.io to get this value for your project
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
   * Optional. `id` is suggested as your product/post identifier. {id, v, width, height, resolution, agent}
   */
  public $meta;
  /**
   * Optional. Your HMAC secret you can find on your dashboard under Advanced settings for signed requests.
   */
  public $secret;
  /**
   * Optional. Possible values are `HMAC` or `JWT` depending on which signature strategy you want.
   */
  public $strategy;

  /**
   * Construct a FLYYER RENDER helper object.
   */
  public function __construct(
    $tenant,
    $deck,
    $template,
    $version = null,
    $extension = null,
    $variables = [],
    $meta = [],
    $secret = null,
    $strategy = null
  ) {
    $this->tenant = $tenant;
    $this->deck = $deck;
    $this->template = $template;
    $this->version = $version;
    $this->extension = $extension;
    $this->variables = $variables ?: [];
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
    $default_v = ['__v' => round(microtime(true))];
    $defaults = [
      '__id' => $this->meta['id'] ?: null,
      '_w' => $this->meta['width'] ?: null,
      '_h' => $this->meta['height'] ?: null,
      '_res' => $this->meta['resolution'] ?: null,
      '_ua' => $this->meta['agent'] ?: null
    ];
    if ($this->strategy && $this->secret) {
      $key = $this->secret;
      if (strtolower($this->strategy) === 'hmac') {
        $default_query = FlyyerRender::to_query($defaults);
        $data = implode('#', [$this->deck, $this->template, $this->version ?: "", $this->extension ?: "", $default_query]);
        $__hmac = substr(hash_hmac('sha256', $data, $key), 0, 16);
        return FlyyerRender::to_query(array_merge($defaults, $default_v, $this->variables, ['__hmac' => $__hmac]));
      } elseif (strtolower($this->strategy) === 'jwt') {
        $jwt_defaults = [
          'i' => $this->meta['id'] ?: null,
          'w' => $this->meta['width'] ?: null,
          'h' => $this->meta['height'] ?: null,
          'r' => $this->meta['resolution'] ?: null,
          'u' => $this->meta['agent'] ?: null
        ];
        $payload = array_merge(array('d' => $this->deck, 't' => $this->template, 'v' => $this->version, 'e' => $this->extension, 'var' => $this->variables), $jwt_defaults);
        $__jwt = JWT::encode($payload, $key);
        return FlyyerRender::to_query(array_merge([ '__jwt' => $__jwt ], $default_v));
      }
    } else {
      return FlyyerRender::to_query(array_merge($default_v, $this->variables));
    }
  }

  /**
   * Get final FLYYER url. Use this as value (or content) of your <head> tags.
   */
  public function href()
  {
    if (empty($this->tenant)) throw new Exception('Missing \'tenant\' property');
    if (empty($this->deck)) throw new Exception('Missing \'deck\' property');
    if (empty($this->template)) throw new Exception('Missing \'template\' property');
    if ($this->secret && empty($this->strategy)) throw new Exception('Got `secret` but missing `strategy`.  Valid options are `HMAC` or `JWT`.');
    if ($this->strategy && empty($this->secret)) throw new Exception('Got `strategy` but missing `secret`. You can find it in your project in Advanced settings.');
    if ($this->strategy && strtolower($this->strategy) != "jwt" && strtolower($this->strategy) != "hmac") throw new Exception('Invalid signing `strategy`. Valid options are `HMAC` or `JWT`.');

    $base_href = "https://cdn.flyyer.io/r/v2/{$this->tenant}";

    if ($this->strategy && strtolower($this->strategy) === "jwt") {
      return "{$base_href}?{$this->querystring()}";
    }

    $final_href = "{$base_href}/{$this->deck}/{$this->template}";
    if ($this->version) $final_href .= ".{$this->version}";
    if ($this->extension) $final_href .= ".{$this->extension}";
    $final_href .= "?{$this->querystring()}";
    return $final_href;
  }
}
