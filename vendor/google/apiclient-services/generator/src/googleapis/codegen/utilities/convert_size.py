#!/usr/bin/python2.7
# Copyright 2011 Google Inc. All Rights Reserved.
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

"""Convert human readable sizes to numbers.

Convert a string like 10G/K/M/B to a number.
"""


def ConvertSize(size):
  if not size:
    return None
  units = [('GB', 2 ** 30),
           ('MB', 2 ** 20),
           ('KB', 2 ** 10),
           ('B', 1)]
  size = size.upper()
  for suffix, multiplier in units:
    if size.endswith(suffix):
      num_units = size[:-len(suffix)]
      try:
        return int(num_units) * multiplier
      except (ValueError, KeyError):
        break
  return None
