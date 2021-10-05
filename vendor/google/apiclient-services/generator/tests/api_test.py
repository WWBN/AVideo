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

"""Tests for api.py."""

__author__ = 'aiuto@google.com (Tony Aiuto)'

import json
import os


import gflags as flags
from google.apputils import basetest

from googleapis.codegen import data_types
from googleapis.codegen import language_model
from googleapis.codegen.api import Api
from googleapis.codegen.api import AuthScope
from googleapis.codegen.api import Method
from googleapis.codegen.api import Resource
from googleapis.codegen.api import Schema
from googleapis.codegen.api_exception import ApiException

FLAGS = flags.FLAGS



class FakeLanguageModel(language_model.LanguageModel):

  def GetCodeTypeFromDictionary(self, def_dict):
    return def_dict.get('type')

  def ArrayOf(self, unused_var, s):
    return 'Array[%s]' % s


class ApiTest(basetest.TestCase):

  # The base discovery doc for most tests.
  _TEST_DISCOVERY_DOC = 'sample_discovery.json'
  _TEST_DISCOVERY_RPC_DOC = 'sample_discovery.rpc.json'
  _TEST_SHARED_TYPES_DOC = 'sample_shared.json'

  def ApiFromDiscoveryDoc(self, path):
    """Load a discovery doc from a file and creates a library Api.

    Args:
      path: (str) The path to the document.

    Returns:
      An Api for that document.
    """

    f = open(os.path.join(os.path.dirname(__file__), 'testdata', path))
    discovery_doc = json.loads(f.read())
    f.close()
    return Api(discovery_doc)

  def testLazySchemaForCreation(self):
    """Check loading schemas which are known to have a forward reference.

    In the test data, "Activity" refers to "Commment", and the nature
    (sorted) of the loading code causes "Activity" to be processed
    before "Commment".  We want to make sure that SchemaFor does the right
    thing with the lazy creation of activity.
    """
    api = self.ApiFromDiscoveryDoc(self._TEST_DISCOVERY_DOC)
    for schema in ['Activity', 'Comment', 'Activity.object']:
      self.assertTrue(isinstance(api._schemas[schema], Schema))

  def SchemaRefInProperties(self):
    """Make sure that an object ref works in a schema properties list."""
    api = self.ApiFromDiscoveryDoc(self._TEST_DISCOVERY_DOC)
    activity_schema = api._schemas['Activity']
    for prop in activity_schema.values['properties']:
      if prop.values['wireName'] == 'object':
        self.assertEquals('ActivityObject',
                          prop.object_type.values['className'])

  def testMakeDefaultSchemaNameFromTheDictTag(self):
    """Use the outer tag as id for schemas which have no id in their dict."""
    discovery_doc = json.loads(
        """
        {
         "name": "fake",
         "version": "v1",
         "schemas": {
           "should_use_id": {
             "id": "named",
             "type": "object",
             "properties": { "dummy": { "type": "string" } }
           },
           "unnamed": {
             "type": "object",
             "properties": { "dummy": { "type": "string" } }
           }
         },
         "resources": {}
        }
        """)
    gen = Api(discovery_doc)
    self.assertTrue('named' in gen._schemas)
    self.assertTrue('unnamed' in gen._schemas)

  def testUnknownHttpMethod(self):
    """Make sure we get an exception on unknown HTTP types."""
    api = Api({'name': 'dummy', 'version': 'v1', 'resources': {}})
    unused_resource = Resource(api, 'temp', {'methods': {}})
    self.assertRaises(ApiException,
                      Method, api, 'bad', {
                          'rpcMethod': 'rpc',
                          'httpMethod': 'Not GET/POST/PUT/DELETE',
                          'parameters': {}
                      })

  def testRequiredParameterList(self):
    """Make sure we are computing required parameters correctly."""
    api = self.ApiFromDiscoveryDoc(self._TEST_DISCOVERY_DOC)

    tests_executed = 0

    for resource in api.values['resources']:
      if resource.values['wireName'] == 'activities':
        for method in resource.values['methods']:
          if method.required_parameters:
            required_names = [p.values['wireName']
                              for p in method.required_parameters]
            self.assertEquals(method.values['parameterOrder'], required_names)
            tests_executed += 1

    method = api.MethodByName('chili.activities.get')
    optional_names = set(p.values['wireName']
                         for p in method.optional_parameters)
    self.assertEquals(set(['truncateAtom', 'max-comments', 'hl', 'max-liked']),
                      optional_names)
    tests_executed += 1
    self.assertEquals(7, tests_executed)

  def testPageable(self):
    """Make sure pageable methods are identified correctly."""
    api = self.ApiFromDiscoveryDoc(self._TEST_DISCOVERY_DOC)

    # non-pageable method
    count = api.MethodByName('chili.activities.count')
    self.assertIsNone(count.values.get('isPageable'))
    self.assertIsNone(count.values.get('isPagingStyleStandard'))

    # non-pageable method with request page token
    update = api.MethodByName('chili.activities.update')
    self.assertIsNone(update.values.get('isPageable'))
    self.assertIsNone(update.values.get('isPagingStyleStandard'))

    # non-pageable method with response page token
    list_related = api.MethodByName('chili.related.list')
    self.assertIsNone(list_related.values.get('isPageable'))
    self.assertIsNone(list_related.values.get('isPagingStyleStandard'))

    # pageable method with common page token names
    list_activities = api.MethodByName('chili.activities.list')
    self.assertEquals(list_activities.values.get('isPageable'), True)
    self.assertEquals(list_activities.values.get('isPagingStyleStandard'),
                      True)

    # pageable method with uncommon page token names
    list_by_album = api.MethodByName('chili.photos.listByAlbum')
    self.assertEquals(list_by_album.values.get('isPageable'), True)
    self.assertEquals(list_by_album.values.get('isPagingStyleStandard'), False)

    # pageable method with page token in request body
    track = api.MethodByName('chili.activities.track')
    self.assertEquals(track.values.get('isPageable'), True)
    self.assertEquals(track.values.get('isPagingStyleStandard'), False)

  def testSchemaLoadingAsString(self):
    """Test for the "schema as strings" representation."""
    api = self.ApiFromDiscoveryDoc('foo.v1.json')
    self.assertEquals(4, len(api._schemas))

  def testSubResources(self):
    """Test for the APIs with subresources."""

    def CountResourceTree(resource):
      ret = 0
      for r in resource._resources:
        ret += 1 + CountResourceTree(r)
      return ret

    api = self.ApiFromDiscoveryDoc('moderator.v1.json')
    top_level_resources = 0
    total_resources = 0
    non_method_resources = 0
    have_sub_resources = 0
    have_sub_resources_and_methods = 0
    for r in api._resources:
      top_level_resources += 1
      total_resources += 1 + CountResourceTree(r)
      if not r._methods:
        non_method_resources += 1
      if r._resources:
        have_sub_resources += 1
      if r._resources and r._methods:
        have_sub_resources_and_methods += 1
    # Hand counted 18 resources in the file.
    self.assertEquals(18, total_resources)
    self.assertEquals(11, top_level_resources)
    # 4 of them have no methods, only sub resources
    self.assertEquals(4, non_method_resources)
    # 6 of them have sub resources.
    self.assertEquals(6, have_sub_resources)
    # And, of course, 2 should have both sub resources and methods
    self.assertEquals(2, have_sub_resources_and_methods)

  def testParameters(self):
    api = self.ApiFromDiscoveryDoc(self._TEST_DISCOVERY_DOC)
    delete = api.MethodByName('chili.activities.delete')
    self.assertEquals(1, len(delete.query_parameters))
    self.assertEquals(3, len(delete.path_parameters))
    required_p = FindByWireName(delete.values['parameters'],
                                'required_parameter')
    self.assertEquals('query', required_p.location)
    post_id = FindByWireName(delete.values['parameters'], 'postId')
    self.assertEquals('path', post_id.location)

  def testEnums(self):
    gen = self.ApiFromDiscoveryDoc('enums.json')
    # Find the method with the enums
    m1 = gen.MethodByName('language.translations.list')
    language = FindByWireName(m1.values['parameters'], 'language')
    e = language.values['enumType']
    self.assertEquals(m1, e.parent)
    for name, value, desc in e.values['pairs']:
      self.assertTrue(name in ['ENGLISH', 'ITALIAN', 'LANG_ZH_CN',
                               'LANG_ZH_TW'])
      self.assertTrue(value in ['english', 'italian', 'lang_zh-CN',
                                'lang_zh-TW'])
      self.assertTrue(desc in ['English (US)', 'Italian',
                               'Chinese (Simplified)', 'Chinese (Traditional)'])
    accuracy = FindByWireName(m1.values['parameters'], 'accuracy')
    e = accuracy.values['enumType']
    self.assertEquals(m1, e.parent)
    for name, value, desc in e.values['pairs']:
      self.assertTrue(name in ['VALUE_1', 'VALUE_2', 'VALUE_3'])
      self.assertTrue(value in ['1', '2', '3'])

  def testArrayParameter(self):
    api = self.ApiFromDiscoveryDoc(self._TEST_DISCOVERY_DOC)
    search = api.MethodByName('chili.people.search')
    filter_param = FindByWireName(search.values['parameters'], 'filters')
    self.assertTrue(isinstance(filter_param.data_type,
                               data_types.ArrayDataType))
    self.assertTrue(isinstance(filter_param.data_type._base_type,
                               data_types.PrimitiveDataType))
    self.assertEquals('string',
                      filter_param.data_type._base_type.values['type'])

  def testRepeatedEnum(self):
    api = self.ApiFromDiscoveryDoc(self._TEST_DISCOVERY_DOC)
    activities = FindByWireName(api.values['resources'], 'activities')
    list_method = FindByWireName(activities.values['methods'], 'list')
    options = [p for p in list_method.values['parameters']
               if p.values['wireName'] == 'options'][0]
    # Should be an array of enums of type string
    self.assertTrue(isinstance(options.data_type, data_types.ArrayDataType))
    self.assertTrue(isinstance(options.data_type._base_type, data_types.Enum))
    self.assertEquals('string', options.data_type._base_type.values['type'])

  def testScopes(self):
    gen = self.ApiFromDiscoveryDoc(self._TEST_DISCOVERY_DOC)
    scopes = gen.GetTemplateValue('authscopes')
    self.assertEquals(2, len(scopes))
    self.assertEquals('https://www.googleapis.com/auth/buzz',
                      scopes[0].GetTemplateValue('value'))
    self.assertEquals('BUZZ',
                      scopes[0].GetTemplateValue('name'))
    self.assertEquals('https://www.googleapis.com/auth/buzz.read-only',
                      scopes[1].GetTemplateValue('value'))
    self.assertEquals('BUZZ_READ_ONLY',
                      scopes[1].GetTemplateValue('name'))

  def testAuthScope(self):
    api = self.ApiFromDiscoveryDoc(self._TEST_DISCOVERY_DOC)
    scope = AuthScope(api,
                      'https://www.googleapis.com/auth/userinfo.email',
                      {'description': 'A typical scope'})
    self.assertEquals('USERINFO_EMAIL', scope.GetTemplateValue('name'))
    self.assertEquals('userinfo.email', scope.GetTemplateValue('lastPart'))
    self.assertEquals('A typical scope', scope.GetTemplateValue('description'))
    scope = AuthScope(api,
                      'https://www.googleapis.com/auth/no.description', {})
    self.assertEquals('NO_DESCRIPTION', scope.GetTemplateValue('name'))
    self.assertEquals('https://www.googleapis.com/auth/no.description',
                      scope.GetTemplateValue('description'))
    scope = AuthScope(api, 'https://www.googleapis.com/auth/trim.slashes//', {})
    self.assertEquals('TRIM_SLASHES', scope.GetTemplateValue('name'))
    self.assertEquals('https://www.googleapis.com/auth/trim.slashes//',
                      scope.GetTemplateValue('value'))
    scope = AuthScope(api,
                      'https://www.googleapis.com/auth/product',
                      {'description': 'A product level scope'})
    self.assertEquals('PRODUCT', scope.GetTemplateValue('name'))
    scope = AuthScope(api,
                      'https://mail.google.com/',
                      {'description': 'A non-googleapis.com scope'})
    self.assertEquals('MAIL_GOOGLE_COM', scope.GetTemplateValue('name'))
    self.assertEquals('mail.google.com', scope.GetTemplateValue('lastPart'))
    self.assertEquals('https://mail.google.com/',
                      scope.GetTemplateValue('value'))
    scope = AuthScope(api,
                      'https://mail.google.com/abc',
                      {'description': 'A non-googleapis.com scope'})
    self.assertEquals('MAIL_GOOGLE_COM_ABC', scope.GetTemplateValue('name'))
    scope = AuthScope(api,
                      'http://mail.google.com/',
                      {'description': 'A non-https scope'})
    self.assertEquals('HTTP___MAIL_GOOGLE_COM', scope.GetTemplateValue('name'))
    scope = AuthScope(api, 'tag:google.com,2010:auth/groups2#email', {})
    self.assertEquals('TAG_GOOGLE_COM_2010_AUTH_GROUPS2_EMAIL',
                      scope.GetTemplateValue('name'))
    scope = AuthScope(api, 'email', {})
    self.assertEquals('EMAIL', scope.GetTemplateValue('name'))

  def testPostVariations(self):
    gen = self.ApiFromDiscoveryDoc('post_variations.json')
    # Check a normal GET method to make sure it has no request and does have
    # a response
    r1 = FindByWireName(gen.values['resources'], 'r1')
    methods = r1.values['methods']
    m = FindByWireName(methods, 'get')
    self.assertIsNone(m.values['requestType'])
    self.assertEquals('Task', m.values['responseType'].class_name)
    # A normal POST with both a request and response
    m = FindByWireName(methods, 'insert')
    self.assertEquals('Task', m.values['requestType'].class_name)
    self.assertEquals('Task', m.values['responseType'].class_name)
    # A POST with neither request nor response
    m = FindByWireName(methods, 'no_request_no_response')
    self.assertIsNone(m.values.get('requestType'))
    self.assertTrue(isinstance(m.values.get('responseType'), data_types.Void))
    # A POST with no request
    m = FindByWireName(methods, 'no_request')
    self.assertIsNone(m.values.get('requestType'))
    self.assertEquals('Task', m.values['responseType'].class_name)
    # A PUT with no response
    m = FindByWireName(methods, 'no_response')
    self.assertEquals('TaskList', m.values['requestType'].class_name)
    self.assertTrue(isinstance(m.values.get('responseType'), data_types.Void))

  def testSchemaParenting(self):
    api = self.ApiFromDiscoveryDoc(self._TEST_DISCOVERY_DOC)
    # Check that top level schemas have no parent
    for schema in ['Activity', 'Comment']:
      self.assertIsNone(api._schemas[schema].parent)
    for schema in ['Person.urls', 'Activity.object',
                   'Activity.object.attachments']:
      self.assertTrue(api._schemas[schema].parent)
    # verify the values in the name to schema map
    for name, schema in api._schemas.items():
      if schema.parent and schema.parent != api:
        wire_name = schema.values['wireName']
        parent_wire_name = schema.parent.values['wireName']
        # Our entry key should never match the wirename of our parent
        self.assertNotEquals(name, parent_wire_name)
        # our key must look like 'p1.p2....parent.me'. We verify that we at
        # least end with 'parent.me'
        self.assertTrue(name.endswith('.'.join([parent_wire_name, wire_name])))

  def testReadingRpcDiscovery(self):
    gen = self.ApiFromDiscoveryDoc(self._TEST_DISCOVERY_RPC_DOC)
    # no resources in RPC
    self.assertEquals(0, len(gen.values['resources']))
    # but we do expect a few methods
    self.assertLess(5, len(gen.values['methods']))
    self.assertGreater(100, len(gen.values['methods']))
    # RPC methods all have an id, httpMethod should be POST and have no path
    for method in gen.values['methods']:
      self.assertIsNotNone(method.values['id'])
      self.assertEquals('POST', method.values['httpMethod'])
      self.assertIsNone(method.values['restPath'])


  def testNormalizeUrlComponents(self):

    googleapis_base = 'https://www.googleapis.com/'

    def LoadApi(discovery_dict):
      d = {'name': 'fake', 'version': 'v1'}
      d.update(discovery_dict)
      api = Api(d)
      return api

    api = LoadApi({})
    self.assertEquals(googleapis_base, api.values['rootUrl'])
    self.assertEquals('fake/v1/', api.values['servicePath'])

    custom_path = '/testing/fake/v1/'
    api = LoadApi({'basePath': custom_path})
    self.assertEquals(googleapis_base, api.values['rootUrl'])
    self.assertEquals('testing/fake/v1/', api.values['servicePath'])

    custom_url = 'https://foo.com/bar/baz/'
    api = LoadApi({'basePath': custom_url})
    self.assertEquals('https://foo.com/', api.values['rootUrl'])
    self.assertEquals('bar/baz/', api.values['servicePath'])

    # Make sure baseUrl wins over basePath
    api = LoadApi({
        'basePath': '/will/not/be/used/',
        'baseUrl': custom_url
    })
    self.assertEquals('https://foo.com/', api.values['rootUrl'])
    self.assertEquals('bar/baz/', api.values['servicePath'])

    # Make sure rootUrl wins over all
    api = LoadApi({
        'basePath': '/will/not/be/used/',
        'baseUrl': 'https://bar.com/not/used/',
        'rootUrl': 'https://foo.com/',
        'servicePath': 'bar/baz/',
    })
    self.assertEquals('https://foo.com/', api.values['rootUrl'])
    self.assertEquals('bar/baz/', api.values['servicePath'])

    # Test Swarm APIs
    api = LoadApi({
        'baseUrl': 'https://localhost.appspot.com/_ah/api/fake/v1/',
        'basePath': '/_ah/api/fake/v1/',
        'rootUrl': 'https://localhost.appspot.com/_ah/api/',
        'servicePath': 'fake/v1/',
    })
    self.assertEquals('https://localhost.appspot.com/_ah/api/',
                      api.values['rootUrl'])
    self.assertEquals('fake/v1/', api.values['servicePath'])

    # .. in path
    self.assertRaises(ValueError, LoadApi, {'basePath': '/do/not/../go/up'})

    # no servicePath
    self.assertRaises(ValueError, LoadApi, {'rootUrl': 'https://foo.com/'})

  def testCanonicalName(self):
    d = {'name': 'fake', 'version': 'v1', 'canonicalName': 'My API'}
    api = Api(d)
    self.assertEquals('fake', api.values['name'])
    self.assertEquals('MyAPI', api._class_name)

  def testNormalizeOwnerInformation(self):

    def LoadApi(**kwargs):
      d = {'name': 'fake', 'version': 'v1'}
      d.update(kwargs)
      return Api(d)

    api = LoadApi()
    self.assertEquals('Google', api.values['ownerName'])
    self.assertEquals('google', api.values['owner'])
    self.assertEquals('google.com', api.values['ownerDomain'])

    api = LoadApi(ownerName='Google', ownerDomain='youtube.com')
    self.assertEquals('Google', api.values['ownerName'])
    self.assertEquals('google', api.values['owner'])
    self.assertEquals('youtube.com', api.values['ownerDomain'])

    api = LoadApi(ownerDomain='youtube.com')
    self.assertEquals('youtube_com', api.values['owner'])
    self.assertEquals('youtube.com', api.values['ownerDomain'])

    # owner is explicitly declared
    api = LoadApi(owner='You Tube', ownerDomain='youtube.com')
    self.assertEquals('You Tube', api.values['owner'])
    self.assertEquals('youtube.com', api.values['ownerDomain'])

    api = LoadApi(servicePath='/fake',
                  rootUrl='https://www.foobar.co.uk:8080/root')
    self.assertEquals('www.foobar.co.uk', api['ownerDomain'])
    self.assertEquals('www_foobar_co_uk', api['owner'])

    api = LoadApi(servicePath='/fake',
                  rootUrl='https://whathaveyou.google.com')
    self.assertEquals('google.com', api['ownerDomain'])
    self.assertEquals('Google', api['ownerName'])
    self.assertEquals('google', api['owner'])

    api = LoadApi(servicePath='/fake',
                  rootUrl='https://whathaveyou.googleapis.com')
    self.assertEquals('google.com', api['ownerDomain'])
    self.assertEquals('Google', api['ownerName'])
    self.assertEquals('google', api['owner'])

    api = LoadApi(servicePath='/fake',
                  rootUrl='https://whathaveyou.google.com')
    self.assertEquals('google.com', api['ownerDomain'])
    self.assertEquals('Google', api['ownerName'])
    self.assertEquals('google', api['owner'])

  def testSharedTypes(self):
    api = self.ApiFromDiscoveryDoc(self._TEST_SHARED_TYPES_DOC)
    api.VisitAll(lambda o: o.SetLanguageModel(language_model.LanguageModel()))
    # class defined by the API
    photos_feed_schema = api._schemas['PhotosFeed']
    # type defined from a shared type repo
    photo_schema = api._schemas[
        'http://www.googleapis.com/types/v1/com.google/plus/v2/photo']
    self.assertEquals('PhotosFeed', photos_feed_schema.values['wireName'])
    self.assertEquals('com.google.myservice', photos_feed_schema.module.name)
    self.assertEquals('Photo', photo_schema.values['wireName'])
    self.assertEquals('com.google.plus.pictures', photo_schema.module.name)
    self.assertEquals('com/google/plus/pictures', photo_schema.module.path)

  def testMethods(self):
    api = self.ApiFromDiscoveryDoc(self._TEST_DISCOVERY_DOC)
    self.assertEquals(api, api.top_level_methods[0].parent)
    self.assertLess(25, len(api.all_methods))
    self.assertLess(0, len(api.top_level_methods))

  def testApiHasTitle(self):
    api_def = {'name': 'fake',
               'version': 'v1',
               'schemas': {},
               'resources': {}}
    api = Api(api_def)
    self.assertEquals('fake', api['title'])

  def testExponentialBackoffDefault(self):
    # Make sure exponentialBackoffDefault defaults to False.
    discovery_doc = json.loads(
        """
        {
         "name": "fake",
         "version": "v1",
         "schemas": {},
         "resources": {}
        }
        """)
    api = Api(discovery_doc)
    # Make sure exponentialBackoffDefault gets set to True.
    self.assertFalse(api.values['exponentialBackoffDefault'])
    discovery_doc2 = json.loads(
        """
        {
         "name": "fake",
         "version": "v1",
         "schemas": {},
         "resources": {},
         "exponentialBackoffDefault": true
        }
        """)
    api2 = Api(discovery_doc2)
    self.assertTrue(api2.values['exponentialBackoffDefault'])


