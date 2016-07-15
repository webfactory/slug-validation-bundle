# Slug Validation Bundle #

[![Build Status](https://travis-ci.org/webfactory/slug-validation-bundle.svg?branch=master)](https://travis-ci.org/webfactory/slug-validation-bundle)
[![Coverage Status](https://coveralls.io/repos/github/webfactory/slug-validation-bundle/badge.svg?branch=master)](https://coveralls.io/github/webfactory/slug-validation-bundle?branch=master)

Do not clutter your controller actions with URL slug validation: This Symfony bundle helps
to validate object slugs in URLs transparently.

- Checks if a slug is valid (if provided at all)
- Redirects to the URL with the correct slug on failure (for example after a slug change)

## Initialization Tasks (remove this block once you are done) ##

- Activate builds in [Travis CI](https://travis-ci.org/)
- Activate repository at [Coveralls](https://coveralls.io)
- Publish at [Packagist](https://packagist.org/)
- Create webhook that pushes repository updates to [Packagist](https://packagist.org/)
- Configure HipChat webhook (post repository updates to "Github" room)

## Motivation: Why does this project exist? ##

## Installation ##

Install the bundle via [Composer](https://getcomposer.org):

    composer require webfactory/slug-validation-bundle

Enable the bundle in your kernel:

    <?php
    // app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new \Webfactory\SlugValidationBundle\WebfactorySlugValidationBundle(),
            // ...
        );
        // ...
    }

## Usage ##

*Prerequisite*: In order to be able to use the slug validation provided by this bundle,
you have to load your sluggable objects via a [param converter](http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html).
For Doctrine entities Symfony brings this capability out of the box.

### Request Your Entity via Param Converter ###

Declare your object as controller action parameter:

    public function myAction(MyEntity $entity)
    {
    }
    
When using Doctrine entities, your route parameter ``entity`` must contain the entity ID to make this work.

### Implement Sluggable ###

Provide the hint that the entity has a slug that can be validated by implementing
``\Webfactory\SlugValidationBundle\Bridge\SluggableInterface``:

    class MyEntity implements SluggableInterface
    {
        /**
         * @return string|null
         */
        public function getSlug()
        {
            return 'my-generated-slug';
        }
    }
    
### Add Slug Parameter to Routes ###
    
Declare a route that contains an ``entitySlug`` parameter and points to your action: 
    
    my_entity_route:
        path: /entity/{entitySlug}.{entity}
        defaults:
            _controller: MyBundle:MyController:my

That's it! Whenever a sluggable entity is used together with a slug parameter in a route this bundle will
step in and perform a validation. If a slug is invalid, then a redirect to the same route with the 
corrected slug will be initiated.

### Additional Information ###

Entity and slug parameters are matched by convention: The slug parameter must use the suffix ``Slug``.
For example the correct parameter name for a ``blogPost`` parameter is ``blogPostSlug``.

If a route contains a sluggable entity but no slug parameter, then nothing will happen, so the usual
Symfony behavior is not changed.

## Known Issues ##

## Credits, Copyright and License ##

This project was started at webfactory GmbH, Bonn.

- <http://www.webfactory.de>
- <http://twitter.com/webfactory>

Copyright 2016 webfactory GmbH, Bonn. Code released under [the MIT license](LICENSE).
