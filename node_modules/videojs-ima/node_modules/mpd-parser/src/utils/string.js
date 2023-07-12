/**
 * Converts the provided string that may contain a division operation to a number.
 *
 * @param {string} value - the provided string value
 *
 * @return {number} the parsed string value
 */
export const parseDivisionValue = (value) => {
  return parseFloat(value.split('/').reduce((prev, current) => prev / current));
};
