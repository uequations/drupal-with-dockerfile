<?php
declare(strict_types=1);
/**
 * This file tests HtmlContent.
 */

namespace SendGrid\Tests;

use PHPUnit\Framework\TestCase;
use SendGrid\Mail\HtmlContent;

/**
 * This file tests HtmlContent.
 *
 * @package SendGrid\Tests
 */
class HtmlContentTest extends TestCase {

  public function testConstructor(): void {
    $htmlContent = new HtmlContent('html_content');
    $this->assertInstanceOf(HtmlContent::class, $htmlContent);
    $this->assertSame('html_content', $htmlContent->getValue());
    $this->assertSame('text/html', $htmlContent->getType());
  }
}
