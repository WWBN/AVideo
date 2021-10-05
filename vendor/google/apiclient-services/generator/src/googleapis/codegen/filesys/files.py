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


import errno
import os



class FileDoesNotExist(Exception):
  """File does not exist."""


def GetFileContents(filename):
  """Returns the contents of a file.


  Args:
    filename: path to a file.
  Returns:
    a string.
  Raises:
    FileDoesNotExist: if the file does not exist
    IOError: for other local IO errors
  """
  try:
    return open(filename).read()
  except IOError as e:
    if e.errno == errno.ENOENT:
      raise FileDoesNotExist(filename)
    raise


def IsFile(filename):
  """Returns whether the named file is a regular file.

  Args:
    filename: path to a file.
  Returns:
    bool: whether the file is a regular file.
  """
  return os.path.isfile(filename)


def IterFiles(directory):
  """yield all files beneath a directory."""
  for root, unused_dirs, filenames in os.walk(directory):
    for f in filenames:
      yield os.path.join(root, f)


