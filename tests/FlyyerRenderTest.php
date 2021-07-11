<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FlyyerRenderTest extends TestCase
{
  public function testCanCreateInstance(): void
  {
    $flyyer = new FlyyerRender('tenant', 'deck', 'template');
    $this->assertInstanceOf(FlyyerRender::class, $flyyer);
  }

  public function testThrowsIfMissingArguments(): void
  {
    $this->expectException(Exception::class); // TODO: use InvalidArgumentException?
    $flyyer = new FlyyerRender('tenant', 'deck', 'template');
    $flyyer->tenant = null;
    $flyyer->href();
  }

  public function testEncodesURL(): void
  {
    $flyyer = new FlyyerRender('tenant', 'deck', 'template');
    $href = $flyyer->href();
    $this->assertStringStartsWith('https://cdn.flyyer.io/render/v2/tenant/deck/template.jpeg?__v=', $href);

    $flyyer->variables = [
      'title' => 'Hello world!'
    ];

    $href = $flyyer->href();
    $this->assertStringStartsWith('https://cdn.flyyer.io/render/v2/tenant/deck/template.jpeg?__v=', $href);
    $this->assertStringEndsWith('&title=Hello+world%21', $href);
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
}
