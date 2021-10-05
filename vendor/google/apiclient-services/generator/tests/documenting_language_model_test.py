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

"""Tests for language_model.DocumentingLanguageModel."""

__author__ = 'aiuto@google.com (Tony Aiuto)'

from google.apputils import basetest
from googleapis.codegen import language_model


class DocumentingLanguageModelTest(basetest.TestCase):

  def testDocumentingLanguageModel(self):
    dlm = language_model.DocumentingLanguageModel()
    self.assertEquals('Array<foo>', dlm.ArrayOf(None, 'foo'))
    self.assertEquals('Map<string, foo>', dlm.MapOf(None, 'foo'))
    self.assertEquals('foo', dlm.GetCodeTypeFromDictionary({'type': 'foo'}))
    self.assertEquals('foo (int)', dlm.GetCodeTypeFromDictionary({
        'type': 'foo', 'format': 'int'}))


if __name__ == '__main__':
  basetest.main()
