<?php

declare(strict_types=1);

use \Firebase\JWT\JWT;

final class Flyyer
{
  /**
   * Required. Your project slug.
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
   * Optional. Possible values are `HMAC` or `JWT` depending on which signature strategy you want.
   */
  public $strategy;

  /**
   * Construct a FLYYER AI helper object.
   */
  public function __construct(
    $project,
    $path = '/',
    $variables = [],
    $meta = [],
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
    $aux = explode("&", http_build_query($hash));
    sort($aux);
    return implode("&", $aux);
  }

  /**
   * Get current path with slash at start
   */
  public function path_safe()
  {
    return substr($this->path, 0, 1) === "/" ? $this->path : "/" . $this->path;
  }

  /**
   * Get current params as hash (meta & variables)
   */
  public function params_hash($ignoreV)
  {
    $defaults = [
      "__v" => $this->meta["v"] ?: round(microtime(true)),
      "__id" => $this->meta["id"] ?: null,
      "_w" => $this->meta["width"] ?: null,
      "_h" => $this->meta["height"] ?: null,
      "_res" => $this->meta["resolution"] ?: null,
      "_ua" => $this->meta["agent"] ?: null,
    ];
    if ($ignoreV) {
      unset($defaults["__v"]);
    }
    return array_merge($defaults, $this->variables);
  }

  /**
   * Get final querystring with added '__v' param to force crawlers to update the image.
   */
  public function querystring($ignoreV = false)
  {
    return Flyyer::to_query(array_filter($this->params_hash($ignoreV)));
  }

  /**
   * Signatures
   */
  public function sign()
  {
    if (is_null($this->strategy) and is_null($this->secret)) {
      return '_';
    }
    if (is_null($this->secret)) throw new Exception('Got `strategy` but missing `secret`. You can find it in your project in Advanced settings.');
    if (is_null($this->strategy)) throw new Exception('Got `secret` but missing `strategy`. Valid options are `HMAC` or `JWT`.');

    $key = $this->secret;
    $strategy_lowercased = strtolower($this->strategy);
    if ($strategy_lowercased == "hmac") {
      $data = "{$this->project}{$this->path_safe()}{$this->querystring(true)}";
      $mac = hash_hmac('sha256', $data, $key);
      return substr($mac, 0, 16);
    } elseif ($strategy_lowercased == "jwt") {
      $payload = array("params" => array_filter($this->params_hash(true)), "path" => $this->path_safe());
      return JWT::encode($payload, $key);
    } else {
      throw new Exception('Invalid `strategy`. Valid options are `HMAC` or `JWT`.');
    }
  }

  /**
   * Get final FLYYER AI url. Use this as value (or content) of your <head> tags.
   */
  public function href()
  {
    if (empty($this->project)) throw new Exception('Missing \'project\' property');
    $signature = $this->sign();
    $params = $this->querystring(false);
    $version = $this->meta["v"] ?? round(microtime(true));
    if (is_null($this->strategy) || strtolower($this->strategy) == "hmac") {
      return "https://cdn.flyyer.io/v2/{$this->project}/{$signature}/{$params}{$this->path_safe()}";
    } else {
      return "https://cdn.flyyer.io/v2/{$this->project}/jwt-{$signature}?__v={$version}";
    }
  }
}
