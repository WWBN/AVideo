#!/usr/bin/python2.7
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

"""A LibraryPackage that creates a .tar.gz file.

This module aids in the construction of a .tar.gz file containing all the
components generated and required by a library.
"""

__author__ = 'sammccall@google.com (Sam McCall)'

from io import BytesIO
import StringIO
import tarfile
import time

from googleapis.codegen.filesys.library_package import LibraryPackage


class TarLibraryPackage(LibraryPackage):
  """The library package."""

  def __init__(self, stream, compress=True):
    """Create a new TarLibraryPackage.

    Args:
      stream: (file) A file-like object to write to.
      compress: (boolean) Whether to gzip-compress the output.
    """
    super(TarLibraryPackage, self).__init__()
    mode = 'w:gz' if compress else 'w'
    self._tar = tarfile.open(fileobj=stream, mode=mode)
    self._current_file_data = None
    self._compress = compress

  def StartFile(self, name):
    """Start writing a named file to the package.

    Args:
      name: (str) path which will identify the contents in the archive.

    Returns:
      A file-like object to write the contents to.
    """
    self.EndFile()
    self._current_file_data = StringIO.StringIO()
    name = '%s%s' % (self._file_path_prefix, name)
    # Let this explode if the name is not ascii.
    self._current_file_name = name.encode('ascii')
    return self._current_file_data

  def EndFile(self):
    """Flush the current output file to the tar container."""
    if self._current_file_data:
      info = tarfile.TarInfo(self._current_file_name)
      info.mtime = time.time()
      info.mode = 0644
      data = self._current_file_data.getvalue()
      if isinstance(data, unicode):
        data = data.encode('utf-8')
      info.size = len(data)
      self._tar.addfile(info, BytesIO(data))
      self._current_file_data.close()
      self._current_file_data = None

  def DoneWritingArchive(self):
    """Signal that we are done writing the entire package.

    This method must be called to flush the tar file directory to the output
    stream.
    """
    if self._tar:
      self.EndFile()
      self._tar.close()
      self._tar = None

  def FileExtension(self):
    """Returns the file extension for this archive, either tar or tgz."""
    if self._compress:
      return 'tgz'
    else:
      return 'tar'

  def MimeType(self):
    """Returns the MIME type for this archive."""
    if self._compress:
      return 'application/x-gtar-compressed'
    return 'application/x-gtar'
