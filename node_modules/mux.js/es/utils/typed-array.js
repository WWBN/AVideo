// IE11 doesn't support indexOf for TypedArrays.
// Once IE11 support is dropped, this function should be removed.
var typedArrayIndexOf = function typedArrayIndexOf(typedArray, element, fromIndex) {
  if (!typedArray) {
    return -1;
  }

  var currentIndex = fromIndex;

  for (; currentIndex < typedArray.length; currentIndex++) {
    if (typedArray[currentIndex] === element) {
      return currentIndex;
    }
  }

  return -1;
};

module.exports = {
  typedArrayIndexOf: typedArrayIndexOf
};