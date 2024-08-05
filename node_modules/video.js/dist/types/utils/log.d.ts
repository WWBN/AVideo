export default log;
declare const log: {
    (...args: any[]): void;
    createLogger(subName: string, subDelimiter?: string, subStyles?: string): any;
    createNewLogger(newName: string, newDelimiter?: string, newStyles?: string): any;
    levels: any;
    level(lvl?: "all" | "debug" | "info" | "warn" | "error" | "off"): string;
    history: {
        (): any[];
        filter(fname: string): any[];
        clear(): void;
        disable(): void;
        enable(): void;
    };
    error(...args: any[]): void;
    warn(...args: any[]): void;
    debug(...args: any[]): void;
};
export const createLogger: (subName: string, subDelimiter?: string, subStyles?: string) => any;
//# sourceMappingURL=log.d.ts.map