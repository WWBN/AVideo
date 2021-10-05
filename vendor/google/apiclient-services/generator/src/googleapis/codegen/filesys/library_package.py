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

"""Base class of library packagers.

This is the abstract base class for modules that can emit a package of files.
The two intended implementations are Zip files and direct to the file system.
"""

__author__ = 'aiuto@google.com (Tony Aiuto)'

import contextlib
import os

from googleapis.codegen.filesys import files


class LibraryPackage(object):
  """The library package."""

  def __init__(self):
    """Create a new LibraryPackage."""
    self._file_path_prefix = ''

  def StartFile(self, name):
    """Start writing a named file to the package.

    Subclasses must implement this.

    Args:
      name: (str) path which will identify the contents in the archive.

    Returns:
      A file-like object to write the contents to.
    """
    raise NotImplementedError(
        'Subclasses of LibraryPackage must implement StartFile')

  def CreateDirectory(self, directory):
    """Create one or more directory entries, esssentially mkdir -p.

    Subclasses may implement this. If they do not, it should be handed as a
    side effect of StartFile.

    Args:
      directory: (str) path of a directory in the archive
    """
    pass

  def EndFile(self):
    """Flush the current output file to the package container.

    Subclasses must implement this.
    """
    pass

  def DoneWritingArchive(self):
    """Signal that we are done writing the entire package.

    Subclasses may implement this if required.

    This method must be called to flush remaining data to the output stream.
    """
    pass

  def IncludeFile(self, path, name):
    """Read a file from disk into the archive.

    Args:
      path: (str) path to the file.
      name: (str) name the file should have in the archive.
    """
    output_stream = self.StartFile(name)
    output_stream.write(files.GetFileContents(path))
    self.EndFile()

  def IncludeManyFiles(self, paths, strip_prefix='', new_prefix=None):
    """Include a list of many files.

    Args:
      paths: (list) list of paths to real files.
      strip_prefix: The common prefix to strip from each file.
      new_prefix: The replacement for the stripped prefix.

    Raises:
      ValueError: if any of the paths to not begin with strip_prefix.
    """
    for path in paths:
      base = path[:len(strip_prefix)]
      name = path[len(strip_prefix):]
      if strip_prefix != base:
        raise ValueError('path: %s did not begin with %s' % (
            path, strip_prefix))
      if new_prefix:
        name = os.path.join(new_prefix, name)
      self.IncludeFile(path, name)

  def SetFilePathPrefix(self, path):
    """Set a prefix to be prepended to any file names."""
    if path and not path.endswith('/'):
      path = '%s/' % path
    self._file_path_prefix = path

  @contextlib.contextmanager
  def FilePathPrefix(self, path):
    """A context manager that sets and restores a file path prefix.

    Example usage:

       with myPackageWriter.FilePathPrefix(path):
         myPackageWriter.WriteDataAsFile(content, 'foo')
    Args:
      path: (str) A top level path.
    Yields:
      (nothing)
    """
    old_prefix = self._file_path_prefix
    self.SetFilePathPrefix(path)
    try:
      yield
    finally:
      self._file_path_prefix = old_prefix

  def WriteDataAsFile(self, content, file_name):
    """Write a blob of content to the package as the given file name.

    Args:
      content: (str) The blob of content to write
      file_name: (str) The file name.
    """
    output_stream = self.StartFile(file_name)
    output_stream.write(content)
    self.EndFile()

  def FileExtension(self):
    """Return the file extension for this type of library, without the dot.

    Subclasses should implement this if they produce an archive file.
    """
    return None

  def MimeType(self):
    """Return the MIME type for this type of library.

    Subclasses should implement this if they produce an archive file.
    """
    return None
