UPGRADE FROM 4.x to 5.0
=======================

Configuration
-------------

* new parameter value ``testing.data_init`` is required. Add it to your existing test project configuration


    parameters:
        env(TESTING_DATA_INIT): '1'
        testing.data_init: '%env(TESTING_DATA_INIT)%'
    