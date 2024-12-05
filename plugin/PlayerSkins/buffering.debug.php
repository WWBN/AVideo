<div class="row">
    <!-- FPS -->
    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6">
        <i class="fas fa-tachometer-alt"></i> <strong>FPS:</strong>
        <span id="fps" class="text-info">Calculating...</span>
    </div>
    <!-- Resolution -->
    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6">
        <i class="fas fa-expand"></i> <strong>Resolution:</strong>
        <span id="resolution" class="text-info">Fetching...</span>
    </div>
    <!-- Buffered Time -->
    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6">
        <i class="fas fa-hourglass-half"></i> <strong>Buffered Time:</strong>
        <span id="bufferedTime" class="text-info">Fetching...</span>s
    </div>
    <!-- Buffer Health -->
    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6">
        <i class="fas fa-heartbeat"></i> <strong>Buffer Health:</strong>
        <span id="bufferHealth" class="text-info">Calculating...</span>
    </div>
    <!-- Dropped Frames -->
    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6">
        <i class="fas fa-times-circle"></i> <strong>Dropped Frames:</strong>
        <span id="droppedFrames" class="text-info">Fetching...</span>
    </div>
    <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6">
        <button id="analyzeButton" class="btn btn-primary btn-xs btn-block">Start Analysis</button>
    </div>
    <div class="col-lg-12">
        <ul id="recommendations" class="list-group"></ul>
    </div>
</div>
<script>
    // Suppress Video.js warnings (if necessary)
    videojs.log.level('error');

    function initializePlayerAnalyzer() {
        if (typeof player === 'undefined') {
            console.warn('initializePlayerAnalyzer Player undefined, retrying in 1 second...');
            setTimeout(initializePlayerAnalyzer, 1000);
            return;
        }
        console.warn('initializePlayerAnalyzer start');

        let previousDecodedFrames = 0;
        let previousTime = 0;
        let videoStarted = false;
        let startTime = null;

        function calculateFPS() {
            const videoElement = player.el().querySelector('video');

            if (videoElement && typeof videoElement.getVideoPlaybackQuality === 'function') {
                const videoQuality = videoElement.getVideoPlaybackQuality();
                const currentTime = performance.now();

                const decodedFrames = videoQuality.totalVideoFrames || videoQuality.totalFrames;
                const deltaFrames = decodedFrames - previousDecodedFrames;
                const deltaTime = currentTime - previousTime;

                previousDecodedFrames = decodedFrames;
                previousTime = currentTime;

                if (deltaTime > 0) {
                    const fps = (deltaFrames / (deltaTime / 1000)).toFixed(0);
                    $('#fps').text(fps + ' fps').removeClass().addClass(
                        fps >= 20 && fps <= 35 ? 'text-success' :
                        fps > 35 ? 'text-warning' :
                        'text-danger'
                    );
                    return parseInt(fps);
                }
            } else {
                $('#fps').text('N/A').removeClass().addClass('text-info');
            }
            return 0;
        }

        function updateVideoInfo() {
            const videoElement = player.el().querySelector('video');
            let resolution = 'N/A';

            if (videoElement) {
                const width = videoElement.videoWidth;
                const height = videoElement.videoHeight;
                resolution = `${width}x${height}`;
                $('#resolution').text(resolution).removeClass().addClass(
                    resolution === '1280x720' ? 'text-success' :
                    resolution === '1920x1080' ? 'text-warning' :
                    'text-danger'
                );
            } else {
                $('#resolution').text('N/A').removeClass().addClass('text-info');
            }

            return {
                resolution
            };
        }

        function updateBufferMetrics() {
            const bufferedEnd = player.bufferedEnd();
            const currentTime = player.currentTime();
            const bufferHealth = (bufferedEnd - currentTime).toFixed(0);

            $('#bufferedTime').text(bufferedEnd ? bufferedEnd.toFixed(0) : '0');
            $('#bufferHealth').text(bufferHealth + 's').removeClass().addClass(
                bufferHealth >= 10 ? 'text-success' :
                bufferHealth >= 5 ? 'text-warning' :
                'text-danger'
            );
        }

        function updateDroppedFrames() {
            const videoElement = player.el().querySelector('video');

            if (videoElement && typeof videoElement.getVideoPlaybackQuality === 'function') {
                const videoQuality = videoElement.getVideoPlaybackQuality();
                const droppedFrames = videoQuality.droppedVideoFrames || 0;

                $('#droppedFrames').text(droppedFrames).removeClass().addClass(
                    droppedFrames <= 5 ? 'text-success' :
                    droppedFrames <= 10 ? 'text-warning' :
                    'text-danger'
                );
            } else {
                $('#droppedFrames').text('N/A').removeClass().addClass('text-info');
            }
        }

        function generateRecommendations(fps, resolution) {
            const recommendations = [];
            const resolutionParts = resolution.split('x');
            const width = parseInt(resolutionParts[0]) || 0;
            const height = parseInt(resolutionParts[1]) || 0;

            if (width > 1920 || height > 1080) {
                recommendations.push('Reduce resolution to a maximum of 1080p. Ideal resolution is 720p.');
            } else if (width !== 1280 && height !== 720) {
                recommendations.push('Use standard resolutions like 720p or 1080p to prevent scaling issues.');
            }

            if (fps < 20 && videoStarted && (Date.now() - startTime > 5000)) {
                recommendations.push('FPS is too low. Check your encoder settings.');
            } else if (fps > 35 && videoStarted && (Date.now() - startTime > 5000)) {
                recommendations.push('FPS is too high. Adjust FPS to 30.');
            }

            const recommendationsList = $('#recommendations');
            recommendations.forEach((rec) => {
                if (!lastTriggeredAlerts.has(rec)) {
                    lastTriggeredAlerts.set(rec, true);
                    recommendationsList.append(`<li class="list-group-item text-warning">${rec}</li>`);
                }
            });
        }

        function startAnalysis() {
            if (analysisActive) {
                clearInterval(intervalId);
                analysisActive = false;
                $('#analyzeButton').text('Start Analysis').removeClass('btn-danger').addClass('btn-primary');
            } else {
                intervalId = setInterval(() => {
                    if (!videoStarted && !player.paused()) {
                        videoStarted = true;
                        startTime = Date.now();
                    }

                    if (videoStarted) {
                        const fps = calculateFPS();
                        const {
                            resolution
                        } = updateVideoInfo();
                        updateBufferMetrics();
                        updateDroppedFrames();

                        if (videoStarted && (Date.now() - startTime > 5000)) {
                            generateRecommendations(fps, resolution);
                        }
                    }
                }, 1000);
                analysisActive = true;
                $('#analyzeButton').text('Stop Analysis').removeClass('btn-primary').addClass('btn-danger');
            }
        }

        $('#analyzeButton').on('click', startAnalysis);

        startAnalysis();
        console.warn('initializePlayerAnalyzer end');
    }

    const lastTriggeredAlerts = new Map();
    let analysisActive = false;
    let intervalId;
    $(function() {
        console.warn('initializePlayerAnalyzer onload');
        initializePlayerAnalyzer();
    });
</script>