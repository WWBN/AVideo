#!/usr/bin/python2.7
# -*- coding: utf-8 -*-
#
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

"""Tests for single_file_library_package."""

from __future__ import absolute_import
from __future__ import division
from __future__ import print_function

from io import BytesIO

import gflags as flags
from google.apputils import basetest
from googleapis.codegen.filesys import single_file_library_package

FLAGS = flags.FLAGS


class SingleFileLibraryPackageTest(basetest.TestCase):

  def setUp(self):
    self._output_stream = BytesIO()
    self._package = single_file_library_package.SingleFileLibraryPackage(
        self._output_stream)

  def tearDown(self):
    pass

  def testBasicWriteFile(self):
    name1 = 'def'
    content1 = 'contents of def'
    name2 = 'abc'
    content2 = 'contents of abc'
    expected_tmpl = '=== begin: %s\n%s=== end: %s\n'

    stream = self._package.StartFile(name1)
    stream.write(content1)
    stream = self._package.StartFile(name2)
    stream.write(content2)
    self._package.DoneWritingArchive()

    # read it back and verify
    expected = expected_tmpl % (name2, content2, name2)
    expected += expected_tmpl % (name1, content1, name1)
    got = self._output_stream.getvalue()
    self.assertEqual(expected, got)


if __name__ == '__main__':
  basetest.main()
