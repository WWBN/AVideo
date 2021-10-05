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

"""Tests for generator.py."""

__author__ = 'aiuto@google.com (Tony Aiuto)'

import io
import logging
import os
import zipfile


from google.apputils import basetest

from googleapis.codegen import generator
from googleapis.codegen.filesys import zip_library_package


class GeneratorTest(basetest.TestCase):

  _TEST_DATA_DIR = os.path.abspath(
    os.path.join(os.path.dirname(__file__), 'testdata')
  )

  def setUp(self):
    self._output_stream = io.BytesIO()
    self._package = zip_library_package.ZipLibraryPackage(self._output_stream)
    self._path_replacements = {
        '___package_path___': 'pp'
        }

  def VerifyPackageContains(self, file_names, must_not_contain=None):
    """Verify that the output package contains some files.

    Args:
      file_names: List of file names.
      must_not_contain: List of file names which must not be in the package.
    """
    expect_to_see = list(file_names)
    archive = zipfile.ZipFile(io.BytesIO(self._output_stream.getvalue()), 'r')
    info_list = archive.infolist()
    self.assertLess(0, len(info_list))
    for i in info_list:
      path = i.filename
      # Show what we got to help make the test log more useful when we fail.
      logging.info('zip contains: %s', path)
      if path in expect_to_see:
        expect_to_see.remove(path)
      else:
        logging.info('unexpected file: %s' % path)
      if path in (must_not_contain or []):
        self.fail('Found unexpected file %s in archive' % path)
    # We should have seen everything we expect
    self.assertEquals(0, len(expect_to_see))

  def testWalkTemplateTree(self):
    gen = generator.TemplateGenerator()
    gen.SetTemplateDir(os.path.join(self._TEST_DATA_DIR, 'library'))
    gen.WalkTemplateTree(
        'templates', self._path_replacements, {}, {}, self._package)
    self._package.DoneWritingArchive()

    # Now read it back and verify
    self.VerifyPackageContains(['foo', 'bar', 'app.yaml', 'pp/xxx'])

  def testWalkTemplateTreeWithFilter(self):
    gen = generator.TemplateGenerator()
    gen.SetTemplateDir(os.path.join(self._TEST_DATA_DIR, 'library'))
    gen.WalkTemplateTree(
        'templates', self._path_replacements, {}, {}, self._package,
        file_filter=lambda template, output: output != 'bar')
    self._package.DoneWritingArchive()
    self.VerifyPackageContains(['foo'], must_not_contain=['bar'])

  def testWalkTemplateTreeWithFilteredResult(self):
    gen = generator.TemplateGenerator()
    gen.SetTemplateDir(os.path.join(self._TEST_DATA_DIR, 'library'))
    gen.WalkTemplateTree(
        'templates', self._path_replacements, {}, {}, self._package,
        file_filter=lambda template, output: output != 'pp/xxx')
    self._package.DoneWritingArchive()
    self.VerifyPackageContains(['foo', 'bar'], must_not_contain=['pp/xxx'])

  def testWalkTemplateTreeWithFilteredTemplate(self):
    gen = generator.TemplateGenerator()
    gen.SetTemplateDir(os.path.join(self._TEST_DATA_DIR, 'library'))
    gen.WalkTemplateTree(
        'templates', self._path_replacements, {}, {}, self._package,
        file_filter=lambda template, output: template != 'bar.tmpl')
    self._package.DoneWritingArchive()
    self.VerifyPackageContains(['foo'], must_not_contain=['bar'])



if __name__ == '__main__':
  basetest.main()
