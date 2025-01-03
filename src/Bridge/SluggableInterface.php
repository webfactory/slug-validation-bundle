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
     * Return null if the object does not have a slug (yet).
     * In that case, no validation will be performed and any
     * (dummy) slug will be accepted.
     */
    public function getSlug(): ?string;
}
