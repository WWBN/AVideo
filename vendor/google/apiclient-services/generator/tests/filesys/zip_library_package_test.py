#!/usr/bin/python2.7
# -*- coding: utf-8 -*-
#
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

"""Tests for zip_library_package."""

__author__ = 'aiuto@google.com (Tony Aiuto)'

import io
import os
import zipfile

import gflags as flags
from google.apputils import basetest
from googleapis.codegen.filesys import zip_library_package

FLAGS = flags.FLAGS


class ZipLibraryPackageTest(basetest.TestCase):
  _FILE_NAME = 'a_test'
  _DISALLOWED_FILE_NAME = 'unicode_☃☄'
  _FILE_CONTENTS = u'this is a test - ☃☄'
  _TEST_DATA_DIR = os.path.join(os.path.dirname(__file__), 'testdata')

  def setUp(self):
    self._output_stream = io.BytesIO()
    self._package = zip_library_package.ZipLibraryPackage(self._output_stream)

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
    archive = zipfile.ZipFile(io.BytesIO(self._output_stream.getvalue()), 'r')
    info_list = archive.infolist()
    self.assertEquals(1, len(info_list))
    self.assertEquals(self._FILE_NAME, info_list[0].filename)
    self.assertEquals(len(self._FILE_CONTENTS.encode('utf-8')),
                      info_list[0].file_size)

  def testStartAutomaticallyClosesPreviousFile(self):
    stream = self._package.StartFile(self._FILE_NAME)
    stream.write(self._FILE_CONTENTS)
    file_name_2 = '%s_2' % self._FILE_NAME
    stream = self._package.StartFile(file_name_2)
    stream.write(self._FILE_CONTENTS)
    self._package.EndFile()
    self._package.DoneWritingArchive()
    # read it back and verify
    archive = zipfile.ZipFile(io.BytesIO(self._output_stream.getvalue()), 'r')
    info_list = archive.infolist()
    self.assertEquals(2, len(info_list))
    self.assertEquals(self._FILE_NAME, info_list[0].filename)
    self.assertEquals(file_name_2, info_list[1].filename)

  def testDoneAutomaticallyEndsFile(self):
    stream = self._package.StartFile(self._FILE_NAME)
    stream.write(self._FILE_CONTENTS)
    self._package.DoneWritingArchive()

    # read it back and verify
    archive = zipfile.ZipFile(io.BytesIO(self._output_stream.getvalue()), 'r')
    info_list = archive.infolist()
    self.assertEquals(1, len(info_list))
    self.assertEquals(self._FILE_NAME, info_list[0].filename)

  def testIncludeFile(self):
    made_up_dir = 'new_directory/'
    made_up_path = '%sfile1.txt' % made_up_dir
    # testdata/file1.txt is 125 bytes long.
    expected_size = 125
    self._package.IncludeFile(os.path.join(self._TEST_DATA_DIR, 'file1.txt'),
                              made_up_path)
    self._package.DoneWritingArchive()

    # read it back and verify
    archive = zipfile.ZipFile(io.BytesIO(self._output_stream.getvalue()), 'r')
    info_list = archive.infolist()
    self.assertEquals(2, len(info_list))  # folder + file
    self.assertEquals(made_up_dir, info_list[0].filename)
    self.assertEquals(0, info_list[0].file_size)
    self.assertEquals(made_up_path, info_list[1].filename)
    self.assertEquals(expected_size, info_list[1].file_size)

  def testManyFiles(self):
    top_of_tree = os.path.join(self._TEST_DATA_DIR, 'tree/')
    total_files_in_testdata_tree = 3  # determined by hand
    total_folders_in_testdata_tree = 1  # determined by hand
    paths = []
    for root, unused_dirs, file_names in os.walk(top_of_tree):
      for file_name in file_names:
        paths.append(os.path.join(root, file_name))
    self._package.IncludeManyFiles(paths, top_of_tree)
    self._package.DoneWritingArchive()

    # check it
    archive = zipfile.ZipFile(io.BytesIO(self._output_stream.getvalue()), 'r')
    info_list = archive.infolist()
    self.assertEquals(
        total_files_in_testdata_tree + total_folders_in_testdata_tree,
        len(info_list))

  def testManyFilesError(self):
    files = [os.path.join(self._TEST_DATA_DIR, file_name)
             for file_name in ['tree/abc', 'tree/def', 'file1.txt']]
    self.assertRaises(ValueError,
                      self._package.IncludeManyFiles,
                      files,
                      os.path.join(self._TEST_DATA_DIR, 'tree/'))

  def testOutputPrefix(self):
    prefix = 'abc/def'
    self._package.SetFilePathPrefix(prefix)
    stream = self._package.StartFile(self._FILE_NAME)
    stream.write(self._FILE_CONTENTS)
    self._package.EndFile()
    self._package.DoneWritingArchive()

    # read it back and verify
    archive = zipfile.ZipFile(io.BytesIO(self._output_stream.getvalue()), 'r')
    info_list = archive.infolist()
    self.assertEquals(3, len(info_list))  # 2 folders + file
    expected_name = '%s/%s' % (prefix, self._FILE_NAME)
    self.assertEquals('abc/', info_list[0].filename)
    self.assertEquals('abc/def/', info_list[1].filename)
    self.assertEquals(expected_name, info_list[2].filename)

  def testDoNotOvercreateDirectories(self):
    """Make sure we do not create more directories than we need."""

    for file_name in ['d1/d2/f1', 'd1/d2/f2', 'd1/d3/f1', 'd4/d5/f1', 'd4/f1',
                      'd1/f1', 'd1/d2/f3']:
      stream = self._package.StartFile(file_name)
      stream.write(self._FILE_CONTENTS)
    self._package.EndFile()
    self._package.DoneWritingArchive()

    # read it back and verify
    archive = zipfile.ZipFile(io.BytesIO(self._output_stream.getvalue()), 'r')
    info_list = archive.infolist()
    dir_bits = (040755 << 16) | 0x10

    index = 0
    self.assertEquals('d1/', info_list[index].filename)
    self.assertEquals(dir_bits, info_list[index].external_attr)

    index += 1
    self.assertEquals('d1/d2/', info_list[index].filename)
    self.assertEquals(dir_bits, info_list[index].external_attr)

    index += 1
    self.assertEquals('d1/d2/f1', info_list[index].filename)
    index += 1
    self.assertEquals('d1/d2/f2', info_list[index].filename)

    index += 1
    self.assertEquals('d1/d3/', info_list[index].filename)
    self.assertEquals(dir_bits, info_list[index].external_attr)

    index += 1
    self.assertEquals('d1/d3/f1', info_list[index].filename)

    index += 1
    self.assertEquals('d4/', info_list[index].filename)
    self.assertEquals(dir_bits, info_list[index].external_attr)

    index += 1
    self.assertEquals('d4/d5/', info_list[index].filename)
    self.assertEquals(dir_bits, info_list[index].external_attr)

    index += 1
    self.assertEquals('d4/d5/f1', info_list[index].filename)

    index += 1
    self.assertEquals('d4/f1', info_list[index].filename)

    index += 1
    self.assertEquals('d1/f1', info_list[index].filename)

    index += 1
    self.assertEquals('d1/d2/f3', info_list[index].filename)

    index += 1
    self.assertEquals(index, len(info_list))

  def testFileProperties(self):
    self.assertEquals('zip', self._package.FileExtension())
    self.assertEquals('application/zip', self._package.MimeType())


if __name__ == '__main__':
  basetest.main()
