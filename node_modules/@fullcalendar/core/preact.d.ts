import * as preact from 'preact';
export * from 'preact';
export { createPortal } from 'preact/compat';

declare function flushSync(runBeforeFlush: any): void;
declare function createContext<T>(defaultValue: T): preact.Context<T>;

export { createContext, flushSync };
