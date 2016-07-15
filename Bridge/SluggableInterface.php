<?php

namespace Webfactory\SlugValidationBundle\Bridge;

/**
 * Interface for objects that provide a URL slug.
 */
interface SluggableInterface
{
    /**
     * Returns the slug of the object.
     *
     * @return string|null
     */
    public function getSlug();
}
