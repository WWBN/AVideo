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

"""A LibraryPackage that creates a Zip file.

This module aids in the construction of a ZIP file containing all the
components generated and required by a library.
"""

__author__ = 'aiuto@google.com (Tony Aiuto)'

import os
import StringIO
import zipfile

from googleapis.codegen.filesys.library_package import LibraryPackage


class ZipLibraryPackage(LibraryPackage):
  """The library package."""

  def __init__(self, stream):
    """Create a new ZipLibraryPackage.

    Args:
      stream: (file) A file-like object to write to.
    """
    super(ZipLibraryPackage, self).__init__()
    self._zip = zipfile.ZipFile(stream, 'w', zipfile.ZIP_STORED)
    self._current_file_data = None
    self._created_dirs = []  # list of directory entries we have auto-created

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
    self.CreateDirectory(os.path.dirname(self._current_file_name))
    return self._current_file_data

  def CreateDirectory(self, directory):
    """Create one or more directory entries, esssentially mkdir -p.

    Some zip readers do not create needed folders for arbitrary file paths.
    When we encounter a new path for the first time, we make sure the tree
    exists.

    Args:
      directory: (str) a directory name.
    """
    # Build list of dirs to build. This will be from bottom up.
    to_create = []
    while directory:
      if directory in self._created_dirs:
        break
      to_create.append(directory)
      self._created_dirs.append(directory)
      directory = os.path.dirname(directory)
    to_create.reverse()  # have to build from top down
    for directory in to_create:
      # Create the directory entry.  File and directory names must be
      # ascii.
      info = zipfile.ZipInfo((directory + '/').encode('ascii'),
                             date_time=self.ZipTimestamp())
      # Notes from the web and zipfile sources:
      # external_attr is 32 in size, with the unix permissions in the
      # high order 16 bit, and the MS-DOS FAT attributes in the lower 16.
      # man 2 stat tells us that 040755 should be a drwxr-xr-x style file,
      # and word of mouth tells me that bit 4 marks directories in FAT.
      info.external_attr = (040755 << 16) | 0x10
      self._zip.writestr(info, '')

  def EndFile(self):
    """Flush the current output file to the ZIP container."""
    if self._current_file_data:
      info = zipfile.ZipInfo(self._current_file_name,
                             date_time=self.ZipTimestamp())
      # This is a chmod 0644, but you have to read the zipfile sources to know
      info.external_attr = 0644 << 16
      data = self._current_file_data.getvalue()
      # File contents may be utf-8
      if isinstance(data, unicode):
        data = data.encode('utf-8')
      self._zip.writestr(info, data)
      self._current_file_data.close()
      self._current_file_data = None

  def ZipTimestamp(self):
    # Use a constant timestamp to avoid non-deterministic build-time output.
    return (1980, 1, 1, 0, 0, 1)

  def DoneWritingArchive(self):
    """Signal that we are done writing the entire package.

    This method must be called to flush the zip file directory to the output
    stream.
    """
    if self._zip:
      self.EndFile()
      self._zip.close()
      self._zip = None

  def FileExtension(self):
    """Returns the file extension for this archive, which is zip."""
    return 'zip'

  def MimeType(self):
    """Returns the MIME type for this archive."""
    return 'application/zip'
