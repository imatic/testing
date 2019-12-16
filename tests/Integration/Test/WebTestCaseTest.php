<?php declare(strict_types=1);
namespace Imatic\Testing\Test\Integration\Test;

use Imatic\Testing\Test\WebTestCase;

class WebTestCaseTest extends WebTestCase
{
    public function testWithoutErrors()
    {
        $this->assertTrue(true);
    }
}
