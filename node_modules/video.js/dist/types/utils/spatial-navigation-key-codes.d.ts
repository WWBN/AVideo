export default SpatialNavKeyCodes;
declare namespace SpatialNavKeyCodes {
    namespace codes {
        export const play: number;
        export const pause: number;
        export const ff: number;
        export const rw: number;
        export { backKeyCode as back };
    }
    const names: {
        [x: number]: string;
        415: string;
        19: string;
        417: string;
        412: string;
    };
    function isEventKey(event: any, keyName: any): boolean;
    function getEventName(event: any): string;
}
declare const backKeyCode: 10009 | 461 | 8;
//# sourceMappingURL=spatial-navigation-key-codes.d.ts.map