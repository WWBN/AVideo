# RFC6455 - The WebSocket Protocol

[![Build Status](https://travis-ci.org/ratchetphp/RFC6455.svg?branch=master)](https://travis-ci.org/ratchetphp/RFC6455)
![Autobahn Testsuite](https://img.shields.io/badge/Autobahn-passing-brightgreen.svg)

This library a protocol handler for the RFC6455 specification.
It contains components for both server and client side handshake and messaging protocol negotation.

Aspects that are left open to interpertation in the specification are also left open in this library.
It is up to the implementation to determine how those interpertations are to be dealt with.

This library is independent, framework agnostic, and does not deal with any I/O.
HTTP upgrade negotiation integration points are handled with PSR-7 interfaces.
