var l35 = {
    code: 'hr',
    week: {
        dow: 1,
        doy: 7, // The week that contains Jan 1st is the first week of the year.
    },
    buttonText: {
        prev: 'Prijašnji',
        next: 'Sljedeći',
        today: 'Danas',
        year: 'Godina',
        month: 'Mjesec',
        week: 'Tjedan',
        day: 'Dan',
        list: 'Raspored',
    },
    weekText: 'Tje',
    allDayText: 'Cijeli dan',
    moreLinkText(n) {
        return '+ još ' + n;
    },
    noEventsText: 'Nema događaja za prikaz',
};

export { l35 as default };
