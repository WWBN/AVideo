[![Build Status](https://travis-ci.org/singpolyma/openpgp-php.svg?branch=master)](https://travis-ci.org/singpolyma/openpgp-php)

OpenPGP.php: OpenPGP for PHP
============================

This is a pure-PHP implementation of the OpenPGP Message Format (RFC 4880).

* <https://github.com/singpolyma/openpgp-php>

About OpenPGP
-------------

OpenPGP is the most widely-used e-mail encryption standard in the world. It
is defined by the OpenPGP Working Group of the Internet Engineering Task
Force (IETF) Proposed Standard RFC 4880. The OpenPGP standard was originally
derived from PGP (Pretty Good Privacy), first created by Phil Zimmermann in
1991.

* <https://tools.ietf.org/html/rfc4880>
* <https://www.openpgp.org/>

Features
--------

* Encodes and decodes ASCII-armored OpenPGP messages.
* Parses OpenPGP messages into their constituent packets.
  * Supports both old-format (PGP 2.6.x) and new-format (RFC 4880) packets.
* Helper class for verifying, signing, encrypting, and decrypting messages <http://phpseclib.sourceforge.net>
* Helper class for encrypting and decrypting messages and keys using <http://phpseclib.sourceforge.net>
  * openssl or mcrypt required for CAST5 encryption and decryption

Bugs, Feature Requests, Patches
-------------------------------

This project is primarily maintained by a single volunteer with many other
things vying for their attention, please be patient.

Bugs, feature request, pull requests, patches, and general discussion may
be submitted publicly via email to: dev@singpolyma.net

Github users may alternately submit on the web there.

Users
-----

OpenPGP.php is currently being used in the following projects:

* <https://wordpress.org/plugins/wp-pgp-encrypted-emails/>

Download
--------

To get a local working copy of the development repository, do:

    git clone https://github.com/singpolyma/openpgp-php.git

Alternatively, you can download the latest development version as a tarball
as follows:

    wget https://github.com/singpolyma/openpgp-php/tarball/master

Authors
-------

* [Arto Bendiken](mailto:arto.bendiken@gmail.com) (Original author) - <http://ar.to/>
* [Stephen Paul Weber](mailto:singpolyma@singpolyma.net) (Maintainer) - <https://singpolyma.net/>

License
-------

OpenPGP.php is free and unencumbered public domain software. For more
information, see <https://unlicense.org/> or the accompanying UNLICENSE file.
