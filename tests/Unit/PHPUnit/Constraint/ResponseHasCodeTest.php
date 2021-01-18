<?php declare(strict_types=1);
namespace Imatic\Testing\Test\Unit\PHPUnit\Constraint;

use Imatic\Testing\PHPUnit\Constraint\ResponseHasCode;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
class ResponseHasCodeTest extends TestCase
{
    public function testValidResponseShouldReturnTrue()
    {
        $constraint = new ResponseHasCode(new Response('content', 200));
        $this->assertTrue($constraint->evaluate(200, '', true));
    }

    public function testInvalidResponseShouldReturnFalse()
    {
        $constraint = new ResponseHasCode(new Response('content', 200));
        $this->assertFalse($constraint->evaluate(404, '', true));
    }

    public function testInvalidResponseShouldTrowExceptionWithContent()
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Failed asserting that 404 is equal to 200 (response: "content").');

        $constraint = new ResponseHasCode(new Response('content', 200));
        $constraint->evaluate(404);
    }
}
