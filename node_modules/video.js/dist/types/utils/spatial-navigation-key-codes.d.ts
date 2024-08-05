export default SpatialNavKeyCodes;
declare namespace SpatialNavKeyCodes {
    namespace codes {
        export let play: number;
        export let pause: number;
        export let ff: number;
        export let rw: number;
        export { backKeyCode as back };
    }
    let names: {
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