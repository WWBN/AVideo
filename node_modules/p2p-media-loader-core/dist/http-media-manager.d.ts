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
import { Segment } from "./loader-interface";
import { SegmentValidatorCallback, XhrSetupCallback, SegmentUrlBuilder } from "./hybrid-loader";
export declare class HttpMediaManager extends STEEmitter<"segment-loaded" | "segment-error" | "bytes-downloaded"> {
    readonly settings: {
        httpFailedSegmentTimeout: number;
        httpUseRanges: boolean;
        segmentValidator?: SegmentValidatorCallback;
        xhrSetup?: XhrSetupCallback;
        segmentUrlBuilder?: SegmentUrlBuilder;
    };
    private xhrRequests;
    private failedSegments;
    private debug;
    constructor(settings: {
        httpFailedSegmentTimeout: number;
        httpUseRanges: boolean;
        segmentValidator?: SegmentValidatorCallback;
        xhrSetup?: XhrSetupCallback;
        segmentUrlBuilder?: SegmentUrlBuilder;
    });
    download(segment: Segment, downloadedPieces?: ArrayBuffer[]): void;
    abort(segment: Segment): void;
    isDownloading(segment: Segment): boolean;
    isFailed(segment: Segment): boolean;
    getActiveDownloads(): ReadonlyMap<string, {
        segment: Segment;
    }>;
    getActiveDownloadsCount(): number;
    destroy(): void;
    private setupXhrEvents;
    private segmentDownloadFinished;
    private segmentFailure;
    private cleanTimedOutFailedSegments;
    private now;
}
