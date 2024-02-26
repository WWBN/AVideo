const path = require('path');
const expect = require('chai').expect;
const DPDBFormatted = require('../dpdb-formatted.json');
const DPDBMinified = require('../dpdb.json');

const suites = [
  { name: 'formatted', data: DPDBFormatted },
  { name: 'minified', data: DPDBMinified },
];

suites.forEach(function (suite) {
  const dpdb = suite.data;

  describe(`DPDB (${suite.name})`, function () {
    it('has format: 1', function () {
      expect(dpdb.format).to.equal(1);
    });

    it('has last_updated in ISO 8601 format', function () {
      const iso = new Date(dpdb.last_updated).toISOString();
      expect(dpdb.last_updated).to.equal(iso.replace(/\.000/, ''));
    });

    describe('device parameters', function () {
      it('all devices are of type "android" or "ios"', function () {
        expect(dpdb.devices.every(device => device.type === 'android' || device.type === 'ios')).to.equal(true);
      });

      it('all bezel widths are defined numbers', function () {
        expect(dpdb.devices.every(device => typeof device.bw === 'number')).to.equal(true);
      });

      it('all accuracy values are 0, 500 or 1000', function () {
        const valid = [0, 500, 1000];
        expect(dpdb.devices.every(device => valid.indexOf(device.ac) !== -1)).to.equal(true);
      });

      it('all DPI values are scalars, or [X,Y] array of numbers', function () {
        dpdb.devices.forEach(device => {
          if (Array.isArray(device.dpi)) {
            expect(device.dpi.length).to.equal(2);
            expect(device.dpi[0]).to.be.a('number');
            expect(device.dpi[1]).to.be.a('number');
          } else {
            expect(device.dpi).to.be.a('number');
          }
        });
      });
    });

    describe('rules', function () {
      it('all devices have atleast one rule', function () {
        expect(dpdb.devices.every(device => device.rules.length >= 1)).to.equal(true);
      });

      it('all rules have one rule type', function () {
        dpdb.devices.forEach(device => {
          device.rules.forEach(rule => {
            let ruleCount = 0;
            if (rule.mdmh) ruleCount++;
            if (rule.ua) ruleCount++;
            if (rule.res) ruleCount++;
            expect(ruleCount).to.equal(1);
          });
        });
      });

      it('all iOS devices have only a resolution rule', function () {
        dpdb.devices.filter(device => device.type === 'ios').forEach(function (device) {
          expect(device.rules.length).to.equal(1);
          expect(device.rules[0].res[0]).to.be.a('number');
          expect(device.rules[0].res[1]).to.be.a('number');
        });
      });
      
      it('all Android devices have a MDMH and UA rule', function () {
        dpdb.devices.filter(device => device.type === 'android').forEach(function (device) {
          expect(device.rules.length).to.equal(2);
          expect(device.rules.find(r => r.mdmh).mdmh).to.be.a('string');
          expect(device.rules.find(r => r.ua).ua).to.be.a('string');
        });
      });

      it('all MDMH rules are properly formatted "model/device/manufacturer/hardware" strings', function () {
        dpdb.devices.forEach(device => {
          const rule = device.rules.find(rule => rule.mdmh);
          if (!rule) {
            return;
          }
          const split = rule.mdmh.split('/');
          expect(split.length).to.equal(4);
          expect(split.every(s => s.length > 0)).to.equal(true);
        });
      });
    });
  });
});
