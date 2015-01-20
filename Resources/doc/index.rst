Testing
=======

Initialize test environment
---------------------------

``composer install``

Run tests
---------

``./vendor/bin/phpunit``

Initialize testing project
==========================

* Add phpunit.xml.dist

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

* After composer install

.. sourcecode:: bash

    $ ./vendor/bin/init-testingbundle

* Created directory structure

.. sourcecode:: text

    Tests
        Fixtures
            TestProject
                config
                    config.yml
                    routing.yml
                XYZBundle (same name as tested bundle)
                    AppXYZBundle.php (dtto)
                console
                TestKernel.php
                WebTestCase.php
        Functional
        Integration
        Unit
        bootstrap.php

* example https://bitbucket.org/imatic/imatic-directorybundle/src

PHPUnit
=======

Constraints
-----------

ResponseHasCode
^^^^^^^^^^^^^^^

.. sourcecode:: php

   <?php

   use Imatic\Bundle\TestingBundle\Test\WebTestCase;

   class ExampleTestCase extends WebTestCase
   {
       public function testHomepageReturnsOk()
       {
           $client = static::createClient();
           $client->request('GET', '/');

           $this->assertResponseHasCode(200, $client->getResponse());
       }
   }

* advantage in comparison with asserting 200 with ``$client->getResponse()->getStatusCode()`` is that the special assert gives you informations about what went wrong instead of giving you just wrong code of the response (e.g. 500)


ScriptHandler
=============

* if you put following into your composer.json, symlink for bower files located at view testing bundle will be created under your testing bundle, so you don't have to copy bower files in order to use templates from view bundle

.. sourcecode:: json

   "scripts": {
        "post-install-cmd": [
            "Imatic\\Bundle\\TestingBundle\\Composer\\ScriptHandler::symlinkBowerIfNotExists"
        ],
        "post-update-cmd": [
            "Imatic\\Bundle\\TestingBundle\\Composer\\ScriptHandler::symlinkBowerIfNotExists"
        ]
    },

