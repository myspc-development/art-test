<?php
namespace ArtPulse\Rest\Tests;

/**
 * @group restapi
 */
class RestRouteAuditCommandTest extends \WP_UnitTestCase
{
    public function set_up()
    {
        parent::set_up();
        // Test-only duplicate routes used to verify conflict detection.
        \register_rest_route('ap/v1', '/conflict', [
            'methods' => 'GET',
            'callback' => '__return_true',
            'permission_callback' => '__return_true',
        ]);
        // \register_rest_route('ap/v1', '/conflict', [
        //     'methods' => 'GET',
        //     'callback' => '__return_false',
        //     'permission_callback' => '__return_true',
        // ]); // Duplicate route for testing removed; retained only one registration.
    }

    public function test_detects_conflict(): void
    {
        $cmd = new \AP_CLI_Rest_Route_Audit();
        $conflicts = $cmd->find_conflicts();
        $found = null;
        foreach ($conflicts as $conflict) {
            if ($conflict['path'] === '/ap/v1/conflict' && $conflict['method'] === 'GET') {
                $found = $conflict;
                break;
            }
        }
        $this->assertNotNull($found, 'Conflict was not detected');
        $this->assertCount(2, $found['callbacks']);
    }

    public function test_json_output(): void
    {
        $cmd = new \AP_CLI_Rest_Route_Audit();
        ob_start();
        $cmd->__invoke([], ['json' => true]);
        $out = ob_get_clean();
        $data = json_decode($out, true);
        $this->assertIsArray($data);
        $this->assertGreaterThan(0, count($data));
    }
}

}

