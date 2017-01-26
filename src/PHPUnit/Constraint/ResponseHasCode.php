<?php

namespace Imatic\Testing\PHPUnit\Constraint;

use PHPUnit_Framework_Constraint;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
class ResponseHasCode extends PHPUnit_Framework_Constraint
{
    /**
     * @var Response
     */
    private $response;

    public function __construct(Response $response)
    {
        parent::__construct();
        $this->response = $response;
    }

    protected function matches($other)
    {
        return $this->response->getStatusCode() === $other;
    }

    public function toString()
    {
        return sprintf('is equal to %s (response: "%s")', $this->response->getStatusCode(), $this->response->getContent());
    }
}
