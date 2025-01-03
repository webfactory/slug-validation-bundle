<?php

namespace Webfactory\SlugValidationBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Webfactory\SlugValidationBundle\Bridge\SluggableInterface;

/**
 * Checks if sluggable objects occur in the request attributes (which are mapped to action
 * parameters) and validates corresponding slugs, if available.
 *
 * This listener must be registered *after* the ParamConverterListener, otherwise
 * the validation cannot work.
 *
 * Slugs must be available as route parameter. The slug for a parameter "object" is
 * expected as "objectSlug" parameter.
 */
final class ValidateSlugListener implements EventSubscriberInterface
{
    /**
     * Priority of this listener. Will run after the param converter.
     */
    public const PRIORITY_AFTER_PARAM_CONVERTER_LISTENER = -1;

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', self::PRIORITY_AFTER_PARAM_CONVERTER_LISTENER],
        ];
    }

    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    /**
     * Searches for sluggable objects in the route parameters and checks slugs if necessary.
     *
     * If an invalid slug is detected, then the user will be redirected to the URLs with the valid slug.
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $attributes = $event->getRequest()->attributes;
        foreach ($attributes as $name => $value) {
            if (!$this->hasInvalidSlug($attributes, $name)) {
                continue;
            }

            $event->stopPropagation();
            // Invalid slug passed. Redirect to a URL with valid slug.
            $event->setController(function () use ($event, $name) {
                return $this->createRedirectFor($event->getRequest(), $name);
            });
            break;
        }
    }

    private function createRedirectFor(Request $request, string $objectParameterName): RedirectResponse
    {
        /* @var $object SluggableInterface */
        $object = $request->attributes->get($objectParameterName);
        $url = $this->urlGenerator->generate(
            $request->get('_route'),
            array_merge(
                $request->attributes->get('_route_params', []),
                [$this->getSlugParameterNameFor($objectParameterName) => $object->getSlug()]
            )
        );

        return new RedirectResponse($url, 301);
    }

    private function hasInvalidSlug(ParameterBag $attributes, string $parameterName): bool
    {
        $object = $attributes->get($parameterName);
        if (!($object instanceof SluggableInterface)) {
            // Only sluggable objects are checked.
            return false;
        }

        $slugParameterName = $this->getSlugParameterNameFor($parameterName);
        if (!$attributes->has($slugParameterName)) {
            // Seems as if no slug is used in the route.
            return false;
        }

        if (null === $object->getSlug()) {
            // Object has no slug (yet). Simply accept any slug to avoid
            // getting into an endless redirect loop.
            return false;
        }

        return $object->getSlug() !== (string) $attributes->get($slugParameterName);
    }

    /**
     * Returns the name of the parameter that could contain the slug for the object retrievable with the $parameterName.
     */
    private function getSlugParameterNameFor(string $parameterName): string
    {
        return $parameterName.'Slug';
    }
}
