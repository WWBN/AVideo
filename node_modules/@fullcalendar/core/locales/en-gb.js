var l20 = {
    code: 'en-gb',
    week: {
        dow: 1,
        doy: 4, // The week that contains Jan 4th is the first week of the year.
    },
    buttonHints: {
        prev: 'Previous $0',
        next: 'Next $0',
        today: 'This $0',
    },
    viewHint: '$0 view',
    navLinkHint: 'Go to $0',
    moreLinkHint(eventCnt) {
        return `Show ${eventCnt} more event${eventCnt === 1 ? '' : 's'}`;
    },
};

export { l20 as default };
