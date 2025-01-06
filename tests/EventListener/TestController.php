<?php

namespace Webfactory\SlugValidationBundle\Tests\EventListener;

use Symfony\Component\HttpFoundation\Response;

/**
 * Test controller with an action that has a named argument, so that e.g. the slug parameter name can be determined.
 */
final class TestController
{
    public function testAction(mixed $object): Response
    {
        return new Response();
    }
}
