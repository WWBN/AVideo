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

"""A LibraryPackage that writes to the file system.

This module implements the LibraryPackage interface, but writes directly to the
file system.
"""

__author__ = 'aiuto@google.com (Tony Aiuto)'

import os

from googleapis.codegen.filesys.library_package import LibraryPackage


class FilesystemLibraryPackage(LibraryPackage):
  """The library package."""

  def __init__(self, root_path):
    """Create a new FilesystemLibraryPackage.

    Args:
      root_path: (str) A path to a directory where the files will be written.
        The directory will be created if it does not exist.
    Raises:
      ValueError: If the directory exists, but is not writable.
      OSError: If the directory does not exist and cannot be created.
    """
    super(FilesystemLibraryPackage, self).__init__()
    # Create the directory if we have to
    self._MakePath(root_path)
    self._root_path = root_path
    self._current_file_stream = None

  def StartFile(self, name):
    """Start writing a named file to the package.

    Args:
      name: (str) path which will identify the contents in the archive.

    Returns:
      A file-like object to write the contents to.
    """
    self.EndFile()
    full_path = os.path.join(self._root_path, self._file_path_prefix, name)
    self._MakePath(os.path.dirname(full_path))
    self._current_file_stream = open(full_path, 'w')
    return self._current_file_stream

  def EndFile(self):
    """Flush the current output file."""
    if self._current_file_stream:
      self._current_file_stream.close()
      self._current_file_stream = None

  def _MakePath(self, path):
    """Create a directory path if needed.

    Args:
      path: (str) A path to a directory where files will be written.  The
        directory will be created if it does not exist.
    Raises:
      ValueError: If the directory exists, but is not writable.
      OSError: If the directory does not exist and cannot be created.
    """
    if not os.access(path, os.W_OK):
      if os.access(path, os.F_OK):
        raise ValueError('%s exists, but is not writable' % path)
      os.makedirs(path, 0755)
