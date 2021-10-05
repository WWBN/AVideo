#!/usr/bin/python2.7
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

"""Tests for template_objects.py."""

__author__ = 'akesling@google.com (Alex Kesling)'

from google.apputils import basetest

from googleapis.codegen import data_types
from googleapis.codegen import data_value
from googleapis.codegen import language_model
from googleapis.codegen.template_objects import CodeObject


class LanguageModelTest(basetest.TestCase):

  def setUp(self):
    self.language_model = language_model.LanguageModel()

  def _CreateDataValue(self, value, val_type):
    def_dict = {
        'className': 'Foo',
        'type': val_type,
        }
    prototype = data_types.DataType(
        def_dict, None, language_model=self.language_model)
    dv = data_value.DataValue(value, prototype)
    return dv

  def testConstructor(self):
    foo = self.language_model
    bar = language_model.LanguageModel()

    self.assertEqual(
        foo._SUPPORTED_TYPES.keys(), bar._SUPPORTED_TYPES.keys())

    # Assure that if someone screws up overriding SUPPORTED_TYPES mappings
    # in subclasses we complain in the testing layer.
    foo._SUPPORTED_TYPES['bar'] = 'foo'
    self.assertNotEqual(
        foo._SUPPORTED_TYPES.keys(), bar._SUPPORTED_TYPES.keys())

  def testRenderInteger(self):
    dv = self._CreateDataValue(42, 'integer')
    render_method = self.language_model._SUPPORTED_TYPES['integer']
    self.assertEqual('42', render_method(dv))
    dv.SetValue('forty-two')
    self.assertRaises(ValueError, render_method, dv)

  def testRenderFloat(self):
    dv = self._CreateDataValue(4.2, 'number')
    render_method = self.language_model._SUPPORTED_TYPES['number']
    self.assertEqual('4.2', render_method(dv))

    dv.SetValue(4)
    self.assertEqual('4.0', render_method(dv))

    dv.SetValue('four point two')
    self.assertRaises(ValueError, render_method, dv)

    dv.SetValue(float('inf'))
    self.assertRaises(ValueError, render_method, dv)

  def testRenderString(self):
    dv = self._CreateDataValue('foo', 'string')
    render_method = self.language_model._SUPPORTED_TYPES['string']
    self.assertEqual('"foo"', render_method(dv))

    dv.SetValue('foo "bar"')
    self.assertEqual('"foo \\"bar\\""', render_method(dv))

  def testRenderDataValue(self):
    render_method = self.language_model.RenderDataValue

    string_dv = self._CreateDataValue('foo "bar"', 'string')
    self.assertEqual('"foo \\"bar\\""', render_method(string_dv))

    integer_dv = self._CreateDataValue(42, 'integer')
    self.assertEqual('42', render_method(integer_dv))

    float_dv = self._CreateDataValue(4.2, 'number')
    self.assertEqual('4.2', render_method(float_dv))

    float_dv = self._CreateDataValue(4.2, 'I\'m a valid type!')
    self.assertRaises(ValueError, render_method, float_dv)


