<?php

namespace Webfactory\SlugValidationBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
class ValidateSlugListener implements EventSubscriberInterface
{
    /**
     * Priority of this listener. Will run after the param converter.
     */
    const PRIORITY_AFTER_PARAM_CONVERTER_LISTENER = -1;

    /**
     * Generator that is used to create the redirect URLs.
     *
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator = null;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array('onKernelController', self::PRIORITY_AFTER_PARAM_CONVERTER_LISTENER)
        );
    }

    /**
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Searches for sluggable objects in the route parameters and checks slugs if necessary.
     *
     * If an invalid slug is detected, then the user will be redirected to the URLs with the valid slug.
     *
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {

    }
}
