<?php

require_once 'PHPUnit/Framework/TestCase.php';
var_dump('entra');
require_once dirname(__DIR__).'/lib/lookslike.php';

/**
 * Portrait TestSuite
 **/
class PortraitsTest extends \PHPUnit_Framework_TestCase
{
  public function testSameImagePairInversed()
  {
    $img1 = __DIR__.'/images/misc01.jpg';
    $img2 = __DIR__.'/images/misc02.jpg';

    $obj = new \codeup\lookslike\LooksLike();

    $h1 = $obj->hash($img1);
    $h2 = $obj->hash($img2);

    $this->assertEquals(
      $obj->compare($h1, $h2),
      $obj->compare($h2, $h1)
    );
  }

  /**
   * @dataProvider scaledImageProvider
   */
  public function testImageScaled($img)
  {
    $im = new \Imagick($img);
    $im->resizeImage(500, 500, \Imagick::FILTER_CUBIC, 1);
    $im->writeImage('/tmp/scaled.jpg');

    $obj = new \codeup\lookslike\LooksLike();

    $h1 = $obj->hash($img);
    $h2 = $obj->hash('/tmp/scaled.jpg');

    unlink('/tmp/scaled.jpg');

      var_dump($obj->compare($h1, $h2));
    $this->assertTrue(
      $obj->compare($h1, $h2) > 98.0
    );
  }

  static public function scaledImageProvider()
  {
    $path = __DIR__.'/images/';
    return array(
      array($path.'portrait01.jpg'),
      array($path.'portrait02.jpg'),
      array($path.'portrait03.jpg'),
      array($path.'portrait04.jpg'),
      array($path.'portrait05.jpg'),
      array($path.'portrait06.jpg'),
      array($path.'portrait07.jpg'),
      array($path.'portrait08.jpg'),
      array($path.'portrait09.jpg'),
      array($path.'portrait10.jpg'),
      array($path.'landscape01.jpg'),
      array($path.'landscape02.jpg'),
      array($path.'misc01.jpg'),
      array($path.'misc02.jpg'),
      array($path.'misc03.jpg'),
      array($path.'misc04.jpg'),
      array($path.'misc05.jpg'),
    );
  }
}
