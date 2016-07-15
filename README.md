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
- Create a [Zap](https://zapier.com/) that converts GitHub issues to Fogbug cases
- Activate job in Jenkins CI

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

## Concept ##

## Usage ##

## Known Issues ##

## Credits, Copyright and License ##

This project was started at webfactory GmbH, Bonn.

- <http://www.webfactory.de>
- <http://twitter.com/webfactory>

Copyright 2016 webfactory GmbH, Bonn. Code released under [the MIT license](LICENSE).
