#!/usr/bin/python2.7
# -*- coding: utf-8 -*-
#
# Copyright 2010 Google Inc. All Rights Reserved.
#
#  Licensed under the Apache License, Version 2.0 (the "License");
#  you may not use this file except in compliance with the License.
#  You may obtain a copy of the License at
#
#      http://www.apache.org/licenses/LICENSE-2.0
#
#  Unless required by applicable law or agreed to in writing, software
#  distributed under the License is distributed on an "AS IS" BASIS,
#  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
#  See the License for the specific language governing permissions and
#  limitations under the License.

"""Tests for utilities.py."""

__author__ = 'aiuto@google.com (Tony Aiuto)'

from google.apputils import basetest
import googleapis.codegen.utilities as utilities


class UtilitiesTest(basetest.TestCase):

  def testCamelCase(self):
    """Basic CamelCase functionality."""
    self.assertEquals('HelloWorld', utilities.CamelCase('hello_world'))
    self.assertEquals('HelloWorld', utilities.CamelCase('hello-world'))
    self.assertEquals('HelloWorld', utilities.CamelCase('helloWorld'))
    self.assertEquals('HelloWorld', utilities.CamelCase('Hello_world'))
    self.assertEquals('HelloWorld', utilities.CamelCase('_hello_world'))
    self.assertEquals('HelloWorld', utilities.CamelCase('helloWorld'))
    self.assertEquals('HelloWorld', utilities.CamelCase('hello.world'))
    self.assertEquals('HELLOWORLD', utilities.CamelCase('HELLO_WORLD'))
    self.assertEquals('HelloWorld', utilities.CamelCase('hello/world'))
    self.assertEquals('HelloWorld', utilities.CamelCase('/hello/world/'))
    self.assertEquals('', utilities.CamelCase(''))
    self.assertEquals(' ', utilities.CamelCase(' '))
    self.assertEquals(' ', utilities.CamelCase('. '))

  def testUnCamelCase(self):
    """Basic CamelCase functionality."""
    # standard case
    self.assertEquals('hello_world', utilities.UnCamelCase('helloWorld'))
    self.assertEquals('hello_world', utilities.UnCamelCase('Hello_world'))
    self.assertEquals('hello_world', utilities.UnCamelCase('helloWorld'))
    self.assertEquals('hello_world', utilities.UnCamelCase('HELLO_WORLD'))
    self.assertEquals('hello_world', utilities.UnCamelCase('HELLOworld'))
    self.assertEquals('hello_world', utilities.UnCamelCase('helloWORLD'))
    self.assertEquals('hello2_world', utilities.UnCamelCase('Hello2World'))

    # keep existing separators
    self.assertEquals('hello_world', utilities.UnCamelCase('hello_world'))
    self.assertEquals('_hello_world', utilities.UnCamelCase('_hello_world'))
    self.assertEquals('_hello_world', utilities.UnCamelCase('_HelloWorld'))
    self.assertEquals('hello__world', utilities.UnCamelCase('Hello__World'))

    # embedded acronym
    self.assertEquals('hello_xw_orld', utilities.UnCamelCase('HelloXWorld'))

    # minimal input
    self.assertEquals('h', utilities.UnCamelCase('H'))
    self.assertEquals('', utilities.UnCamelCase(''))

    # Other cases involving expanded alphabet.
    self.assertEquals('_', utilities.UnCamelCase('_'))
    self.assertEquals('hello-world', utilities.UnCamelCase('hello-world'))
    self.assertEquals('hello.world', utilities.UnCamelCase('hello.world'))
    self.assertEquals('hello/world', utilities.UnCamelCase('hello/world'))
    self.assertEquals('hello world', utilities.UnCamelCase('Hello World'))
    self.assertEquals(' ', utilities.UnCamelCase(' '))

  def testSanitizeDomain(self):
    self.assertIsNone(utilities.SanitizeDomain(None))
    self.assertEquals('google.com', utilities.SanitizeDomain('google.com'))
    self.assertEquals('google.com', utilities.SanitizeDomain('GooglE.com'))
    self.assertEquals('google.com', utilities.SanitizeDomain('goo|gle.com'))
    self.assertEquals('google.com', utilities.SanitizeDomain('goo gle.com'))
    self.assertEquals('googl.com', utilities.SanitizeDomain('googlê.com'))
    self.assertEquals('www_test.appspot.com',
                      utilities.SanitizeDomain('www-test.appspot.com'))

  def testReversedDomainComponents(self):
    self.assertEquals([],
                      utilities.ReversedDomainComponents(''))
    self.assertEquals(['com', 'google'],
                      utilities.ReversedDomainComponents('google.com'))

  def testNoSpaces(self):
    self.assertIsNone(utilities.NoSpaces(None))
    self.assertEquals('', utilities.NoSpaces(''))
    self.assertEquals('', utilities.NoSpaces(' '))
    self.assertEquals('abc', utilities.NoSpaces('a b  c '))

if __name__ == '__main__':
  basetest.main()
