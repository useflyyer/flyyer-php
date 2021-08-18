<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use \Firebase\JWT\JWT;

final class FlyyerRenderTest extends TestCase
{
  public function testCanCreateInstance(): void
  {
    $flyyer = new FlyyerRender('tenant', 'deck', 'template');
    $this->assertInstanceOf(FlyyerRender::class, $flyyer);
  }

  public function testCanStringifyHashOfPrimitives(): void
  {
    $hash = ['a' => 'hello', 'b' => 100, 'c' => false, 'd' => null, 'b' => 999];
    $str = FlyyerRender::to_query($hash);
    $this->assertEquals($str, 'a=hello&b=999&c=0');
  }

  public function testCanStringifyComplexHash(): void
  {
    $hash = [
      'a' => ['aa' => 'bar', 'ab' => 'foo'],
      'b' => [['c' => 'foo'], ['c' => 'bar']],
    ];
    $str = FlyyerRender::to_query($hash);
    $this->assertEquals(urldecode($str), 'a[aa]=bar&a[ab]=foo&b[0][c]=foo&b[1][c]=bar');
  }

  public function testThrowsIfMissingArguments(): void
  {
    $this->expectException(Exception::class); // TODO: use InvalidArgumentException?
    $flyyer = new FlyyerRender('tenant', 'deck', 'template');
    $flyyer->tenant = null;
    $flyyer->href();
  }

  public function testThrowsIfMissingSignatureArguments(): void
  {
    $this->expectException(Exception::class); // TODO: use InvalidArgumentException?
    $flyyer = new FlyyerRender('tenant', 'deck', 'template');
    $flyyer->secret = null;
    $flyyer->strategy = "HMAC";
    $flyyer->href();
  }

  public function testThrowsIfMissingSignatureArguments2(): void
  {
    $this->expectException(Exception::class); // TODO: use InvalidArgumentException?
    $flyyer = new FlyyerRender('tenant', 'deck', 'template');
    $flyyer->secret = "sg1j0HVy9bsMihJqa8Qwu8ZYgCYHG0tx";
    $flyyer->strategy = null;
    $flyyer->href();
  }

  public function testEncodesURL(): void
  {
    $flyyer = new FlyyerRender('tenant', 'deck', 'template');
    $href = $flyyer->href();
    $this->assertStringStartsWith('https://cdn.flyyer.io/r/v2/tenant/deck/template.jpeg?__v=', $href);
    $flyyer->variables = [
      'title' => 'Hello world!'
    ];

    $href = $flyyer->href();
    $this->assertStringStartsWith('https://cdn.flyyer.io/r/v2/tenant/deck/template.jpeg?__v=', $href);
    $this->assertStringEndsWith('&title=Hello+world%21', $href);
  }

  public function testEncodesURLWithHmacSignature(): void
  {
    $flyyer = new FlyyerRender('tenat', 'deck', 'template');
    $flyyer->variables = ['title' => 'Hello world!'];
    $flyyer->extension = 'jpeg';
    $flyyer->strategy = 'HMAC';
    $flyyer->secret = 'sg1j0HVy9bsMihJqa8Qwu8ZYgCYHG0tx';
    $href = $flyyer->href();
    $this->assertMatchesRegularExpression('/https:\/\/cdn.flyyer.io\/r\/v2\/tenant\/deck\/template.jpeg\?__v=\d+&title=Hello\+world%21&__hmac=6b631ae8c4ca2977/', $href);
  }

  public function testEncodesURLWithJWTAndDefaultValues(): void
  {
    $key = 'sg1j0HVy9bsMihJqa8Qwu8ZYgCYHG0tx';
    $flyyer = new FlyyerRender('tenant', 'deck', 'template');
    $flyyer->variables = ['title' => 'Hello world!'];
    $flyyer->version = 4;
    $flyyer->extension = 'jpeg';
    $flyyer->strategy = 'JWT';
    $flyyer->secret = $key;
    $href = $flyyer->href();
    $matches = array();
    preg_match('/(jwt=)(.*?)(&?)/', $href, $matches);
    $token = $matches[2];
    $payload = JWT::decode($token, $key, array('HS256'));
    $check = [
      "d"=>"deck",
      "t"=>"template",
      "v"=>4,
      "e"=>"jpeg",
      "i"=>null,
      "w"=>null,
      "h"=>null,
      "r"=>null,
      "u"=>null,
      "var"=>[
          "title"=>"Hello world!",
      ]
    ];
    $this->assertEquals($payload, $check);
    $this->assertMatchesRegularExpression('/https:\/\/cdn.flyyer.io\/r\/v2\/tenant\?__jwt=.*\?__v=\d+/', $href);
  }

  public function testEncodesURLWithJWTWithMeta(): void
  {
    $key = 'sg1j0HVy9bsMihJqa8Qwu8ZYgCYHG0tx';
    $flyyer = new FlyyerRender('tenant', 'deck', 'template');
    $flyyer->meta = [
      "agent" => "whatsapp",
      "id" => "dev forgot to slugify",
      "width" => "100",
      "height"=> 200,
    ];
    $flyyer->strategy = 'JWT';
    $flyyer->secret = $key;
    $href = $flyyer->href();
    $matches = array();
    preg_match('/(jwt=)(.*?)(&?)/', $href, $matches);
    $token = $matches[2];
    $payload = JWT::decode($token, $key, array('HS256'));
    $check = [
      "d"=>"deck",
      "t"=>"template",
      "v"=>4,
      "e"=>"jpeg",
      "i"=>null,
      "w"=>null,
      "h"=>null,
      "r"=>null,
      "u"=>null,
      "var"=>[
          "title"=>"Hello world!",
      ]
    ];
    $this->assertEquals($payload, $check);
    $this->assertMatchesRegularExpression('/https:\/\/cdn.flyyer.io\/r\/v2\/tenant\?__jwt=.*\?__v=\d+/', $href);
  }
}
