<?php

namespace Webfactory\SlugValidationBundle\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Webfactory\SlugValidationBundle\Bridge\SluggableInterface;
use Webfactory\SlugValidationBundle\EventListener\ValidateSlugListener;

final class ValidateSlugListenerTest extends TestCase
{
    /**
     * System under test.
     */
    private ValidateSlugListener $listener;

    protected function setUp(): void
    {
        parent::setUp();
        $this->listener = new ValidateSlugListener($this->createUrlGenerator());
    }

    /**
     * @test
     */
    public function isEventSubscriber(): void
    {
        $this->assertInstanceOf(EventSubscriberInterface::class, $this->listener);
    }

    /**
     * @test
     */
    public function listenerDoesNotRedirectIfRequestContainsNoObjects(): void
    {
        $event = $this->createEvent();

        $this->assertListenerDoesNotRedirectForEvent($event);
    }

    /**
     * @test
     */
    public function listenerDoesNotRedirectIfRequestContainsObjectButNoSlugParameterIsProvided(): void
    {
        $event = $this->createEvent([$this->createSluggable()]);

        $this->assertListenerDoesNotRedirectForEvent($event);
    }

    /**
     * @test
     */
    public function listenerDoesNotRedirectIfRequestContainsValidSlugForObject(): void
    {
        $sluggable = $this->createSluggable();
        $event = $this->createEvent([$sluggable], $sluggable->getSlug());

        $this->assertListenerDoesNotRedirectForEvent($event);
    }

    /**
     * @test
     */
    public function listenerRedirectsToUrlWithCorrectSlugIfRequestContainsInvalidSlug(): void
    {
        $event = $this->createEventForRedirect();
        $sluggable = $event->getArguments()[0];

        $this->listener->pepareRedirectIfAnInvalidSlugIsGiven($event);

        $resultingController = $event->getController();
        self::assertIsCallable($resultingController, 'Controller must be callable.');
        $response = \call_user_func($resultingController);
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertStringContainsString($sluggable->getSlug(), $response->getTargetUrl());
    }

    /**
     * There are problems with the template listener in newer Symfony versions if
     * the event propagation is not stopped.
     *
     * @test
     */
    public function listenerStopsEventPropagationIfRedirectIsNecessary(): void
    {
        $event = $this->createEventForRedirect();

        $this->listener->pepareRedirectIfAnInvalidSlugIsGiven($event);

        self::assertTrue($event->isPropagationStopped());
    }

    /**
     * Ensures that the listener does not redirect if there is no slug defined
     * for the object.
     *
     * @test
     */
    public function listenerDoesNotRedirectIfObjectHasNoSlug(): void
    {
        $sluggable = $this->createSluggable(null);
        $event = $this->createEvent([$sluggable], 'invalid-slug');

        $this->assertListenerDoesNotRedirectForEvent($event);
    }

    /**
     * Simulates an object that provides the given slug.
     */
    private function createSluggable(?string $slug = 'correct-slug'): SluggableInterface
    {
        $sluggable = $this->createMock(SluggableInterface::class);
        $sluggable->expects($this->any())
            ->method('getSlug')
            ->willReturn($slug);

        return $sluggable;
    }

    private function createEventForRedirect(): ControllerArgumentsEvent
    {
        return $this->createEvent([$this->createSluggable()], 'invalid-slug');
    }

    /**
     * Creates a basic event that is used for testing.
     */
    private function createEvent(
        array $controllerArguments = [],
        ?string $slugParameterValue = null
    ): ControllerArgumentsEvent {
        $requestAttributes = ['_route' => 'test'];
        if (null !== $slugParameterValue) {
            $requestAttributes['objectSlug'] = $slugParameterValue;
        }

        return new ControllerArgumentsEvent(
            $this->createMock(HttpKernelInterface::class),
            [new TestController(), 'testAction'],
            $controllerArguments,
            new Request(attributes: $requestAttributes),
            HttpKernelInterface::MAIN_REQUEST
        );
    }

    /**
     * Creates a URL generator for testing.
     */
    private function createUrlGenerator(): UrlGeneratorInterface
    {
        $generator = $this->createMock(UrlGeneratorInterface::class);
        // Create a dummy URL that contains relevant provided data.
        $generator->expects($this->any())
            ->method('generate')
            ->willReturnCallback(
                function ($route, array $attributes) {
                    return '/'.$route.'?'.http_build_query($attributes);
                }
            );

        return $generator;
    }

    private function assertListenerDoesNotRedirectForEvent(ControllerArgumentsEvent $event): void
    {
        $originalController = $event->getController();

        $this->listener->pepareRedirectIfAnInvalidSlugIsGiven($event);

        self::assertSame($originalController, $event->getController());
    }
}
