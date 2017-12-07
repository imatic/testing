.. image:: https://secure.travis-ci.org/imatic/testing.png?branch=master
   :alt: Build Status
   :target: http://travis-ci.org/imatic/testing
|
.. image:: https://img.shields.io/badge/License-MIT-yellow.svg
   :alt: License: MIT
   :target: LICENSE

Testing
=======
This `bundle <https://symfony.com/doc/current/bundles.html>`_ makes it easy to setup tests including testing project for reusable bundles. It will create all required files so that you can start writing your tests and visualise components of your bundle without necessity of including it into some application (as this bundle creates such application in testing namespace of your bundle).

Content
=======
* `Initialize testing project`_
* `PHPUnit`_
* `Usage`_
* `Checking your testing project in browser`_
* `Writing tests working with database`_
* `Testing project configuration`_

Initialize testing project
==========================

* Add `phpunit.xml.dist <https://phpunit.de/manual/current/en/appendixes.configuration.html>`_ to your repository to configure `phpunit <https://phpunit.de/>`__
* Set KERNEL_DIR server variable so that phpunit finds symfony kernel (example value nees to be modified based on bundle name)

.. sourcecode:: xml

   <!-- phpunit.xml.dist -->
   <phpunit>
       <php>
           <server name="KERNEL_DIR" value="Imatic\Bundle\TestsTemplateBundle\Tests\Fixtures\TestProject\TestKernel"/>
       </php>
   </phpunit>

* Add imatic-testing into your dev dependencies

.. sourcecode:: bash

   $ composer require --dev 'imatic/testing'

* Create required files in your `bundle <bundle_>`_

.. sourcecode:: bash

    $ ./vendor/bin/testingbundle-init


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

* Directory structure above is created based on template in `TestsTemplate <Resources/skeleton/TestsTemplate>`_ directory

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

   use Imatic\Testing\Test\WebTestCase;

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

Using PHP's bult-in web server
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. sourcecode:: bash

   $ cd Tests/Fixtures/TestProject/
   $ ./console server:run --docroot=web

* now open your browser at url reported by the last command (probably "http://127.0.0.1:8000/app.php")
* you will see exception now probably because you didn't configure any routes for your project yet
* you can find more details on the command in `symfony documentation <https://symfony.com/doc/current/setup/built_in_web_server.html>`__

Using other web servers
^^^^^^^^^^^^^^^^^^^^^^^

* see `symfony documentation <https://symfony.com/doc/current/setup/web_server_configuration.html>`__ on how to configure each

Using symfony console
---------------------

* as you may or may not notice when we talked about testing project directory structure, you have also available symfony console - so that you can run all commands that your bundle or bundles your testing project uses provide
* see `symfony documentation <https://symfony.com/doc/current/components/console/usage.html>`__ on how to work with console command (note that in our case, the executable running console is called ``console`` and is placed in root of the testing project


Writing tests working with database
===================================

* if you use our ``WebTestCase`` as parent of your tests, then each test will run in transaction so all your modifications to db are lost (so you have db in state before the test run)
* see commented test below on how it works

.. sourcecode:: php

    <?php

    namespace Imatic\Bundle\DataBundle\Tests\Integration\Data\Command;

    use Doctrine\ORM\EntityManager;
    use Doctrine\ORM\EntityRepository;
    use Imatic\Bundle\DataBundle\Data\Command\Command;
    use Imatic\Bundle\DataBundle\Data\Command\CommandExecutor;
    use Imatic\Bundle\DataBundle\Tests\Fixtures\TestProject\ImaticDataBundle\Entity\User;
    use Imatic\Bundle\DataBundle\Tests\Fixtures\TestProject\WebTestCase;

    // this is our test class wich extends ``WebTestCase`` which ensures
    // that each test runs in the same environment (has predictable db state)
    class CommandExecutorTest extends WebTestCase
    {
        public function testGivenCommandShouldBeSuccessfullyExecuted()
        {
            /* @var $user User */
            $user = $this->getUserRepository()->findOneByName('Adam');

            // here we make sure that the user is not activated
            // so that we can test activating functionality
            $this->assertTrue($user->isActivated());

            $command = new Command('user.deactivate', ['id' => $user->getId()]);
            // here we execute command which activates the user
            // and stores the information into database
            $result = $this->getCommandExecutor()->execute($command);
            $this->assertTrue($result->isSuccessful());

            // here we check that user was activated
            $this->assertFalse($user->isActivated());

            // after this test finishes, user is deactivated because the transaction
            // is rollbacked
        }

        /**
         * @return EntityRepository
         */
        public function getUserRepository()
        {
            return $this->getEntityManager()->getRepository('AppImaticDataBundle:User');
        }

        /**
         * @return EntityManager
         */
        public function getEntityManager()
        {
            return $this->container->get('doctrine.orm.entity_manager');
        }

        /**
         * @return CommandExecutor
         */
        private function getCommandExecutor()
        {
            return $this->container->get('imatic_data.command_executor');
        }
    }

* note that because of doctrine connection wrapper we use - you can write `symfony funcional tests <https://symfony.com/doc/current/testing.html#functional-tests>`__ and after each test, transaction will still be rollbacked (which is not possible without using the wrapper
    * you can see this connection wrapper in our `config template <TestsTemplate/Fixtures/TestProject/config/config.ym>`__

.. sourcecode:: yaml

    doctrine:
        dbal:
            connections:
                default:
                    wrapper_class: "Imatic\\Testing\\Doctrine\\DBAL\\PersistedConnection"

Testing project configuration
=============================

* if you need to make changes to the configuration, just edit generated config file which you can find in ``config/config.yml`` (relative to the testing project roor)

