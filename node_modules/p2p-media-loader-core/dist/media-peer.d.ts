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
import { STEEmitter } from "./stringly-typed-event-emitter";
export declare enum MediaPeerSegmentStatus {
    Loaded = 0,
    LoadingByHttp = 1
}
export declare class MediaPeer extends STEEmitter<"connect" | "close" | "data-updated" | "segment-request" | "segment-absent" | "segment-loaded" | "segment-error" | "segment-timeout" | "bytes-downloaded" | "bytes-uploaded"> {
    readonly peer: any;
    readonly settings: {
        p2pSegmentDownloadTimeout: number;
        webRtcMaxMessageSize: number;
    };
    id: string;
    remoteAddress: string;
    private downloadingSegmentId;
    private downloadingSegment;
    private segmentsMap;
    private debug;
    private timer;
    constructor(peer: any, settings: {
        p2pSegmentDownloadTimeout: number;
        webRtcMaxMessageSize: number;
    });
    private onPeerConnect;
    private onPeerClose;
    private onPeerError;
    private receiveSegmentPiece;
    private getJsonCommand;
    private onPeerData;
    private createSegmentsMap;
    private sendCommand;
    destroy(): void;
    getDownloadingSegmentId(): string | null;
    getSegmentsMap(): Map<string, MediaPeerSegmentStatus>;
    sendSegmentsMap(segmentsMap: {
        [key: string]: [string, number[]];
    }): void;
    sendSegmentData(segmentId: string, data: ArrayBuffer): void;
    sendSegmentAbsent(segmentId: string): void;
    requestSegment(segmentId: string): void;
    cancelSegmentRequest(): ArrayBuffer[] | undefined;
    private runResponseTimeoutTimer;
    private cancelResponseTimeoutTimer;
    private terminateSegmentRequest;
}
