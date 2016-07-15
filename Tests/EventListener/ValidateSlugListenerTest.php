<?php

namespace Webfactory\SlugValidationBundle\Tests\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
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

    public function testListenerDoesNotRedirectIfRequestContainsNoObjects()
    {
        $event = $this->createEvent();
        $originalController = $event->getController();

        $this->listener->onKernelController($event);

        $this->assertSame($originalController, $event->getController());
    }

    public function testListenerDoesNotRedirectIfRequestContainsObjectButNoSlugIsRequired()
    {
        $event = $this->createEvent();
        $event->getRequest()->attributes->set('object', $this->createSluggableObject(null));
        $originalController = $event->getController();

        $this->listener->onKernelController($event);

        $this->assertSame($originalController, $event->getController());
    }

    public function testListenerDoesNotRedirectIfRequestContainsValidSlugForObject()
    {
        $object = $this->createSluggableObject('my-slug');
        $event   = $this->createEvent();
        $event->getRequest()->attributes->set('object', $object);
        $event->getRequest()->attributes->set('objectSlug', $object->getSlug());
        $originalController = $event->getController();

        $this->listener->onKernelController($event);

        $this->assertSame($originalController, $event->getController());
    }

    public function testListenerRedirectsIfRequestContainsInvalidSlugForObject()
    {
        $event = $this->createEvent();
        $event->getRequest()->attributes->set('object', $this->createSluggableObject('real-slug'));
        $event->getRequest()->attributes->set('objectSlug', 'an-invalid-slug');

        $this->listener->onKernelController($event);

        $controller = $event->getController();
        $this->assertTrue(is_callable($controller), 'Controller must be callable.');
        $response = call_user_func($controller);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testListenerAddsCorrectSlugToUrlIfNecessary()
    {
        $event  = $this->createEvent();
        $object = $this->createSluggableObject('real-slug');
        $event->getRequest()->attributes->set('object', $object);
        $event->getRequest()->attributes->set('objectSlug', 'an-invalid-slug');

        $this->listener->onKernelController($event);

        $controller = $event->getController();
        $this->assertTrue(is_callable($controller), 'Controller must be callable.');
        /* @var $response RedirectResponse */
        $response = call_user_func($controller);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertContains($object->getSlug(), $response->getTargetUrl());
    }

    /**
     * Ensures that the listener does not redirect if there is no slug defined
     * for the object.
     */
    public function testListenerDoesNotRedirectIfObjectHasNoSlug()
    {
        $event  = $this->createEvent();
        $object = $this->createSluggableObject(null);
        $event->getRequest()->attributes->set('object', $object);
        $event->getRequest()->attributes->set('objectSlug', 'an-invalid-slug');
        $originalController = $event->getController();

        $this->listener->onKernelController($event);

        $this->assertSame($originalController, $event->getController());
    }

    /**
     * Simulates an object that provides the given slug.
     *
     * @param string|null $slug
     * @return SluggableInterface
     */
    private function createSluggableObject($slug)
    {
        $object = $this->getMock(SluggableInterface::class);
        $object->expects($this->any())
            ->method('getSlug')
            ->willReturn($slug);
        return $object;
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