class ApiModulesTest(basetest.TestCase):

  def setUp(self):
    self.discovery_doc = json.loads(
        """
        {
         "name": "fake",
         "version": "v1",
         "schemas": {},
         "resources": {}
        }
        """)
    self.language_model = FakeLanguageModel()

  def testModuleOwnerDomain(self):
    self.discovery_doc['ownerDomain'] = 'foo.bar'
    api = Api(self.discovery_doc)
    api.VisitAll(lambda o: o.SetLanguageModel(self.language_model))
    self.assertEquals('bar/foo/fake', api.values['module'].path)

  def testModulePackagePath(self):
    self.discovery_doc['packagePath'] = 'foo/BAR'
    api = Api(self.discovery_doc)
    api.VisitAll(lambda o: o.SetLanguageModel(self.language_model))
    self.assertEquals('com/google/foo/BAR/fake', api.values['module'].path)

  def testModuleOwnerDomainAndPackagePath(self):
    self.discovery_doc['ownerDomain'] = 'toasty.com'
    self.discovery_doc['packagePath'] = 'foo/BAR'
    api = Api(self.discovery_doc)
    api.VisitAll(lambda o: o.SetLanguageModel(self.language_model))
    self.assertEquals('com/toasty/foo/BAR/fake', api.values['module'].path)


def FindByWireName(list_of_resource_or_method, wire_name):
  """Find an element in a list by its "wireName".

  The "wireName" is the name of the method "on the wire", which is the raw name
  as it appears in the JSON.

  Args:
    list_of_resource_or_method: A list of resource or methods as annotated by
      the Api.
    wire_name: (str): the name to fine.

  Returns:
    dict or None
  """
  for x in list_of_resource_or_method:
    if x.values['wireName'] == wire_name:
      return x
  return None


if __name__ == '__main__':
  basetest.main()
