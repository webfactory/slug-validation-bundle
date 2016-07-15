<?php

namespace StaatsoperBerlin\EntitySlugValidationBundle\Tests\EventListener;

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
