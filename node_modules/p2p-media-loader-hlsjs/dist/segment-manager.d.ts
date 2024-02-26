/**
 * Copyright 2018 Novage LLC.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
import { LoaderInterface } from "p2p-media-loader-core";
import { AssetsStorage } from "./engine";
export declare type Byterange = {
    length: number;
    offset: number;
} | undefined;
export declare class SegmentManager {
    private readonly loader;
    private masterPlaylist;
    private readonly variantPlaylists;
    private segmentRequest;
    private playQueue;
    private readonly settings;
    constructor(loader: LoaderInterface, settings?: Partial<SegmentManagerSettings>);
    getSettings(): SegmentManagerSettings;
    processPlaylist(requestUrl: string, content: string, responseUrl: string): void;
    loadPlaylist(url: string): Promise<{
        response: string;
        responseURL: string;
    }>;
    loadSegment(url: string, byterange: Byterange): Promise<{
        content: ArrayBuffer | undefined;
        downloadBandwidth?: number;
    }>;
    setPlayingSegment(url: string, byterange: Byterange, start: number, duration: number): void;
    setPlayingSegmentByCurrentTime(playheadPosition: number): void;
    abortSegment(url: string, byterange: Byterange): void;
    destroy(): Promise<void>;
    private updateSegments;
    private onSegmentLoaded;
    private onSegmentError;
    private onSegmentAbort;
    private getSegmentLocation;
    private loadSegments;
    private getSegmentId;
    private getMasterSwarmId;
    private getStreamSwarmId;
    private loadContent;
}
export interface SegmentManagerSettings {
    /**
     * Number of segments for building up predicted forward segments sequence; used to predownload and share via P2P
     */
    forwardSegmentCount: number;
    /**
     * Override default swarm ID that is used to identify unique media stream with trackers (manifest URL without
     * query parameters is used as the swarm ID if the parameter is not specified)
     */
    swarmId?: string;
    /**
     * A storage for the downloaded assets: manifests, subtitles, init segments, DRM assets etc. By default the assets are not stored.
     */
    assetsStorage?: AssetsStorage;
}
