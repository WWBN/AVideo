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

"""Tests for php_generator."""

__author__ = 'chirags@google.com (Chirag Shah)'



from google.apputils import basetest
from googleapis.codegen import api
from googleapis.codegen import php_generator
from googleapis.codegen import schema


class PHPApiTest(basetest.TestCase):

  def setUp(self):
    gen_params = {'name': 'test', 'version': 'v1', 'resources': {}}
    self.api = php_generator.PHPApi(gen_params)
    self.generator = php_generator.PHPGenerator(gen_params)
    self.language_model = php_generator.PhpLanguageModel()
    # TODO(user): Do what we did for template_helpers and allow language
    # model to come from global state. Then we don't need this stuff.
    self.api.VisitAll(lambda o: o.SetLanguageModel(self.language_model))

  def tearDown(self):
    self.api = None

  def testAnnotateMethod(self):
    param_dict = {'httpMethod': 'GET',
                  'id': 'myservice.foo.count',
                  'parameters': {'alt': {}}}
    method = api.Method(self.api, 'count', param_dict)
    self.generator.AnnotateMethod(self.api, method)

    self.assertEquals('myservice.foo.count', method.values['id'])
    self.assertEquals('count', method.values['name'])
    self.assertEquals('count', method.values['wireName'])
    self.assertEquals('Count', method.values['className'])

  def testSetTypeHint(self):
    """Test creating safe class names from object names."""
    test_schema = api.Schema(self.api, 'testSchema', {})
    type_to_hint = [
        ({'$ref': 'Activity'}, 'Google_Service_Test_Activity'),
        ({'type': 'boolean'}, ''),
        ({'type': 'integer'}, ''),
        ({'type': 'string'}, ''),  # PHP doesn't support primitive type hints.
        ({'type': 'StRing'}, ''),  # PHP var names are case-insensitive.
        ({'$ref': 'Photo'}, 'Google_Service_Test_Photo'),
        ({'type': 'array', 'items': {'type': 'string'}}, ''),
        ({'type': 'object', 'properties': {'p1': {'type': 'string'}}},
         'Google_Service_Test_TestSchemaTest'),
        ]
    for type_dict, expected_hint in type_to_hint:
      test_property = schema.Property(self.api, test_schema, 'test', type_dict)
      test_property.SetLanguageModel(self.language_model)
      self.generator._SetTypeHint(test_property)
      self.assertEquals(expected_hint, test_property.values['typeHint'])

  def testToMethodName(self):
    """Test creating safe method names from wire names."""
    method = {'wireName': 'foo'}
    method_name = self.generator._ToMethodName(method, None)
    self.assertEquals('foo', method_name)

    # Method name that doesn't conflict with a PHP keyword.
    method['wireName'] = 'get'
    resource = {'className': 'ResourceClassName'}
    method_name = self.generator._ToMethodName(method, resource)
    self.assertEquals('get', method_name)

    # Method name that conflicts with a PHP keyword.
    method['wireName'] = 'as'
    resource['className'] = 'Class'
    method_name = self.generator._ToMethodName(method, resource)
    self.assertEquals('asClass', method_name)

    # Method name that conflicts with a canonical PHP keyword.
    method['wireName'] = 'aS'
    method_name = self.generator._ToMethodName(method, resource)
    self.assertEquals('aSClass', method_name)

  def testToClassName(self):
    """Test creating safe class names from object names."""
    self.assertEquals('Foo', self.api.ToClassName('foo', None))
    self.assertEquals('TestObject', self.api.ToClassName('object', None))
    self.assertEquals('TestString', self.api.ToClassName('string', None))

  def testGetCodeTypeFromDictionary(self):
    """Test mapping of JSON schema types to PHP class names."""

    php_type_to_schema = [('object', {'type': 'object'}),
                          ('string', {'type': 'string'}),
                          ('array', {'type': 'any'}),
                          ('bool', {'type': 'boolean'}),
                          ('int', {'type': 'integer'}),

                          ('string', {'type': 'number', 'format': 'uint32'}),
                          ('string', {'type': 'integer', 'format': 'uint32'}),
                          ('string', {'type': 'string', 'format': 'uint32'}),

                          ('string', {'type': 'number', 'format': 'uint64'}),
                          ('string', {'type': 'integer', 'format': 'uint64'}),
                          ('string', {'type': 'string', 'format': 'uint64'}),

                          ('int', {'type': 'number', 'format': 'int32'}),
                          ('int', {'type': 'integer', 'format': 'int32'}),
                          ('int', {'type': 'string', 'format': 'int32'}),

                          ('string', {'type': 'number', 'format': 'int64'}),
                          ('string', {'type': 'integer', 'format': 'int64'}),
                          ('string', {'type': 'string', 'format': 'int64'}),

                          ('string', {'type': 'string',
                                      'format': 'date-time'}),

                          ('double', {'type': 'number', 'format': 'double'}),
                          ('double', {'type': 'string', 'format': 'double'}),

                          ('float', {'type': 'number', 'format': 'float'}),
                          ('float', {'type': 'string', 'format': 'float'})]

    for schema_obj in php_type_to_schema:
      php_type = schema_obj[0]
      s = schema_obj[1]
      self.assertEquals(php_type,
                        self.language_model.GetCodeTypeFromDictionary(s))

if __name__ == '__main__':
  basetest.main()
