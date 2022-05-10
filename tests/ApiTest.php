<?php

namespace Michaelr0\LOIA\Tests;

use Michaelr0\LOIA\ImageBuilderApi;

class ApiTest extends \Orchestra\Testbench\TestCase
{
    /** @test */
    public function canRequestBuild()
    {
        $request = ImageBuilderApi::build('bcm27xx/bcm2711', 'rpi-4', 'SNAPSHOT');

        if (!empty($request['status']) && in_array($request['status'], [200, 202])) {
            $result = true;
        } else {
            $result = false;
        }

        $this->assertTrue($result);
    }

    /** @test */
    public function canGetOverview()
    {
        $request = ImageBuilderApi::overview(false);

        $this->assertTrue(isset($request['branches']));

        $this->assertTrue(isset($request['latest']));
        $this->assertTrue(in_array('SNAPSHOT', $request['latest']));
    }

    /** @test */
    public function canGetOverviewFromStatic()
    {
        $request = ImageBuilderApi::overview();

        $this->assertTrue(isset($request['latest']));
        $this->assertTrue(in_array('SNAPSHOT', $request['latest']));
    }

    /** @test */
    public function canGetRevision()
    {
        $request = ImageBuilderApi::revision('SNAPSHOT', 'bcm27xx', 'bcm2711');

        $this->assertTrue(isset($request['revision']));
        $this->assertNotEmpty($request['revision']);
    }
}
