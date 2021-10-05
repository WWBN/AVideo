#!/usr/bin/python2.7
# -*- coding: utf-8 -*-
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
"""Tests for Unicode handling."""

import json
import os

import gflags as flags
from google.apputils import basetest
from googleapis.codegen import api

FLAGS = flags.FLAGS



class UnicodeTest(basetest.TestCase):

  _TEST_DISCOVERY_DOC = 'unicode.json'

  def ApiFromDiscoveryDoc(self, path):
    """Load a discovery doc from a file and creates a library Api.

    Args:
      path: (str) The path to the document.

    Returns:
      An Api for that document.
    """

    with open(os.path.join(os.path.dirname(__file__), 'testdata', path)) as f:
      discovery_doc = json.loads(f.read().decode('utf-8'))
    return api.Api(discovery_doc)

  def testGiveMeAName(self):
    an_api = self.ApiFromDiscoveryDoc(self._TEST_DISCOVERY_DOC)

    accented = u'\xdaRL'  # "URL" with an accent

    # An object which holds a count. This is just to have an object to
    # increment as a side-effect of a lambda.
    class Counter(object):
      value = 0

      def Increment(self, expr):
        self.value += expr or 0

    def CheckDescription(counter, x, match):
      """Does a CodeObject object contain a string in its description."""
      counter.Increment(match in (x.values.get('description') or ''))

    # Look for 'RL' for a baseline
    rl_counter = Counter()
    an_api.VisitAll(lambda x: CheckDescription(rl_counter, x, 'RL'))
    self.assertLess(6, rl_counter.value)

    url_counter = Counter()
    an_api.VisitAll(lambda x: CheckDescription(url_counter, x, accented))
    self.assertEquals(rl_counter.value, url_counter.value)

    def CheckEnumDescription(counter, x, match):
      enum_type = x.values.get('enumType')
      if enum_type:
        for _, _, description in enum_type.values.get('pairs') or []:
          counter.Increment(match in description)

    enum_counter = Counter()
    an_api.VisitAll(lambda x: CheckEnumDescription(enum_counter, x, accented))
    self.assertEquals(2, enum_counter.value)


if __name__ == '__main__':
  basetest.main()
