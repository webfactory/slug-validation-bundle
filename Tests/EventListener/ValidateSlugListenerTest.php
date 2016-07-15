<?php

namespace StaatsoperBerlin\EntitySlugValidationBundle\Tests\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Webfactory\SlugValidationBundle\Bridge\SluggableInterface;
use Webfactory\SlugValidationBundle\EventListener\ValidateSlugListener;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ValidateSlugListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * System under test.
     *
     * @var ValidateSlugListener
     */
    protected $listener = null;

    /**
     * Initializes the test environment.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->listener = new ValidateSlugListener($this->createUrlGenerator());
    }

    /**
     * Cleans up the test environment.
     */
    protected function tearDown()
    {
        $this->listener = null;
        parent::tearDown();
    }

    /**
     * Simulates an object that provides the given slug.
     *
     * @param string|null $slug
     * @return SluggableInterface
     */
    private function createSluggableObject($slug)
    {
        $entity = $this->getMock(SluggableInterface::class);
        $entity->expects($this->any())
            ->method('getSlug')
            ->willReturn($slug);
        return $entity;
    }

    /**
     * Creates a basic event that is used for testing.
     *
     * @return FilterControllerEvent
     */
    private function createEvent()
    {
        return new FilterControllerEvent(
            $this->createKernel(),
            $this->createController(),
            new Request(),
            HttpKernelInterface::MASTER_REQUEST
        );
    }

    /**
     * Creates a mocked kernel.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpKernelInterface
     */
    private function createKernel()
    {
        return $this->getMock(HttpKernelInterface::class);
    }

    /**
     * Creates a mocked controller (which is basically a callable).
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|callable
     */
    private function createController()
    {
        return $this->getMock(\stdClass::class, array('__invoke'));
    }

    /**
     * Creates a URL generator for testing.
     *
     * @return UrlGeneratorInterface
     */
    private function createUrlGenerator()
    {
        $generator = $this->getMock(UrlGeneratorInterface::class);
        // Create a dummy URL that contains relevant provided data.
        $generateUrl = function ($route, array $attributes) {
            $url = '/' . $route . '?' . http_build_query($attributes);
            return $url;
        };
        $generator->expects($this->any())->method('generate')->willReturnCallback($generateUrl);
        return $generator;
    }
}
