/**
 * Mimetypes
 */
export type MimetypesKind = any;
export namespace MimetypesKind {
    let opus: string;
    let ogv: string;
    let mp4: string;
    let mov: string;
    let m4v: string;
    let mkv: string;
    let m4a: string;
    let mp3: string;
    let aac: string;
    let caf: string;
    let flac: string;
    let oga: string;
    let wav: string;
    let m3u8: string;
    let mpd: string;
    let jpg: string;
    let jpeg: string;
    let gif: string;
    let png: string;
    let svg: string;
    let webp: string;
}
export function getMimetype(src?: string): string;
export function findMimetype(player: Player, src: string): string;
/**
 * ~Kind
 */
export type Mimetypes = any;
import type Player from '../player';
//# sourceMappingURL=mimetypes.d.ts.map