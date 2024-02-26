import { values } from './object';

export const range = (start, end) => {
  const result = [];

  for (let i = start; i < end; i++) {
    result.push(i);
  }

  return result;
};

export const flatten = lists => lists.reduce((x, y) => x.concat(y), []);

export const from = list => {
  if (!list.length) {
    return [];
  }

  const result = [];

  for (let i = 0; i < list.length; i++) {
    result.push(list[i]);
  }

  return result;
};

export const findIndexes = (l, key) => l.reduce((a, e, i) => {
  if (e[key]) {
    a.push(i);
  }

  return a;
}, []);

/**
 * Returns a union of the included lists provided each element can be identified by a key.
 *
 * @param {Array} list - list of lists to get the union of
 * @param {Function} keyFunction - the function to use as a key for each element
 *
 * @return {Array} the union of the arrays
 */
export const union = (lists, keyFunction) => {
  return values(lists.reduce((acc, list) => {
    list.forEach((el) => {
      acc[keyFunction(el)] = el;
    });

    return acc;
  }, {}));
};
