<?php

namespace Webfactory\SlugValidationBundle\Tests\EventListener;

use Webfactory\SlugValidationBundle\EventListener\ValidateSlugListener;

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
        $this->listener = new ValidateSlugListener();
    }

    /**
     * Cleans up the test environment.
     */
    protected function tearDown()
    {
        $this->listener = null;
        parent::tearDown();
    }
}
