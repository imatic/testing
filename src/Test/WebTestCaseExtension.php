<?php
namespace Imatic\Testing\Test;

use Imatic\Testing\PHPUnit\Constraint\ResponseHasCode;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
trait WebTestCaseExtension
{
    public static function assertResponseHasCode($code, Response $response, $message = '')
    {
        self::assertThat($code, new ResponseHasCode($response), $message);
    }
}
