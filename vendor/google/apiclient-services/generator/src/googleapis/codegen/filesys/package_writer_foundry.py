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
"""Foundary for getting a package writer."""

from googleapis.codegen.filesys import filesystem_library_package
from googleapis.codegen.filesys import single_file_library_package
from googleapis.codegen.filesys import tar_library_package
from googleapis.codegen.filesys import zip_library_package


def GetPackageWriter(output_dir=None, output_file=None, output_format='zip'):
  """Get an output writer for a package."""

  if not (output_dir or output_file):
    raise ValueError(
        'GetPackageWriter requires either output_dir or output_file')
  if output_dir and output_file:
    raise ValueError(
        'GetPackageWriter requires only one of output_dir or output_file')

  if output_dir:
    package_writer = filesystem_library_package.FilesystemLibraryPackage(
        output_dir)
  else:
    out = open(output_file, 'w')
    if output_format == 'tgz':
      package_writer = tar_library_package.TarLibraryPackage(out)
    elif output_format == 'tar':
      package_writer = tar_library_package.TarLibraryPackage(out,
                                                             compress=False)
    elif output_format == 'txt':
      package_writer = single_file_library_package.SingleFileLibraryPackage(out)
    else:
      package_writer = zip_library_package.ZipLibraryPackage(out)
  return package_writer
