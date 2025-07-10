export declare function setOption<T, K extends keyof T>(options: T, key: K, value: T[K]): T;
export declare function setOptions<T>(options: T, partialOptions: Partial<T>): T;
