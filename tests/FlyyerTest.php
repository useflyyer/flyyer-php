<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use \Firebase\JWT\JWT;

final class FlyyerTest extends TestCase
{
  public function testCanCreateInstance(): void
  {
    $flyyer = new Flyyer('project', '/path/to/product');
    $this->assertInstanceOf(Flyyer::class, $flyyer);
  }

  public function testCanStringifyHashOfPrimitives(): void
  {
    $hash = ['a' => 'hello', 'b' => 100, 'c' => false, 'd' => null, 'b' => 999];
    $str = Flyyer::to_query($hash);
    $this->assertEquals($str, 'a=hello&b=999&c=0');
  }

  public function testCanStringifyComplexHash(): void
  {
    $hash = [
      'a' => ['aa' => 'bar', 'ab' => 'foo'],
      'b' => [['c' => 'foo'], ['c' => 'bar']],
    ];
    $str = Flyyer::to_query($hash);
    $this->assertEquals(urldecode($str), 'a[aa]=bar&a[ab]=foo&b[0][c]=foo&b[1][c]=bar');
  }

  public function testEncodesURLHappyPath(): void
  {
    $flyyer = new Flyyer('project', '/path/to/product', ['title' => 'Hello world!', 'description' => null, 'img' => 'https://image.com'], ['id' => 'dev forgot to slugify', 'width' => '100', 'height' => 200, 'v' => '2', 'resolution' => 0.9]);
    $href = $flyyer->href();
    $this->assertEquals('https://cdn.flyyer.io/v2/project/_/__id=dev+forgot+to+slugify&__v=2&_h=200&_res=0.9&_w=100&img=https%3A%2F%2Fimage.com&title=Hello+world%21/path/to/product', $href);
  }

  public function testEncodesURLDefaultValues(): void
  {
    $flyyer = new Flyyer('project');
    $href = $flyyer->href();
    $this->assertMatchesRegularExpression('/https:\/\/cdn.flyyer.io\/v2\/project\/_\/__v=\d+/', $href);
  }

  public function testEncodesURLMissingSlashAtStart(): void
  {
    $flyyer = new Flyyer('project', 'path/to/product');
    $href = $flyyer->href();
    $this->assertMatchesRegularExpression('/https:\/\/cdn.flyyer.io\/v2\/project\/_\/__v=\d+\/path\/to\/product/', $href);
  }

  public function testEncodesURLWithQueryParams(): void
  {
    $flyyer = new Flyyer('project', '/path/to/collection?sort=price');
    $href = $flyyer->href();
    $this->assertMatchesRegularExpression('/https:\/\/cdn.flyyer.io\/v2\/project\/_\/__v=\d+\/path\/to\/collection\?sort=price/', $href);
  }

  public function testEncodesURLWithHmacSignature(): void
  {
    $flyyer = new Flyyer('project', '/collections/col', ['title' => 'Hello world!'], ['id' => 'dev forgot to slugify', 'width' => '100', 'height' => 200], 'sg1j0HVy9bsMihJqa8Qwu8ZYgCYHG0tx', 'HMAC');
    $href = $flyyer->href();
    $this->assertMatchesRegularExpression('/https:\/\/cdn.flyyer.io\/v2\/project\/361b2a456daf8415\/__id=dev\+forgot\+to\+slugify&__v=\d+&_h=200&_w=100&title=Hello\+world%21\/collections\/col/', $href);
  }

  public function testEncodesURLWithJWTAndDefaultValues(): void
  {
    $key = 'sg1j0HVy9bsMihJqa8Qwu8ZYgCYHG0tx';
    $flyyer = new Flyyer('project', '/', [], [], $key, 'JWT');
    $href = $flyyer->href();
    $matches = array();
    preg_match('/(jwt-)(.*)(\?)/', $href, $matches);
    $token = $matches[2];
    $payload = JWT::decode($token, $key, array('HS256'));
    $this->assertEquals(array(), $payload->params);
    $this->assertEquals("/", $payload->path);
    $this->assertMatchesRegularExpression('/https:\/\/cdn.flyyer.io\/v2\/project\/jwt-.*\?__v=\d/', $href);
  }

  public function testEncodesURLWithJWTWithMeta(): void
  {
    $key = 'sg1j0HVy9bsMihJqa8Qwu8ZYgCYHG0tx';
    $flyyer = new Flyyer('project', '/collections/col', [], ['id' => 'dev forgot to slugify', 'width' => '100', 'height' => 200, 'v' => '2', 'resolution' => 0.9], $key, 'JWT');
    $matches = array();
    preg_match('/(jwt-)(.*)(\?)/', $flyyer->href(), $matches);
    $token = $matches[2];
    $payload = JWT::decode($token, $key, array('HS256'));
    $this->assertEquals('dev forgot to slugify', $payload->params->__id);
    $this->assertEquals('100', $payload->params->_w);
    $this->assertEquals(200, $payload->params->_h);
    $this->assertEquals(0.9, $payload->params->_res);
    $this->assertEquals("/collections/col", $payload->path);
  }

  public function testEncodesURLWithPathMissingSlashAtStart(): void
  {
    $key = 'sg1j0HVy9bsMihJqa8Qwu8ZYgCYHG0tx';
    $flyyer = new Flyyer('project', 'collections/col', ['title' => 'Hello world!'], ['id' => 'dev forgot to slugify'], $key, 'JWT');
    $matches = array();
    preg_match('/(jwt-)(.*)(\?)/', $flyyer->href(), $matches);
    $token = $matches[2];
    $payload = JWT::decode($token, $key, array('HS256'));
    $this->assertEquals('dev forgot to slugify', $payload->params->__id);
    $this->assertEquals('Hello world!', $payload->params->title);
    $this->assertEquals("/collections/col", $payload->path);
  }
}
