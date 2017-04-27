<!doctype html>
<html class="no-js">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Server monitor</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link rel="stylesheet" href="gauge/css/asPieProgress.css">
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">


        <style type="text/css">
            body {
                padding-top: 60px;
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
            pre{
                height: 250px;
                overflow: scroll;
            }
            .title{
                height: 50px;
            }
        </style>
    </head>
    <body><nav class="navbar navbar-default navbar-fixed-top">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#"></a>
                </div>
                <div id="navbar" class="navbar-collapse collapse">
                    <a href="https://github.com/DanielnetoDotCom/ServerMonitor" class="btn btn-success navbar-btn pull-right" ><span class="fa fa-github"></span> Download <strong>ServerMonitor</strong> Source Code Free Now</a>
                </div><!--/.nav-collapse -->
            </div>
        </nav>
        <div class="container">
            <div class="col-xs-4 col-sm-4 col-lg-4" id="cpuDiv">                        
                <div class="pie_progress_cpu" role="progressbar" data-goal="33">
                    <div class="pie_progress__number">0%</div>
                    <div class="pie_progress__label">CPU</div>
                </div>
                <h1>Cpu</h1>
                <div class='title'></div>
                <pre></pre>
            </div>
            <div class="col-xs-4 col-sm-4 col-lg-4" id="memDiv">
                <div class="pie_progress_mem" role="progressbar" data-goal="33">
                    <div class="pie_progress__number">0%</div>
                    <div class="pie_progress__label">Memory</div>
                </div>
                <h1>Memory</h1>
                <div class='title'></div>
                <pre></pre>
            </div>
            <div class="col-xs-4 col-sm-4 col-lg-4" id="diskDiv">
                <div class="pie_progress_disk" role="progressbar" data-goal="33">
                    <div class="pie_progress__number">0%</div>
                    <div class="pie_progress__label">Disk</div>
                </div>
                <h1>Disk</h1>
                <div class='title'></div>
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
                $('.pie_progress_cpu, .pie_progress_mem, .pie_progress_disk').asPieProgress({});
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
                $("#" + name + "Div div.title").text(response.title);
                $("#" + name + "Div pre").text(response.output.join('\n'));
            }
        </script>
    </body>
</html>
