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

"""Tests for schema.py."""

__author__ = 'aiuto@google.com (Tony Aiuto)'


from google.apputils import basetest

from googleapis.codegen import language_model
from googleapis.codegen.api import Api
from googleapis.codegen.api_exception import ApiException
from googleapis.codegen.schema import Schema


class FakeLanguageModel(language_model.LanguageModel):

  language = 'fake'

  def GetCodeTypeFromDictionary(self, def_dict):
    return def_dict.get('type')

  def ArrayOf(self, unused_var, s):
    return 'Array[%s]' % s

  def ToMemberName(self, s, unused_api):
    return s.replace('-', '_')


def MakeApiWithSchemas(schemas):
  discovery_doc = {
      'name': 'fake',
      'version': 'v1',
      'resources': {},
      'schemas': schemas
      }
  api = Api(discovery_doc)
  api.VisitAll(lambda o: o.SetLanguageModel(FakeLanguageModel()))
  return api


class SchemaTest(basetest.TestCase):
  """Tests for the Schema class."""

  def testArrayOfArray(self):
    api = MakeApiWithSchemas({
        'AdsenseReportsGenerateResponse': {
            'id': 'AdsenseReportsGenerateResponse',
            'type': 'object',
            'properties': {
                'basic': {
                    'type': 'string'
                    },
                'simple_array': {
                    'type': 'array',
                    'items': {'type': 'string'}
                    },
                'array_of_arrays': {
                    'type': 'array',
                    'items': {'type': 'array', 'items': {'type': 'string'}}
                    }
                }
            }
        })
    response_schema = api._schemas.get('AdsenseReportsGenerateResponse')
    self.assertTrue(response_schema)
    prop = [prop for prop in response_schema.values['properties']
            if prop.values['wireName'] == 'array_of_arrays']
    self.assertTrue(len(prop) == 1)
    prop = prop[0]
    self.assertEquals('Array[Array[string]]', prop.codeType)

  def testDetectInvalidSchema(self):
    bad_discovery = {
        'name': 'fake',
        'version': 'v1',
        'resources': {},
        'schemas': {
            'NoItemsInArray': {'id': 'noitems', 'type': 'array'}
            }
        }
    self.assertRaises(ApiException, Api, bad_discovery)

  def testSchemaWithoutProperties(self):
    api = MakeApiWithSchemas({
        'NoProperties': {'id': 'NoProperties', 'type': 'object'}
        })
    for name, schema in api._schemas.items():
      if name == 'NoProperties':
        self.assertEquals(0, len(schema.values.get('properties')))
        return
    self.fail('Did not find NoProperties')

  def testSchemaWithAdditionalPropertiesWithoutId(self):
    api = MakeApiWithSchemas({
        'Snorg': {
            'id': 'Snorg',
            'type': 'object',
            'additionalProperties': {
                'type': 'object',
                'properties': {
                    'thing': {
                        'type': 'boolean'
                        }
                    }
                }
            },
        'SnorgFresser': {
            'id': 'SnorgFresser',
            'type': 'object',
            'properties': {
                'snacks': {
                    'type': 'array',
                    'items': {
                        '$ref': 'Snorg'
                        }
                    }
                }
            }
        })
    schemas = api._schemas
    self.assertTrue('SnorgFresser' in schemas)
    self.assertTrue('Snorg' in schemas)
    self.assertTrue('SnorgElement' in schemas)
    snorg = api.SchemaByName('Snorg')
    self.assertTrue(snorg)
    self.assertFalse('Snorg' in api.ModelClasses())
    snorg_element = api.SchemaByName('SnorgElement')
    self.assertTrue(snorg_element)
    self.assertTrue(snorg_element in api.ModelClasses())

  def testNestedSchemaWithAdditionalProperties(self):
    api = MakeApiWithSchemas({
        'RestDescription': {
            'id': 'RestDescription',
            'type': 'object',
            'properties': {
                'auth': {
                    'type': 'object',
                    'properties': {
                        'oauth2': {
                            'type': 'object',
                            'properties': {
                                'scopes': {
                                    'type': 'object',
                                    'additionalProperties': {
                                        'type': 'object',
                                        'properties': {
                                            'description': {
                                                'type': 'string',
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                }
            }
        })

    expected_names = {'RestDescription',
                      'RestDescriptionAuth',
                      'RestDescriptionAuthOauth2',
                      'RestDescriptionAuthOauth2Scopes',
                      'RestDescriptionAuthOauth2ScopesElement'}
    schema_names = set(x.values.get('className') for x
                       in api._schemas.itervalues())
    self.assertEquals(expected_names, schema_names)
    scopes_elem = api._schemas['RestDescription.auth.oauth2.scopesElement']
    self.assertEquals('ScopesElement', scopes_elem.safe_code_type)
    self.assertEquals('RestDescriptionAuthOauth2ScopesElement',
                      scopes_elem.code_type)
    oauth2_elem = api._schemas['RestDescription.auth.oauth2']
    self.assertEquals('Oauth2', oauth2_elem.safe_code_type)
    self.assertEquals('RestDescriptionAuthOauth2', oauth2_elem.code_type)

  def testSchemaWithAdditionalPropertiesWithId(self):
    api = MakeApiWithSchemas({
        'Snorg': {
            'id': 'Snorg',
            'type': 'object',
            'additionalProperties': {
                'id': 'Skrimpkin',
                'type': 'object',
                'properties': {
                    'thing': {
                        'type': 'boolean'
                        }
                    }
                }
            },
        'SnorgFresser': {
            'id': 'SnorgFresser',
            'type': 'object',
            'properties': {
                'snacks': {
                    'type': 'array',
                    'items': {
                        '$ref': 'Snorg'
                        }
                    }
                }
            }
        })
    schemas = api._schemas
    self.assertTrue('SnorgFresser' in schemas)
    self.assertTrue('Snorg' in schemas)
    self.assertTrue('Skrimpkin' in schemas)
    snorg = api.SchemaByName('Snorg')
    self.assertTrue(snorg)
    self.assertFalse('Snorg' in api.ModelClasses())
    skrimpkin = api.SchemaByName('Skrimpkin')
    self.assertTrue(skrimpkin)
    self.assertTrue(skrimpkin in api.ModelClasses())

  def testUndefinedSchema(self):
    gen = MakeApiWithSchemas({
        'foo': {
            'id': 'foo',
            'type': 'object',
            'properties': {'basic': {'$ref': 'bar'}}
            }
        })
    # We expect foo to be in the list because the id is 'foo'
    self.assertTrue('foo' in gen._schemas.keys())
    # We expect 'Foo' to be in the list because that is the class name we would
    # create for foo
    self.assertTrue('foo' in gen._schemas.keys())
    # We do not expect Bar to be in the list because we only have a ref to it
    # but no definition.
    self.assertFalse('Bar' in gen._schemas.keys())

  def testSchemaWithNameClash(self):
    clashing_names = {
        'Snorg': {
            'id': 'Snorg',
            'type': 'object',
            'properties': {
                'thing': {
                    'type': 'boolean'
                },
                '@thing': {
                    'type': 'boolean'
                }
            }
        }
    }
    self.assertRaises(ApiException, MakeApiWithSchemas, clashing_names)

  def testWrappedContainer(self):
    discovery_doc = {
        'name': 'fake',
        'version': 'v1',
        }
    api = Api(discovery_doc)
    wrapped_container_def = {
        'id': 'SeriesList',
        'type': 'object',
        'properties': {
            'items': {
                'type': 'array',
                'items': {
                    '$ref': 'Snorg'
                    }
                },
            }
        }
    schema = Schema.Create(api, 'foo', wrapped_container_def, 'foo', None)
    self.assertEquals(1, len(schema.properties))
    self.assertIsNotNone(schema.isContainerWrapper)
    container_property = schema.containerProperty
    self.assertIsNotNone(container_property)
    array_of = container_property.data_type.GetTemplateValue('arrayOf')
    self.assertIsNotNone(array_of)
    self.assertEquals('Snorg', array_of.values['wireName'])

    # Add a kind
    wrapped_container_def['properties'].update({'kind': {'type': 'string'}})
    schema = Schema.Create(api, 'foo', wrapped_container_def, 'foo', None)
    self.assertEquals(2, len(schema.properties))
    self.assertTrue(schema.isContainerWrapper)

    # Add an etag
    wrapped_container_def['properties'].update({'etag': {'type': 'string'}})
    schema = Schema.Create(api, 'foo', wrapped_container_def, 'foo', None)
    self.assertEquals(3, len(schema.properties))
    self.assertTrue(schema.isContainerWrapper)

    # Add a field which disqualifies
    wrapped_container_def['properties'].update({'foo': {'type': 'string'}})
    schema = Schema.Create(api, 'foo', wrapped_container_def, 'foo', None)
    self.assertEquals(4, len(schema.properties))
    self.assertFalse(schema.isContainerWrapper)

    # Make the main property not a container
    not_wrapped_container_def = {
        'id': 'SeriesList',
        'type': 'object',
        'properties': {
            'items': {'type': 'string'},
            'kind': {'type': 'string'}
            }
        }
    schema = Schema.Create(api, 'foo', not_wrapped_container_def, 'foo', None)
    self.assertEquals(2, len(schema.properties))
    self.assertFalse(schema.isContainerWrapper)

  def testMemberNameIsJsonName(self):
    api = MakeApiWithSchemas({
        's': {
            'type': 'object',
            'properties': {
                'easyname': {
                    'type': 'string',
                    'expect': True,
                    },
                'dashed-name': {
                    'type': 'string',
                    'expect': False,
                    }
                }
            }
        })
    for name, schema in api._schemas.items():
      if name == 's':
        expect_len = len(schema.values.get('properties'))
        got = 0
        for p in schema.values.get('properties'):
          self.assertEquals(
              p.values['expect'], p.member_name_is_json_name,
              'Unexpected code name %s for json name: %s' % (
                  p.memberName, p.values['wireName']))
          got += 1
        self.assertEquals(expect_len, got)
        return
    self.fail('Did not find schema')


if __name__ == '__main__':
  basetest.main()
