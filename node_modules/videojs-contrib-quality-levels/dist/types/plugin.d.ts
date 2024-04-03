export default qualityLevels;
/**
 * A video.js plugin.
 *
 * In the plugin function, the value of `this` is a video.js `Player`
 * instance. You cannot rely on the player being in a "ready" state here,
 * depending on how the plugin is invoked. This may or may not be important
 * to you; if not, remove the wait for "ready"!
 *
 * @param {Object} options Plugin options object
 * @return {QualityLevelList} a list of QualityLevels
 */
declare function qualityLevels(options: any): QualityLevelList;
declare namespace qualityLevels {
    export { VERSION };
}
import QualityLevelList from './quality-level-list.js';
//# sourceMappingURL=plugin.d.ts.map