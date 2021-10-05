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

"""Tests for data_value.py."""


from google.apputils import basetest

from googleapis.codegen import data_types
from googleapis.codegen import data_value
from googleapis.codegen import language_model
from googleapis.codegen import schema
from django import template as django_template  # pylint: disable=g-bad-import-order


class DataValueTest(basetest.TestCase):

  def setUp(self):
    super(DataValueTest, self).setUp()
    self.language_model = language_model.LanguageModel(class_name_delimiter='|')

  def testDataValue(self):
    foo_def_dict = {
        'className': 'Foo',
        'type': 'string',
        }
    prototype = data_types.DataType(
        foo_def_dict, None, language_model=self.language_model)
    dv = data_value.DataValue(3, prototype)

    # Basic Checks
    self.assertEqual(3, dv.value)
    self.assertEqual(prototype, dv.data_type)
    self.assertEqual({}, dv.metadata)
    self.assertEqual('Foo', dv.code_type)

    dv.metadata['foo'] = 'bar'
    self.assertEqual({'foo': 'bar'}, dv.metadata)

    dv.SetValue('four')
    self.assertEqual(dv.value, 'four')

    self.assertEqual(self.language_model, dv.GetLanguageModel())
    other_language_model = language_model.LanguageModel(
        class_name_delimiter='+')
    dv.SetLanguageModel(other_language_model)
    self.assertEqual(other_language_model, dv.GetLanguageModel())

    # Now that we've set a local language model... make sure the codepath
    # for setting the data_type's language model gets exercised.
    self.assertEqual('Foo', dv.code_type)

    # Check that the constructor doesn't freak if an odd object is passed in
    dv = data_value.DataValue(object, prototype)
    self.assertEqual(dv.value, object)

    # A standard case is the prototype being a Property object.  It is not
    # uncommon that the Property's data_type is a SchemaReference. To verify
    # this case is handled correctly we must fake an API.
    bar_def_dict = {
        'className': 'Foo',
        'type': 'string',
        }

    class MockApi(object):

      def __init__(self):
        self.model_module = None

      def SetSchema(self, s):
        self.schema = s

      def SetSchemaRef(self, schema_ref):
        self.schema_ref = schema_ref

      # pylint: disable=unused-argument
      def ToClassName(self, name, element, element_type):
        return name

      # pylint: disable=unused-argument
      def SchemaByName(self, schema_name):
        return self.schema

      # pylint: disable=unused-argument
      def DataTypeFromJson(self, unused_def_dict, tentative_class_name,
                           parent=None, wire_name=None):
        return self.schema_ref

      def NestedClassNameForProperty(self, name, owning_schema):
        return '%s%s' % (owning_schema.class_name, name)

    mock_api = MockApi()
    bar_schema = schema.Schema(mock_api, 'Bar', bar_def_dict)
    mock_api.SetSchema(bar_schema)
    schema_ref = data_types.SchemaReference('Bar', mock_api)
    mock_api.SetSchemaRef(schema_ref)

    prototype = schema.Property(mock_api, schema_ref, 'baz', foo_def_dict)
    dv = data_value.DataValue('3', prototype)
    # Assure all the unwrapping gymnastics in the DataValue constructor did
    # their job correctly.
    self.assertEqual(mock_api.schema, dv.data_type)


class DataValueRenderingTest(basetest.TestCase):
  """Tests for DataValue rendering methods in template_helpers."""

  def _GetContext(self, data=None):
    return django_template.Context(data or {})

  def testDataContextNode(self):
    # This happens to test the "value_of" tag as well.
    lang_model = language_model.LanguageModel('|')
    foo_def_dict = {
        'className': 'Foo',
        'type': 'string',
        }
    prototype = data_types.DataType(
        foo_def_dict, None, language_model=lang_model)
    dv = data_value.DataValue('four', prototype)

    source = '{% value_of data %}'
    template = django_template.Template(source)

    context = self._GetContext({'data': dv})
    self.assertEquals('"four"', template.render(context))

    context = self._GetContext({'data': 'foo'})
    self.assertRaises(
        django_template.TemplateSyntaxError, template.render, context)

    bar_def_dict = {
        'className': 'Foo',
        'type': 'parrot',
        }
    prototype = data_types.DataType(
        bar_def_dict, None, language_model=lang_model)
    dv = data_value.DataValue('fred', prototype)

    context = self._GetContext({'data': dv})
    self.assertRaises(
        django_template.TemplateSyntaxError, template.render, context)


if __name__ == '__main__':
  basetest.main()
