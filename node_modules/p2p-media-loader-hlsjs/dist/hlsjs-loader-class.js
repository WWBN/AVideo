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

function createHlsJsLoaderClass(HlsJsLoader, engine) {
    function HlsJsLoaderClass() {
        this.impl = new HlsJsLoader(engine.segmentManager);
        this.stats = this.impl.stats;
    }

    HlsJsLoaderClass.prototype.load = function (context, config, callbacks) {
        this.context = context;
        this.impl.load(context, config, callbacks);
    };

    HlsJsLoaderClass.prototype.abort = function () {
        this.impl.abort(this.context);
    };

    HlsJsLoaderClass.prototype.destroy = function () {
        if (this.context) {
            this.impl.abort(this.context);
        }
    };

    HlsJsLoaderClass.getEngine = function () {
        return engine;
    };

    return HlsJsLoaderClass;
}

module.exports.createHlsJsLoaderClass = createHlsJsLoaderClass;
