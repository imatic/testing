<?php
namespace Imatic\Testing\Test\Unit\PHPUnit\Constraint;

use Imatic\Testing\PHPUnit\Constraint\ResponseHasCode;
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

    /**
     * @expectedException \PHPUnit\Framework\ExpectationFailedException
     * @expectedExceptionMessage Failed asserting that 404 is equal to 200 (response: "content").
     */
    public function testInvalidResponseShouldTrowExceptionWithContent()
    {
        $constraint = new ResponseHasCode(new Response('content', 200));
        $constraint->evaluate(404);
    }
}
