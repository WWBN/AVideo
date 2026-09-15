/* Run with node tests/Integration/registrationCharts.js. No database or browser required. */
const assert = require('assert');
const fs = require('fs');
const vm = require('vm');
const path = require('path');
const context = {window: {}, jQuery: {}};
vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../../view/js/reportDashboard.js'), 'utf8'), context);
const series = (...args) => JSON.parse(JSON.stringify(context.window.AVideoReports.registrationSeries(...args)));

const daily = series({'2024-02-28': 2, '2024-03-01': 3}, 3, false, '2024-03-01');
assert.deepStrictEqual(daily.values, [2, 0, 3], 'Leap day without registrations must remain visible');
assert.deepStrictEqual(daily.ranges[1], ['2024-02-29', '2024-02-29']);
const growth = series({'2024-02-01': 20, '2024-02-28': 22, '2024-03-01': 25}, 3, true, '2024-03-01');
assert.deepStrictEqual(growth.values, [22, 22, 25], 'Carry forward existing accounts; never sum cumulative totals');
assert.deepStrictEqual(series({'2024-01-01': 20}, 3, true, '2024-03-01').values, [20, 20, 20]);
assert.deepStrictEqual(series({'2024-01-01': 20}, 3, false, '2024-03-01').values, [0, 0, 0]);

const counts = {'2023-12-31': 50, '2024-01-01': 2, '2024-01-31': 3, '2024-03-01': 4};
const monthly = series(counts, 365, false, '2024-12-30');
assert.strictEqual(monthly.unit, 'month');
assert.strictEqual(monthly.values.reduce((sum, value) => sum + value, 0), 9, 'Exclude registrations before the cutoff');
assert.strictEqual(monthly.values[0], 5);
assert.strictEqual(monthly.values[1], 0, 'Keep empty months');
assert.strictEqual(monthly.values.length, 12);
const accumulated = series({'2023-12-31': 50, '2024-01-01': 52, '2024-01-31': 55, '2024-03-01': 59}, 365, true, '2024-12-30');
assert.deepStrictEqual(accumulated.values.slice(0, 3), [55, 55, 59]);
assert.strictEqual(accumulated.values.at(-1), 59);

const weekly = series({'2024-01-01': 100, '2024-01-30': 2, '2024-02-04': 3, '2024-02-05': 4}, 32, false, '2024-03-01');
assert.strictEqual(weekly.unit, 'week');
assert.strictEqual(weekly.partial[0], true, 'Identify incomplete intervals to avoid misleading comparisons');
assert.deepStrictEqual(weekly.ranges[0], ['2024-01-30', '2024-02-04'], 'Clip the first week to the selected period');
assert.strictEqual(weekly.values[0], 5);
assert.strictEqual(weekly.values[1], 4);
assert.strictEqual(weekly.values.reduce((sum, value) => sum + value, 0), 9);

const history = {};
for (let year = 1900; year <= 2026; year++) { history[year + '-01-01'] = 1; }
const long = series(history, 0, false, '2026-09-15');
assert(long.values.length <= 31, 'A century of data must not create thousands of columns');
assert.strictEqual(long.values.reduce((sum, value) => sum + value, 0), 127, 'Grouping must preserve all registrations');
assert.deepStrictEqual(series({}, 365, false, '2026-09-15').values, []);
assert.deepStrictEqual(series({'2027-01-01': 1}, 365, false, '2026-09-15').values, []);
console.log('PASS: registration grouping, date boundaries, empty intervals and cumulative totals.');
