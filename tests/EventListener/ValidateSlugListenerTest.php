<?php

namespace Webfactory\SlugValidationBundle\Tests\EventListener;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Webfactory\SlugValidationBundle\Bridge\SluggableInterface;
use Webfactory\SlugValidationBundle\EventListener\ValidateSlugListener;

class ValidateSlugListenerTest extends TestCase
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
    protected function setUp(): void
    {
        parent::setUp();
        $this->listener = new ValidateSlugListener($this->createUrlGenerator());
    }

    /**
     * @test
     */
    public function isEventSubscriber()
    {
        $this->assertInstanceOf(EventSubscriberInterface::class, $this->listener);
    }

    /**
     * @test
     */
    public function listenerDoesNotRedirectIfRequestContainsNoObjects()
    {
        $event = $this->createEvent();
        $originalController = $event->getController();

        $this->listener->onKernelController($event);

        self::assertSame($originalController, $event->getController());
    }

    /**
     * @test
     */
    public function listenerDoesNotRedirectIfRequestContainsObjectButNoSlugIsRequired()
    {
        $event = $this->createEvent();
        $event->getRequest()->attributes->set('object', $this->createSluggableObject(null));
        $originalController = $event->getController();

        $this->listener->onKernelController($event);

        self::assertSame($originalController, $event->getController());
    }

    /**
     * @test
     */
    public function listenerDoesNotRedirectIfRequestContainsValidSlugForObject()
    {
        $object = $this->createSluggableObject('my-slug');
        $event = $this->createEvent();
        $event->getRequest()->attributes->set('object', $object);
        $event->getRequest()->attributes->set('objectSlug', $object->getSlug());
        $originalController = $event->getController();

        $this->listener->onKernelController($event);

        self::assertSame($originalController, $event->getController());
    }

    /**
     * @test
     */
    public function listenerRedirectsIfRequestContainsInvalidSlugForObject()
    {
        $event = $this->createEvent();
        $event->getRequest()->attributes->set('object', $this->createSluggableObject('real-slug'));
        $event->getRequest()->attributes->set('objectSlug', 'an-invalid-slug');

        $this->listener->onKernelController($event);

        $controller = $event->getController();
        self::assertIsCallable($controller, 'Controller must be callable.');
        $response = \call_user_func($controller);
        self::assertInstanceOf(RedirectResponse::class, $response);
    }

    /**
     * There are problems with the template listener in newer Symfony versions if
     * the event propagation is not stopped.
     *
     * @test
     */
    public function listenerStopsEventPropagationIfRedirectIsNecessary()
    {
        $event = $this->createEvent();
        $event->getRequest()->attributes->set('object', $this->createSluggableObject('real-slug'));
        $event->getRequest()->attributes->set('objectSlug', 'an-invalid-slug');

        $this->listener->onKernelController($event);

        self::assertTrue($event->isPropagationStopped());
    }

    /**
     * @test
     */
    public function listenerAddsCorrectSlugToUrlIfNecessary()
    {
        $event = $this->createEvent();
        $object = $this->createSluggableObject('real-slug');
        $event->getRequest()->attributes->set('object', $object);
        $event->getRequest()->attributes->set('objectSlug', 'an-invalid-slug');

        $this->listener->onKernelController($event);

        $controller = $event->getController();
        self::assertIsCallable($controller, 'Controller must be callable.');
        /* @var $response RedirectResponse */
        $response = \call_user_func($controller);
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertStringContainsString($object->getSlug(), $response->getTargetUrl());
    }

    /**
     * Ensures that the listener does not redirect if there is no slug defined
     * for the object.
     *
     * @test
     */
    public function listenerDoesNotRedirectIfObjectHasNoSlug()
    {
        $event = $this->createEvent();
        $object = $this->createSluggableObject(null);
        $event->getRequest()->attributes->set('object', $object);
        $event->getRequest()->attributes->set('objectSlug', 'an-invalid-slug');
        $originalController = $event->getController();

        $this->listener->onKernelController($event);

        self::assertSame($originalController, $event->getController());
    }

    /**
     * Simulates an object that provides the given slug.
     *
     * @param string|null $slug
     *
     * @return SluggableInterface
     */
    private function createSluggableObject($slug)
    {
        $object = $this->createMock(SluggableInterface::class);
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
        return new ControllerEvent(
            $this->createKernel(),
            $this->createController(),
            new Request(),
            HttpKernelInterface::MASTER_REQUEST
        );
    }

    /**
     * Creates a mocked kernel.
     *
     * @return MockObject&HttpKernelInterface
     */
    private function createKernel()
    {
        return $this->createMock(HttpKernelInterface::class);
    }

    /**
     * Creates a mocked controller (which is basically a callable).
     *
     * @return MockObject&callable
     */
    private function createController()
    {
        return $this->getMockBuilder(\stdClass::class)->setMethods(['__invoke'])->getMock();
    }

    /**
     * Creates a URL generator for testing.
     *
     * @return UrlGeneratorInterface
     */
    private function createUrlGenerator()
    {
        $generator = $this->createMock(UrlGeneratorInterface::class);
        // Create a dummy URL that contains relevant provided data.
        $generateUrl = function ($route, array $attributes) {
            $url = '/'.$route.'?'.http_build_query($attributes);

            return $url;
        };
        $generator->expects($this->any())->method('generate')->willReturnCallback($generateUrl);

        return $generator;
    }
}
