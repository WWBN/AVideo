/**
 * A single QualityLevel.
 *
 * interface QualityLevel {
 *   readonly attribute DOMString id;
 *            attribute DOMString label;
 *   readonly attribute long width;
 *   readonly attribute long height;
 *   readonly attribute long bitrate;
 *            attribute boolean enabled;
 * };
 *
 * @class QualityLevel
 */
export default class QualityLevel {
    /**
     * Creates a QualityLevel
     *
     * @param {Representation|Object} representation The representation of the quality level
     * @param {string}   representation.id        Unique id of the QualityLevel
     * @param {number=}  representation.width     Resolution width of the QualityLevel
     * @param {number=}  representation.height    Resolution height of the QualityLevel
     * @param {number}   representation.bandwidth Bitrate of the QualityLevel
     * @param {number=}  representation.frameRate Frame-rate of the QualityLevel
     * @param {Function} representation.enabled   Callback to enable/disable QualityLevel
     */
    constructor(representation: Representation | any);
    id: any;
    label: any;
    width: any;
    height: any;
    bitrate: any;
    frameRate: any;
    enabled_: any;
}
//# sourceMappingURL=quality-level.d.ts.map