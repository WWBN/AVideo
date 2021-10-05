#!/usr/bin/python2.7
# Copyright 2017 Google Inc. All Rights Reserved.
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

"""A LibraryPackage that creates a single output file with delimiters.

Merge all the individual output files into a single stream with delimiters of
the form |=== begin: <path>| and |=== end: <path>|, where the path names are
sorted by name. This is designed to make diffing output against a golden copy
easy, but is is wildly inefficent, as we store all the data before writing any.
"""

from __future__ import absolute_import
from __future__ import division
from __future__ import print_function

import StringIO

from googleapis.codegen.filesys.library_package import LibraryPackage


class SingleFileLibraryPackage(LibraryPackage):
  """The library package."""

  def __init__(self, stream):
    """Create a new SingleFileLibraryPackage.

    Args:
      stream: (file) A file-like object to write to.
    """
    super(SingleFileLibraryPackage, self).__init__()
    self._files = {}
    self._current_file_data = None
    self._final_output_stream = stream

  def StartFile(self, name):
    """Start writing a named file to the package.

    Args:
      name: (str) path which will identify the contents in the archive.

    Returns:
      A file-like object to write the contents to.
    """
    self.EndFile()
    self._current_file_data = StringIO.StringIO()
    self._current_file_name = '%s%s' % (self._file_path_prefix, name)
    return self._current_file_data

  def EndFile(self):
    """Flush the current output file."""
    if self._current_file_data:
      data = self._current_file_data.getvalue()
      self._current_file_data.close()
      self._current_file_data = None
      # File contents may be utf-8
      if not isinstance(data, bytes):
        data = data.encode('utf-8')
      # Replace CRLF with LF because in the C# .xml files, some have CRLF but
      # others do not. This causes confusion because depending on how you do
      # a diff on golden output, you get either a change or not.
      self._files[self._current_file_name] = data.replace('\r\n', '\n')

  def DoneWritingArchive(self):
    """Signal that we are done writing the entire package.

    Emit the files.
    """
    self.EndFile()
    for file_name in sorted(self._files):
      print('=== begin: %s' % file_name, file=self._final_output_stream)
      self._final_output_stream.write(self._files[file_name])
      print('=== end: %s' % file_name, file=self._final_output_stream)
    self._final_output_stream.flush()

  def FileExtension(self):
    """Returns the file extension for this archive."""
    return 'txt'

  def MimeType(self):
    """Returns the MIME type for this archive."""
    return 'text/plain'
