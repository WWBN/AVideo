export default log;
declare const log: {
    (...args: any[]): void;
    createLogger(subName: string, subDelimiter?: string, subStyles?: string): any;
    createNewLogger(newName: string, newDelimiter?: string, newStyles?: string): any;
    levels: any;
    level(lvl?: "info" | "error" | "all" | "debug" | "warn" | "off"): string;
    history: {
        (): any[];
        filter(fname: string): any[];
        clear(): void;
        disable(): void;
        enable(): void;
    };
    error(...args: any[]): any;
    warn(...args: any[]): any;
    debug(...args: any[]): any;
};
export const createLogger: (subName: string, subDelimiter?: string, subStyles?: string) => any;
//# sourceMappingURL=log.d.ts.map