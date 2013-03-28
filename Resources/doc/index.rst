Testing
=======

Initialize test environment
---------------------------

``composer install --dev``

Run tests
---------

``phpunit``

Initialize testing project
==========================

* Add phpunit.xml.dist
* Add to gitignore:

.. sourcecode:: text

    vendor
    Tests/Fixtures/TestProject/cache
    Tests/Fixtures/TestProject/logs
    Tests/Fixtures/TestProject/ProjectBundle/DataFixtures
    Tests/Fixtures/TestProject/ProjectBundle/Entity

* Add to composer.json

.. sourcecode:: javascript

    "repositories": [
        {
            "type": "composer",
            "url": "http://composer.imatic.cz"
        }
    ],
    "require-dev": {
        "imatic/testing-bundle": "dev-master"
    },

* Directory structure

.. sourcecode:: text

    Tests/Fixtures
        TestProject
            config
                config.yml
                routing.yml
            XYZBundle (same name as tested bundle)
                AppXYZBundle.php (dtto)
            console
            TestKernel.php
        bootstrap.php
        WebTestCase.php

* note: content of files must be configured according with tested bundle name (see content of example files)
* example https://bitbucket.org/imatic/imatic-directorybundle/src
