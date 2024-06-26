<?php

namespace Drupal\Tests\mailchimp\Functional;

/**
 * Tests core API functionality.
 *
 * @group mailchimp
 */
class MailchimpAPITest extends FunctionalMailchimpTestBase {

  /**
   * Tests that the test API has been loaded.
   */
  public function testApi() {
    $mailchimp_api = mailchimp_get_api_object();

    $this->assertNotNull($mailchimp_api);

    $this->assertInstanceOf('Mailchimp\Tests\MailchimpApiUser', $mailchimp_api);
  }

}