class LanguagePolicyTest(basetest.TestCase):

  def testApplyCaseTransform(self):

    class TestLanguageModel(language_model.LanguageModel):
      allowed_characters = '#'

      def __init__(self, **kwargs):
        super(TestLanguageModel, self).__init__(**kwargs)

    m = TestLanguageModel()
    # An identifier with several bad characters, including one at the end
    # which we expect to strip off.
    s = 'I-am -a_tesT@'
    p = language_model.NamingPolicy(case_transform=language_model.LOWER_CASE)
    self.assertEquals('iamatest', m.ApplyCaseTransform(s, p))
    p = language_model.NamingPolicy(
        case_transform=language_model.PRESERVE_CASE,
        separator='_')
    self.assertEquals('I_am_a_tesT', m.ApplyCaseTransform(s, p))
    p = language_model.NamingPolicy(
        case_transform=language_model.UPPER_CASE,
        separator='_')
    self.assertEquals('I_AM_A_TEST', m.ApplyCaseTransform(s, p))
    p = language_model.NamingPolicy(
        case_transform=language_model.LOWER_CASE,
        separator='_')
    self.assertEquals('i_am_a_test', m.ApplyCaseTransform(s, p))
    p = language_model.NamingPolicy(
        case_transform=language_model.UPPER_CAMEL_CASE)
    self.assertEquals('IAmATesT', m.ApplyCaseTransform(s, p))
    p = language_model.NamingPolicy(
        case_transform=language_model.LOWER_CAMEL_CASE)
    self.assertEquals('iAmATesT', m.ApplyCaseTransform(s, p))
    p = language_model.NamingPolicy(
        case_transform=language_model.UPPER_CAMEL_CASE)
    s = 'allow#this'
    self.assertEquals('Allow#this', m.ApplyCaseTransform(s, p))

  def testApplyFormat(self):

    class TestLanguageModel(language_model.LanguageModel):
      allowed_characters = '#'
      member_policy = language_model.NamingPolicy(
          case_transform=language_model.UPPER_CAMEL_CASE,
          format_string='{api_name}!{parent_name}!{name}')

      def __init__(self, **kwargs):
        super(TestLanguageModel, self).__init__(**kwargs)

    m = TestLanguageModel()
    api = CodeObject({'name': 'my_api'}, None, wire_name='my_api')
    schema = CodeObject({'name': 'my_schema'}, api, wire_name='my_schema',
                        parent=api)
    member = CodeObject({'name': 'object'}, api, parent=schema)
    # Note: the transform will happen for the extra variables. The actual
    # member name is not transformed in this case, because we are only calling
    # ApplyFormat.
    self.assertEquals('MyApi!MySchema!some_string',
                      m.ApplyFormat(member, 'some_string', m.member_policy))
    self.assertEquals('MyApi!MySchema!SomeString',
                      m.ApplyPolicy('member', member, 'some_string'))

  def testPoliciesGetUsedInTheRightMethods1(self):

    class TestLanguageModel(language_model.LanguageModel):
      class_name_policy = language_model.NamingPolicy(
          case_transform=language_model.UPPER_CAMEL_CASE,
          separator='!CLASS!',
          format_string='C {name}')

      constant_policy = language_model.NamingPolicy(
          case_transform=language_model.UPPER_CASE,
          separator='!CONSTANT!',
          format_string='K {name}')

      def __init__(self, **kwargs):
        super(TestLanguageModel, self).__init__(**kwargs)

    m = TestLanguageModel()
    max_results = 'max-results'

    self.assertEquals('C Max!CLASS!Results',
                      m.ApplyPolicy('class_name', None, max_results))
    self.assertEquals('K MAX!CONSTANT!RESULTS',
                      m.ApplyPolicy('constant', None, max_results))

  def testPoliciesGetUsedInTheRightMethods2(self):

    class TestLanguageModel(language_model.LanguageModel):
      member_policy = language_model.NamingPolicy(
          case_transform=language_model.LOWER_CASE,
          separator='!MEMBER!',
          format_string='M {name}')

      getter_policy = language_model.NamingPolicy(
          case_transform=language_model.LOWER_CAMEL_CASE,
          separator='!GETTER!',
          format_string='G {name}')

      setter_policy = language_model.NamingPolicy(
          case_transform=language_model.UPPER_CAMEL_CASE,
          separator='!SETTER!',
          format_string='S {name}')

      def __init__(self, **kwargs):
        super(TestLanguageModel, self).__init__(**kwargs)

    m = TestLanguageModel()
    max_results = 'max-results'

    self.assertEquals('M max!MEMBER!results',
                      m.ApplyPolicy('member', None, max_results))
    self.assertEquals('G max!GETTER!Results',
                      m.ApplyPolicy('getter', None, max_results))
    self.assertEquals('S Max!SETTER!Results',
                      m.ApplyPolicy('setter', None, max_results))

  def testArrayOf(self):

    class TestLanguageModel(language_model.LanguageModel):
      # transform and separator are not used for array of
      array_of_policy = language_model.NamingPolicy(
          case_transform=language_model.UPPER_CAMEL_CASE,
          separator='will not be used',
          format_string='Array[{name}]')

      def __init__(self, **kwargs):
        super(TestLanguageModel, self).__init__(**kwargs)

    m = TestLanguageModel()
    self.assertEquals('Array[foo-bar]', m.ArrayOf(None, 'foo-bar'))

  def testAtSignPolicy(self):

    class TestLanguageModel(language_model.LanguageModel):
      allowed_characters = '#'

      def __init__(self, **kwargs):
        super(TestLanguageModel, self).__init__(**kwargs)

    m = TestLanguageModel()

    # An identifier with several bad characters, including one at the end
    # which we expect to strip off.
    p = language_model.NamingPolicy(
        case_transform=language_model.UPPER_CAMEL_CASE)
    self.assertEquals('Myname', m.ApplyCaseTransform('my@name', p))
    self.assertEquals('Name', m.ApplyCaseTransform('@name', p))

    p = language_model.NamingPolicy(
        case_transform=language_model.UPPER_CAMEL_CASE,
        atsign_policy=language_model.ATSIGN_STRIP)
    self.assertEquals('Myname', m.ApplyCaseTransform('my@name', p))
    self.assertEquals('Name', m.ApplyCaseTransform('@name', p))

    p = language_model.NamingPolicy(
        case_transform=language_model.UPPER_CAMEL_CASE,
        atsign_policy=language_model.ATSIGN_BREAK)
    self.assertEquals('MyName', m.ApplyCaseTransform('my@name', p))
    self.assertEquals('Name', m.ApplyCaseTransform('@name', p))


if __name__ == '__main__':
  basetest.main()
