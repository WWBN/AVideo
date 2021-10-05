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

"""Tests for tar_library_package."""

__author__ = 'sammccall@google.com (Sam McCall)'

from io import BytesIO
import os
import tarfile

import gflags as flags
from google.apputils import basetest
from googleapis.codegen.filesys import tar_library_package

FLAGS = flags.FLAGS


class TarLibraryPackageTest(basetest.TestCase):
  _FILE_NAME = 'a_test'
  _DISALLOWED_FILE_NAME = 'unicode_☃☄'
  _FILE_CONTENTS = u'this is a test - ☃☄'
  _TEST_DATA_DIR = os.path.join(os.path.dirname(__file__), 'testdata')

  def setUp(self):
    self._output_stream = BytesIO()
    self._package = tar_library_package.TarLibraryPackage(
        self._output_stream)

  def tearDown(self):
    pass

  def testAsciiFilenames(self):
    self.assertRaises(UnicodeError, self._package.StartFile,
                      self._DISALLOWED_FILE_NAME)

  def testBasicWriteFile(self):
    stream = self._package.StartFile(self._FILE_NAME)
    stream.write(self._FILE_CONTENTS)
    self._package.EndFile()
    self._package.DoneWritingArchive()

    # read it back and verify
    archive = tarfile.open(fileobj=BytesIO(self._output_stream.getvalue()),
                           mode='r:gz')
    info_list = archive.getmembers()
    self.assertEquals(1, len(info_list))
    self.assertEquals(self._FILE_NAME, info_list[0].name)
    self.assertEquals(len(self._FILE_CONTENTS.encode('utf-8')),
                      info_list[0].size)

  def testBasicWriteFileUncompressed(self):
    output_stream = BytesIO()
    package = tar_library_package.TarLibraryPackage(
        output_stream, compress=False)
    stream = package.StartFile(self._FILE_NAME)
    stream.write(self._FILE_CONTENTS)
    package.EndFile()
    package.DoneWritingArchive()

    # read it back and verify
    archive = tarfile.open(fileobj=BytesIO(output_stream.getvalue()), mode='r')
    info_list = archive.getmembers()
    self.assertEquals(1, len(info_list))
    self.assertEquals(self._FILE_NAME, info_list[0].name)
    self.assertEquals(len(self._FILE_CONTENTS.encode('utf-8')),
                      info_list[0].size)

  def testStartAutomaticallyClosesPreviousFile(self):
    stream = self._package.StartFile(self._FILE_NAME)
    stream.write(self._FILE_CONTENTS)
    file_name_2 = '%s_2' % self._FILE_NAME
    stream = self._package.StartFile(file_name_2)
    stream.write(self._FILE_CONTENTS)
    self._package.EndFile()
    self._package.DoneWritingArchive()
    # read it back and verify
    archive = tarfile.open(fileobj=BytesIO(self._output_stream.getvalue()),
                           mode='r:gz')
    info_list = archive.getmembers()
    self.assertEquals(2, len(info_list))
    self.assertEquals(self._FILE_NAME, info_list[0].name)
    self.assertEquals(file_name_2, info_list[1].name)

  def testDoneAutomaticallyEndsFile(self):
    stream = self._package.StartFile(self._FILE_NAME)
    stream.write(self._FILE_CONTENTS)
    self._package.DoneWritingArchive()

    # read it back and verify
    archive = tarfile.open(fileobj=BytesIO(self._output_stream.getvalue()),
                           mode='r:gz')
    info_list = archive.getmembers()
    self.assertEquals(1, len(info_list))
    self.assertEquals(self._FILE_NAME, info_list[0].name)

  def testIncludeFile(self):
    made_up_dir = 'new_directory/'
    made_up_path = '%sfile1.txt' % made_up_dir
    # testdata/file1.txt is 125 bytes long.
    expected_size = 125
    self._package.IncludeFile(os.path.join(self._TEST_DATA_DIR, 'file1.txt'),
                              made_up_path)
    self._package.DoneWritingArchive()

    # read it back and verify
    archive = tarfile.open(fileobj=BytesIO(self._output_stream.getvalue()),
                           mode='r:gz')
    info_list = archive.getmembers()
    self.assertEquals(1, len(info_list))  # no explicit folders
    self.assertEquals(made_up_path, info_list[0].name)
    self.assertEquals(expected_size, info_list[0].size)

  def testManyFiles(self):
    top_of_tree = os.path.join(self._TEST_DATA_DIR, 'tree/')
    total_files_in_testdata_tree = 3  # determined by hand
    paths = []
    for root, unused_dirs, file_names in os.walk(top_of_tree):
      for file_name in file_names:
        paths.append(os.path.join(root, file_name))
    self._package.IncludeManyFiles(paths, top_of_tree)
    self._package.DoneWritingArchive()

    # check it
    archive = tarfile.open(fileobj=BytesIO(self._output_stream.getvalue()),
                           mode='r:gz')
    info_list = archive.getmembers()
    self.assertEquals(total_files_in_testdata_tree, len(info_list))

  def testManyFilesError(self):
    files = [os.path.join(self._TEST_DATA_DIR, file_name)
             for file_name in ['tree/abc', 'tree/def', 'file1.txt']]
    self.assertRaises(ValueError,
                      self._package.IncludeManyFiles,
                      files,
                      os.path.join(self._TEST_DATA_DIR, 'tree/'))

  def testFileProperties(self):
    self.assertEquals('tgz', self._package.FileExtension())
    self.assertEquals('application/x-gtar-compressed', self._package.MimeType())
    uncompressed = tar_library_package.TarLibraryPackage(
        BytesIO(), compress=False)
    self.assertEquals('tar', uncompressed.FileExtension())
    self.assertEquals('application/x-gtar', uncompressed.MimeType())


if __name__ == '__main__':
  basetest.main()
