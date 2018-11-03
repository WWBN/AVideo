#!/bin/bash
set -e
set -o pipefail

if [[ "$TRAVIS_PHP_VERSION" != "hhvm" &&
      "$TRAVIS_PHP_VERSION" != "hhvm-nightly" ]]; then

    # install 'event' PHP extension
    echo "yes" | pecl install event

    # install 'libevent' PHP extension (does not support php 7)
    if [[ "$TRAVIS_PHP_VERSION" != "7.0" &&
          "$TRAVIS_PHP_VERSION" != "7.1" ]]; then
        curl http://pecl.php.net/get/libevent-0.1.0.tgz | tar -xz
        pushd libevent-0.1.0
        phpize
       ./configure
       make
       make install
       popd
       echo "extension=libevent.so" >> "$(php -r 'echo php_ini_loaded_file();')"
    fi

    # install 'libev' PHP extension (does not support php 7)
    if [[ "$TRAVIS_PHP_VERSION" != "7.0" &&
          "$TRAVIS_PHP_VERSION" != "7.1" ]]; then
        git clone --recursive https://github.com/m4rw3r/php-libev
        pushd php-libev
        phpize
        ./configure --with-libev
        make
        make install
        popd
        echo "extension=libev.so" >> "$(php -r 'echo php_ini_loaded_file();')"
    fi

fi
