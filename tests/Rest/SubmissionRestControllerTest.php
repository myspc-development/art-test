<?php
namespace ArtPulse\Rest\Tests;

use PHPUnit\Framework\TestCase;
use ArtPulse\Rest\SubmissionRestController;

class SubmissionRestControllerTest extends TestCase
{
    public function test_meta_fields_include_names(): void
    {
        $ref = new \ReflectionClass(SubmissionRestController::class);
        $method = $ref->getMethod('get_meta_fields_for');
        $method->setAccessible(true);

        $artist = $method->invoke(null, 'artpulse_artist');
        $org    = $method->invoke(null, 'artpulse_org');

        $this->assertArrayHasKey('artist_name', $artist);
        $this->assertSame('artist_name', $artist['artist_name']);
        $this->assertArrayHasKey('ead_org_name', $org);
        $this->assertSame('ead_org_name', $org['ead_org_name']);
    }

    public function test_endpoint_args_include_names(): void
    {
        $args = SubmissionRestController::get_endpoint_args();
        $this->assertArrayHasKey('artist_name', $args);
        $this->assertSame('string', $args['artist_name']['type']);
        $this->assertArrayHasKey('ead_org_name', $args);
        $this->assertSame('string', $args['ead_org_name']['type']);
    }
}
