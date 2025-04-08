<?php
use OpenApi\Attributes as OA;
$configFile = __DIR__ . '/../../videos/configuration.php';
require_once $configFile;

if (!User::isAdmin()) {
    forbiddenPage('You need to be an admin to access this page');
}

?>
<!-- HTML for static distribution bundle build -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>OpenAPI 3.1 specification</title>
    <link rel="stylesheet" type="text/css" href="./docs/swagger-ui.css" />
    <link rel="stylesheet" type="text/css" href="./docs/index.css" />
    <link rel="stylesheet" type="text/css" href="./swagger.css" />
</head>

<body>
    <div id="swagger-ui"></div>
    <script src="./docs/swagger-ui-bundle.js" charset="UTF-8"> </script>
    <script src="./docs/swagger-ui-standalone-preset.js" charset="UTF-8"> </script>
    <script src="./docs/swagger-initializer.js?2" charset="UTF-8"> </script>
    <script>
        window.onload = function() {
            window.ui = SwaggerUIBundle({
                url: "./swagger.json.php",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",

                tagsSorter: "alpha",
                operationsSorter: (a, b) => {
                    return a.get("path").localeCompare(b.get("path"));
                }
            });
        };
    </script>
</body>

</html>
