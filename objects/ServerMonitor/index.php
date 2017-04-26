<!doctype html>
<html class="no-js">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>jquery-asPieProgress</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link rel="stylesheet" href="gauge/css/asPieProgress.css">



        <style type="text/css">
            body {
                padding: 40px;
            }
            .pie_progress {
                width: 160px;
                margin: 10px auto;
            }
            @media all and (max-width: 768px) {
                .pie_progress {
                    width: 80%;
                    max-width: 300px;
                }
            }
        </style>
    </head>
    <body>
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-lg-4" id="cpuDiv">                        
                <div class="pie_progress_cpu" role="progressbar" data-goal="33">
                    <div class="pie_progress__number">0%</div>
                    <div class="pie_progress__label">CPU</div>
                </div>
                <h1>Cpu</h1>
                <h2></h2>
                <pre></pre>
            </div>
            <div class="col-xs-12 col-sm-12 col-lg-4" id="memDiv">
                <div class="pie_progress_mem" role="progressbar" data-goal="33">
                    <div class="pie_progress__number">0%</div>
                    <div class="pie_progress__label">Memory</div>
                </div>
                <h1>Memory</h1>
                <h2></h2>
                <pre></pre>
            </div>
            <div class="col-xs-12 col-sm-12 col-lg-4" id="diskDiv">
                <div class="pie_progress_disk" role="progressbar" data-goal="33">
                    <div class="pie_progress__number">0%</div>
                    <div class="pie_progress__label">Disk</div>
                </div>
                <h1>Disk</h1>
                <h2></h2>
                <pre></pre>
            </div>
        </div>
        <script
            src="https://code.jquery.com/jquery-3.2.1.min.js"
            integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
        crossorigin="anonymous"></script>
        <script type="text/javascript" src="gauge/jquery-asPieProgress.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                // Example with grater loading time - loads longer
                $('.pie_progress_cpu, .pie_progress_mem, .pie_progress_disk').asPieProgress({
                    namespace: 'pie_progress',
                    goal: 100,
                    min: 0,
                    max: 100,
                    speed: 50,
                    easing: 'linear'
                });
                getCpu();
                getMem();
                getDisk();
            });

            function getCpu() {
                $.ajax({
                    url: 'cpu.json.php',
                    success: function (response) {
                        update('cpu', response);
                        setTimeout(function () {
                            getCpu();
                        }, 1000);
                    }
                });
            }

            function getMem() {
                $.ajax({
                    url: 'memory.json.php',
                    success: function (response) {
                        update('mem', response);

                        setTimeout(function () {
                            getMem();
                        }, 1000);
                    }
                });
            }

            function getDisk() {
                $.ajax({
                    url: 'disk.json.php',
                    success: function (response) {
                        update('disk', response);
                        setTimeout(function () {
                            getDisk();
                        }, 1000);
                    }
                });
            }

            function update(name, response) {
                $('.pie_progress_' + name).asPieProgress('go', response.percent);
                $("#" + name + "Div h2").text(response.title);
                $("#" + name + "Div pre").text(response.output.join('\n'));
            }
        </script>
    </body>
</html>
