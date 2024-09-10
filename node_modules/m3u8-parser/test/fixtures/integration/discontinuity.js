module.exports = {
  allowCache: true,
  dateRanges: [],
  iFramePlaylists: [],
  mediaSequence: 0,
  discontinuitySequence: 0,
  segments: [
    {
      duration: 10,
      timeline: 0,
      uri: '001.ts',
      title: '0'
    },
    {
      duration: 19,
      timeline: 0,
      uri: '002.ts',
      title: '0'
    },
    {
      discontinuity: true,
      duration: 10,
      timeline: 1,
      uri: '003.ts',
      title: '0'
    },
    {
      duration: 11,
      timeline: 1,
      uri: '004.ts',
      title: '0'
    },
    {
      discontinuity: true,
      duration: 10,
      timeline: 2,
      uri: '005.ts',
      title: '0'
    },
    {
      duration: 10,
      timeline: 2,
      uri: '006.ts',
      title: '0'
    },
    {
      duration: 10,
      timeline: 2,
      uri: '007.ts',
      title: '0'
    },
    {
      discontinuity: true,
      duration: 10,
      timeline: 3,
      uri: '008.ts',
      title: '0'
    },
    {
      duration: 16,
      timeline: 3,
      uri: '009.ts',
      title: '0'
    }
  ],
  targetDuration: 19,
  endList: true,
  discontinuityStarts: [2, 4, 7],
  version: 3
};
