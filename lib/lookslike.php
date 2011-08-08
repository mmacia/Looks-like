<?php

/**
 * Algorithm to detect similar images, works specially well with
 * rescaled and light modified originals.
 *
 * copyright (c) 2011 by Moisés Maciá
 *
 * permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "software"), to deal
 * in the software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the software, and to permit persons to whom the software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author Moisés Maciá <mmacia@gmail.com>
 */

namespace codeup\lookslike;

class LooksLike
{
  /**
   * Compute the image hash
   *
   * @param string Image file path
   * @return string
   */
  public function hash($file)
  {
    $im = new \Imagick($file);

    // reduce size to 8x8
    $im->resizeImage(8, 8, \Imagick::FILTER_CUBIC, 1);
    // reduce color information to B/W and 64 gray tones
    $im->quantizeImage(64, \Imagick::COLORSPACE_GRAY, 0, false, false);

    // get array of color values, 8x8 = 64 values
    $vect = $this->flattenImageColors($im);

    // average color
    $avg = (int)(array_sum($vect)/64);
    $avghash = (double)0;

    for ($k = 0; $k < 64; ++$k) {
      if ($vect[$k] >= $avg) {
        $avghash |= (1 << (63-$k));
      }
    }

    //printf("%064b\n", $avghash);
    return dechex($avghash);
  }

  /**
   * Compares two hashes and gets the diference (percentage)
   *
   * @param string $h1 string hash in hexadecimal format
   * @param string $h2 string hash in hexadecimal format
   * @return float
   */
  public function compare($hash1, $hash2)
  {
    $distance = $this->hamming($hash1, $hash2);
    return ((64 - $distance)*100)/64;
  }

  /**
   * @param string $h1 string hash in hexadecimal format
   * @param string $h2 string hash in hexadecimal format
   * @return int Hamming distance between two hashes
   */
  protected function hamming($h1, $h2)
  {
    $h1 = gmp_init($h1, 16);
    $h2 = gmp_init($h2, 16);

    // seems that PHP can't handle big numbers, so we need to use GMP extension
    $distance = gmp_hamdist($h1,$h2);

    /*for ($distance = 0, $val = hexdec($h1) ^ hexdec($h2); $val; ++$distance) {
      $val &= $val - 1;
    }*/

    return $distance;
  }

  /**
   * Get an array with all image colors
   *
   * @param Imagick $im Image instance
   * @return array int[]
   */
  protected function flattenImageColors(\Imagick $im)
  {
    $it = $im->getPixelIterator();
    $flat = array();

    foreach ($it as $row => $pixels) {
      foreach ($pixels as $pixel) {
        $rgb = $pixel->getColor();
        $flat[] = (int)(($rgb['r'] >> 16) + ($rgb['g'] >> 8) + ($rgb['b']));
      }
    }

    return $flat;
  }
}
