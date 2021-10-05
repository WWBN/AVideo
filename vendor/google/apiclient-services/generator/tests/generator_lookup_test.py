#!/usr/bin/python2.7
# Copyright 2012 Google Inc. All Rights Reserved.
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

"""Tests for generator_lookup."""

__author__ = 'akesling@google.com (Alex Kesling)'

import json
import os


from google.apputils import basetest

from googleapis.codegen import generator_lookup
from googleapis.codegen import php_generator
from googleapis.codegen import targets


class GeneratorLookupTest(basetest.TestCase):

  def testDetermineGenerator(self):
    test_gen = generator_lookup.GetGeneratorByLanguage('php')
    self.assertEqual(php_generator.PHPGenerator, test_gen)
    self.assertRaises(
        ValueError, generator_lookup.GetGeneratorByLanguage,
        'I\'m an invalid language!')

  def testSupportedLanguage(self):
    languages = generator_lookup.SupportedLanguages()
    self.assertContainsSubset(['php'], languages)
    self.assertNotIn('php-head', languages)

  def testVersionFromFeature(self):
    template_root = os.path.join(os.path.dirname(__file__),
                                 'testdata/languages')
    targets.Targets.SetDefaultTemplateRoot(template_root)
    features_path = os.path.join(template_root,
                                 'php/generator_test/features.json')
    raw_features = json.load(open(features_path))
    generator_name = raw_features['generator']
    gen = generator_lookup.GetGeneratorByLanguage(generator_name)
    self.assertEquals(php_generator.PHPGenerator, gen)


if __name__ == '__main__':
  basetest.main()
