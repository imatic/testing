<?php

namespace Imatic\Bundle\TestingBundle\Test;

use Imatic\Bundle\TestingBundle\PHPUnit\Constraint\ResponseHasCode;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
trait TestCaseExtension
{
    public static function assertResponseHasCode($code, Response $response, $message = '')
    {
        self::assertThat($code, new ResponseHasCode($response), $message);
    }
}
