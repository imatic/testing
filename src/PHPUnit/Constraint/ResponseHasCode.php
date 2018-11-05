<?php declare(strict_types=1);
namespace Imatic\Testing\PHPUnit\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Miloslav Nenadal <miloslav.nenadal@imatic.cz>
 */
class ResponseHasCode extends Constraint
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

    protected function matches($other): bool
    {
        return $this->response->getStatusCode() === $other;
    }

    public function toString(): string
    {
        return \sprintf('is equal to %s (response: "%s")', $this->response->getStatusCode(), $this->response->getContent());
    }
}
