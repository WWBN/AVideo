define([], function() {
    return function(version) {
        switch (version) {
            case '1.5.2': return 0;
            case '2.0.0': return 1;
            case '2.1.0':
            case '2.1.1': return 2;
            case '2.1.2': return 3;
            case '2.1.3':
            case '2.1.4':
            case '2.2.7': return 4;
            case '3.0.1':
            case '3.0.2':
            case '3.0.3':
            case '3.0': return 5;
            case '3.1.0':
            case '3.1.1':
            case '3.1.2':
            case '3.1':
            default: return 6;
        }
    };
});
