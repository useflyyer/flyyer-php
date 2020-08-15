<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FlayyerTest extends TestCase
{
  public function testCanCreateInstance(): void
  {
    $flayyer = new Flayyer("tenant", "deck", "template");
    $this->assertInstanceOf(Flayyer::class, $flayyer);
  }

  public function testThrowsIfMissingArguments(): void
  {
    $this->expectException(Exception::class); // TODO: use InvalidArgumentException?
    $flayyer = new Flayyer("tenant", "deck", "template");
    $flayyer->tenant = null;
    $flayyer->href();
  }

  public function testEncodesURL(): void
  {
    $flayyer = new Flayyer("tenant", "deck", "template");
    $flayyer->variables = [
      "title" => "Hello world!"
    ];

    $href = $flayyer->href();
    $this->assertStringStartsWith("https://flayyer.host/v2/tenant/deck/template.jpeg?__v=", $href);
    $this->assertStringEndsWith("&title=Hello+world%21", $href);
  }

  public function testCanStringifyHashOfPrimitives(): void
  {
    $hash = ["a" => "hello", "b" => 100, "c" => false, "d" => null, "b" => 999];
    $str = Flayyer::toQuery($hash);
    $this->assertEquals($str, "a=hello&b=999&c=0");
  }

  public function testCanStringifyComplexHash(): void
  {
    $hash = [
      "a" => ["aa" => "bar", "ab" => "foo"],
      "b" => [["c" => "foo"], ["c" => "bar"]],
    ];
    $str = Flayyer::toQuery($hash);
    $this->assertEquals(urldecode($str), "a[aa]=bar&a[ab]=foo&b[0][c]=foo&b[1][c]=bar");
  }
}
