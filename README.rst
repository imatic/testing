Testing
=======
This `bundle <https://symfony.com/doc/current/bundles.html>`_ makes it easy to setup tests including testing project for reusable bundles. It will create all required files so that you can start writing your tests and visualise components of your bundle.

Content
=======
* `Initialize testing project`_
* `PHPUnit`_
* `Usage`_

Initialize testing project
==========================

* Add `phpunit.xml.dist <https://phpunit.de/manual/current/en/appendixes.configuration.html>`_ to your repository to configure `phpunit <https://phpunit.de/>`__

* Add imatic-testingbundle into your dev dependencies

.. sourcecode:: bash

   $ composer require --dev -- 'imatic/testingbundle'

* Create required files in your `bundle <bundle_>`_

.. sourcecode:: bash

    $ ./vendor/bin/init-testingbundle


* Command above should create directory structure similar to

.. sourcecode:: text

    Tests
        Fixtures
            TestProject
                config
                    config.yml
                    routing.yml
                XYZBundle (same name as tested bundle)
                    AppXYZBundle.php
                web
                    app.php
                console
                TestKernel.php
                WebTestCase.php
        Functional
        Integration
        Unit
        bootstrap.php

* Directory structure above is created based on template in `TestsTemplate <TestsTemplate>`_ directory

PHPUnit
=======

Additional constraints
----------------------
All additinal constraints usable in `symfony functional tests <symfony functional tests_>`_ are included in our ``WebTestCase`` automatically. If you have your own ``WebTestCase`` implementation, you can use our trait `WebTestCaseExtension <Test/WebTestCaseExtension.php>`_ to include our additional constraints in your ``WebTestCase``.

ResponseHasCode
^^^^^^^^^^^^^^^
This constraint can be used to assert status codes in `symfony functional tests <https://symfony.com/doc/current/testing.html#functional-tests>`_. Usage:

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

* advantage in comparison with asserting 200 with ``$client->getResponse()->getStatusCode()`` is that the special assert gives you information about what went wrong instead of giving you just wrong code of the response (e.g. 500)

Usage
=====
Now if you have all configured, you can start writing tests or check your testing project in browser. In order to check your testing project in browser.

Checking your testing project in browser
----------------------------------------

* go to the testing project root and run web server

.. sourcecode:: bash

   $ cd Tests/Fixtures/TestProject/
   $ ./console server:run --docroot=web

* now open your browser at url reported by the last command (probably "http://127.0.0.1:8000/app.php")
* you will se exception now probably because you didn't configure any routes for your project yet

