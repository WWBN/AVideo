#!/usr/bin/python2.7
# Copyright 2011 Google Inc. All Rights Reserved.
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


__author__ = 'aiuto@google.com (Tony Aiuto)'



from google.apputils import basetest
from googleapis.codegen import data_types
from googleapis.codegen import language_model
from googleapis.codegen import template_objects


class DataTypesTest(basetest.TestCase):

  def testVoidDataTypeDefault(self):
    api = template_objects.CodeObject({}, None)
    void = data_types.Void(api)
    api.SetLanguageModel(language_model.LanguageModel())
    self.assertEquals('void', void.code_type)

  def testVoidDataTypeOverride(self):
    class FakeLM(language_model.LanguageModel):
      def CodeTypeForVoid(self):
        return 'the absence of all'

    api = template_objects.CodeObject({}, None)
    void = data_types.Void(api)
    api.SetLanguageModel(FakeLM())
    self.assertEquals('the absence of all', void.code_type)


if __name__ == '__main__':
  basetest.main()
