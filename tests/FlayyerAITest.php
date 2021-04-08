<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FlayyerAITest extends TestCase
{
  public function testCanCreateInstance(): void
  {
    $flayyer = new FlayyerAI('project', '/path/to/product');
    $this->assertInstanceOf(FlayyerAI::class, $flayyer);
  }

  public function testCanStringifyHashOfPrimitives(): void
  {
    $hash = ['a' => 'hello', 'b' => 100, 'c' => false, 'd' => null, 'b' => 999];
    $str = FlayyerAI::to_query($hash);
    $this->assertEquals($str, 'a=hello&b=999&c=0');
  }

  public function testCanStringifyComplexHash(): void
  {
    $hash = [
      'a' => ['aa' => 'bar', 'ab' => 'foo'],
      'b' => [['c' => 'foo'], ['c' => 'bar']],
    ];
    $str = FlayyerAI::to_query($hash);
    $this->assertEquals(urldecode($str), 'a[aa]=bar&a[ab]=foo&b[0][c]=foo&b[1][c]=bar');
  }

  public function testEncodesURL(): void
  {
    $flayyer = new FlayyerAI('project', '/path/to/product');
    $href = $flayyer->href();
    $this->assertStringStartsWith('https://flayyer.ai/v2/project/_/_/path/to/product', $href);
  }
}
