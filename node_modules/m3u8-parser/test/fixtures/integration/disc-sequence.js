module.exports = {
  allowCache: true,
  dateRanges: [],
  iFramePlaylists: [],
  mediaSequence: 0,
  discontinuitySequence: 3,
  segments: [
    {
      duration: 10,
      timeline: 3,
      uri: '001.ts',
      title: '0'
    },
    {
      duration: 19,
      timeline: 3,
      uri: '002.ts',
      title: '0'
    },
    {
      discontinuity: true,
      duration: 10,
      timeline: 4,
      uri: '003.ts',
      title: '0'
    },
    {
      duration: 11,
      timeline: 4,
      uri: '004.ts',
      title: '0'
    }
  ],
  targetDuration: 19,
  endList: true,
  discontinuityStarts: [2],
  version: 3
};
