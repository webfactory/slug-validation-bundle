<?php

namespace Webfactory\SlugValidationBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Webfactory\SlugValidationBundle\Bridge\SluggableInterface;

/**
 * This listener is called after the arguments for a controller action are resolved.
 *
 * It checks these arguments for SluggableInterface implementations and if one is found,
 * it checks it's slug against the slug in the route parameters. If the route parameter
 * slug is invalid, a RedirectResponse to the URL with the correct slug is created.
 *
 * The name of the slug parameter in the route paramters is expected to be the argument
 * name + "Slug", e.g. named "objectSlug" for an argument named "object".
 */
final class ValidateSlugListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => 'pepareRedirectIfAnInvalidSlugIsGiven',
        ];
    }

    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function pepareRedirectIfAnInvalidSlugIsGiven(ControllerArgumentsEvent $event): void
    {
        $attributes = $event->getRequest()->attributes;
        foreach ($event->getNamedArguments() as $parameterName => $parameterValue) {
            if ($this->hasInvalidSlug($attributes, $parameterName, $parameterValue)) {
                $this->prepareRedirect($event, $parameterName, $parameterValue);
                break;
            }
        }
    }

    private function prepareRedirect(
        ControllerArgumentsEvent $event,
        string $parameterName,
        SluggableInterface $sluggable
    ): void {
        $event->setController(function () use ($event, $parameterName, $sluggable) {
            return new RedirectResponse(
                $this->urlGenerator->generate(
                    $event->getRequest()->get('_route'),
                    array_merge(
                        $event->getRequest()->attributes->get('_route_params', []),
                        [$this->getSlugParameterNameFor($parameterName) => $sluggable->getSlug()]
                    )
                ),
                Response::HTTP_MOVED_PERMANENTLY,
            );
        });

        $event->stopPropagation();
    }

    private function hasInvalidSlug(ParameterBag $attributes, string $parameterName, mixed $object): bool
    {
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
