#!/usr/bin/env bash

# This script forms part of the tests for behaviour on environments that have extensive security restrictions or hardening
# In general you do not need to run this suite unless you have changes that interact directly with the PHP runtime or
# the host system. For more information see Tests/BaseCommand/BaseCommandHardenedContainerTest.php

# Limitations:
# This test suite does not currently work on
# - non-linux systems
# - HHVM

php \
-d disable_functions="ini_set" \
-d error_reporting="~E_WARNING & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED" \
vendor/phpunit/phpunit/phpunit \
-c phpunit.hardened.xml