export default QualityLevelList;
/**
 * A list of QualityLevels.
 *
 * interface QualityLevelList : EventTarget {
 *   getter QualityLevel (unsigned long index);
 *   readonly attribute unsigned long length;
 *   readonly attribute long selectedIndex;
 *
 *   void addQualityLevel(QualityLevel qualityLevel)
 *   void removeQualityLevel(QualityLevel remove)
 *   QualityLevel? getQualityLevelById(DOMString id);
 *
 *   attribute EventHandler onchange;
 *   attribute EventHandler onaddqualitylevel;
 *   attribute EventHandler onremovequalitylevel;
 * };
 *
 * @extends videojs.EventTarget
 * @class QualityLevelList
 */
declare class QualityLevelList extends videojs.EventTarget {
    levels_: any[];
    selectedIndex_: number;
    /**
     * Adds a quality level to the list.
     *
     * @param {Representation|Object} representation The representation of the quality level
     * @param {string}   representation.id        Unique id of the QualityLevel
     * @param {number=}  representation.width     Resolution width of the QualityLevel
     * @param {number=}  representation.height    Resolution height of the QualityLevel
     * @param {number}   representation.bandwidth Bitrate of the QualityLevel
     * @param {number=}  representation.frameRate Frame-rate of the QualityLevel
     * @param {Function} representation.enabled   Callback to enable/disable QualityLevel
     * @return {QualityLevel} the QualityLevel added to the list
     * @method addQualityLevel
     */
    addQualityLevel(representation: Representation | any): QualityLevel;
    /**
     * Removes a quality level from the list.
     *
     * @param {QualityLevel} qualityLevel The QualityLevel to remove from the list.
     * @return {QualityLevel|null} the QualityLevel removed or null if nothing removed
     * @method removeQualityLevel
     */
    removeQualityLevel(qualityLevel: QualityLevel): QualityLevel | null;
    /**
     * Searches for a QualityLevel with the given id.
     *
     * @param {string} id The id of the QualityLevel to find.
     * @return {QualityLevel|null} The QualityLevel with id, or null if not found.
     * @method getQualityLevelById
     */
    getQualityLevelById(id: string): QualityLevel | null;
    /**
     * Resets the list of QualityLevels to empty
     *
     * @method dispose
     */
    dispose(): void;
    /**
     * change - The selected QualityLevel has changed.
     * addqualitylevel - A QualityLevel has been added to the QualityLevelList.
     * removequalitylevel - A QualityLevel has been removed from the QualityLevelList.
     */
    allowedEvents_: {
        change: string;
        addqualitylevel: string;
        removequalitylevel: string;
    };
    [Symbol.iterator]: () => IterableIterator<any>;
}
import videojs from 'video.js';
import QualityLevel from './quality-level.js';
//# sourceMappingURL=quality-level-list.d.ts.map