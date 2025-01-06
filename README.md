# Slug Validation Bundle #

![Tests](https://github.com/webfactory/slug-validation-bundle/workflows/Tests/badge.svg)
![Dependencies](https://github.com/webfactory/slug-validation-bundle/workflows/Dependencies/badge.svg)

Do not clutter your controller actions with URL slug validation: This Symfony bundle helps
to validate object slugs in URLs transparently.

- Checks if a slug is valid (if provided at all)
- Redirects to the URL with the correct slug on failure (for example after a slug change)

## Motivation ##

Handling of URL Slugs is a part of many web applications. Although readable URLs are nice, they usually do not
contribute to your main functionality. Instead, slug validation and handling of redirects in case of failure generates
a lot of noise in your controller actions, is often cluttered over many parts of the application and makes it harder
to see the core problems that are solved.

After facing these problems several times, we decided to create a system that handles slug validation as part
of the middleware, that keeps your controller actions clean and lets you concentrate on what is really important:
Your domain problems.

## Installation ##

Install the bundle via [Composer](https://getcomposer.org):

    composer require webfactory/slug-validation-bundle

Enable the bundle:

    <?php
    // src/bundles.php

    return [
        // ...
        Webfactory\SlugValidationBundle\WebfactorySlugValidationBundle::class => ['all' => true],
        // ...
    ];

## Usage ##

### Prerequisite: Sluggable object as controller action parameter ###

Declare your sluggable object as controller action parameter:

    public function myAction(MyEntity $entity)
    {
    }

And configure it to be resolved before the controller action is called, e.g. via
[`#[MapEntity]`](https://symfony.com/doc/current/doctrine.html#mapentity-options) or
[`@ParamConverter`](http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html) (deprecated).

### Implement Sluggable ###

Provide the hint that the entity has a slug that can be validated by implementing
``\Webfactory\SlugValidationBundle\Bridge\SluggableInterface``:

    class MyEntity implements SluggableInterface
    {
        public function getSlug(): ?string
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

#### Slug Generation ####

If you are not sure how to create your slugs, then you might find [cocur/slugify](https://github.com/cocur/slugify)
useful. A component that generates URL slugs from any string.

#### Simplified Routing ####

Passing slug values during route generation can be a tedious and error-prone task.
[webfactory/object-routing](https://github.com/webfactory/object-routing) and [webfactory/object-routing-bundle](https://github.com/webfactory/BGObjectRoutingBundle)
can ease that task by defining route construction rules directly with your entity:

    /**
     * @ObjectRoute(type="my_object_route", name="my_entity_route", params={
     *     "entity": "id",
     *     "entitySlug": "slug"
     * })
     */
    class MyEntity implements SluggableInterface
    {
        public function getId() 
        {
            // ...
        }
        
        public function getSlug() 
        {
            // ...
        }
        
        // ...
    }

When generating the URL, you don't have to deal with passing these parameters anymore (example in Twig):

    {{ object_path('my_object_route', myEntityInstance) }}

## Credits, Copyright and License ##

This project was started at webfactory GmbH, Bonn.

- <https://www.webfactory.de>
- <https://twitter.com/webfactory>

Copyright 2016-2025 webfactory GmbH, Bonn. Code released under [the MIT license](LICENSE).
