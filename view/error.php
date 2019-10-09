<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
header("HTTP/1.0 404 Not Found");
?>
<!DOCTYPE html>
<html>
    <head>
        <style>
            @import url(https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,300);

            body {
                background-color: #335B67;
                background: -ms-radial-gradient(ellipse at center,  #335B67 0%, #2C3E50 100%) fixed no-repeat;
                background: -moz-radial-gradient(ellipse at center,  #335B67 0%, #2C3E50 100%) fixed no-repeat;
                background: -o-radial-gradient(ellipse at center, #335B67 0%, #2C3E50 100%) fixed no-repeat;
                background: -webkit-gradient(radial, center center, 0, center center, 497, color-stop(0, #335B67), color-stop(1, #2C3E50));
                background: -webkit-radial-gradient(ellipse at center,  #335B67 0%, #2C3E50 100%) fixed no-repeat;
                background: radial-gradient(ellipse at center, #335B67 0%, #2C3E50 100%) fixed no-repeat;
                font-family: 'Source Sans Pro', sans-serif;
                -webkit-font-smoothing: antialiased;
                margin: 0px;
            }

            ::selection {
                background-color: rgba(0,0,0,0.2);
            }

            ::-moz-selection {
                background-color: rgba(0,0,0,0.2);
            }


            a {
                color: white;
                text-decoration: none;
                border-bottom: 1px solid rgba(255,255,255,0.5);
                transition: all 0.5s ease;
                -moz-transition: all 0.5s ease;
                -o-transition: all 0.5s ease;
                -webkit-transition: all 0.5s ease;
                margin-right: 10px;
            }

            a:last-child { margin-right: 0px; }

            a:hover {
                text-shadow: 0px 0px 1px rgba(255,255,255,.5);
                border-bottom: 1px solid rgba(255,255,255,1);
            }

            #noscript-warning {
                width: 100%;
                text-align: center;
                background-color: rgba(0,0,0,0.2);
                font-weight: 300;
                color: white;
                padding-top: 10px;
                padding-bottom: 10px;
            }



            /* === WRAP === */

            #wrap {
                width: 80%;
                max-width: 1400px;
                margin:0 auto;
                height: auto;
                position: relative;
                margin-top: 8%;
            }



            /* === MAIN TEXT CONTENT === */

            #main-content {
                float: right;
                max-width: 45%;
                color: white;
                font-weight: 300;
                font-size: 18px;
                padding-bottom: 40px;
                line-height: 28px;
            }

            #main-content h1 {
                margin: 0px;
                font-weight: 400;
                font-size: 42px;
                margin-bottom: 40px;
                line-height: normal;
            }



            /* === NAVIGATION BUTTONS === */

            #navigation { margin-top: 2%; }

            #navigation a.navigation {
                display: block;
                float: left;
                background-color: rgba(0,0,0,0.2);
                padding-left: 15px;
                padding-right: 15px;
                color: white;
                height: 41px;
                line-height: 41px;
                text-decoration: none;
                font-size: 16px;
                transition: all 0.5s ease;
                -moz-transition: all 0.5s ease;
                -o-transition: all 0.5s ease;
                -webkit-transition: all 0.5s ease;
                margin-right: 2%;
                margin-bottom: 2%;
                border-bottom: none;
            }

            #navigation a.navigation i { line-height: 41px; }

            #navigation a.navigation:hover {
                background-color: rgba(26,188,156,0.7);
                border-bottom: none;
            }



            /* === WORDSEARCH === */

            #wordsearch {
                width: 45%;
                float: left;
            }

            #wordsearch ul {
                margin: 0px;
                padding: 0px;
            }

            #wordsearch ul li {
                float: left;
                width: 12%;
                background-color: rgba(0,0,0,.2);
                list-style: none;
                margin-right: 0.5%;
                margin-bottom: 0.5%;
                padding: 0;
                display: block;
                text-align: center;
                color: rgba(255,255,255,0.7);
                text-transform: uppercase;
                overflow: hidden;
                font-size: 24px;
                font-size: 1.6vw;
                font-weight: 300;
                transition: background-color 0.75s ease;
                -moz-transition: background-color 0.75s ease;
                -webkit-transition: background-color 0.75s ease;
                -o-transition: background-color 0.75s ease;
            }

            #wordsearch ul li.selected {
                background-color: rgba(26,188,156,0.7);
                color: rgba(255,255,255,1);
                font-weight: 400;
            }



            /* === SEARCH FORM === */

            #search { margin-top: 30px; }

            #search input[type='text'] {
                width: 88%;
                height: 41px;
                padding-left: 15px;
                padding-rigt: 15px;
                box-sizing: border-box;
                -moz-box-sizing: border-box;
                background-color: rgba(0,0,0,0.2);
                border: none;
                outline: none;
                -webkit-appearance: none;
                font-size: 16px;
                font-weight: 300;
                color: white;
                font-family: 'Source Sans Pro', sans-serif;
                transition: all 0.5s ease;
                -moz-transition: all 0.5s ease;
                -o-transition: all 0.5s ease;
                -webkit-transition: all 0.5s ease;
                border-radius: 0px;
            }

            #search .input-search {
                width: 10%;
                float: right;
                height: 41px;
                background-color: rgba(0,0,0,0.2);
                outline: none;
                border: none;
                -webkit-appearance: none;
                font-family: 'Source Sans Pro', sans-serif;
                color: white;
                font-weight: 300;
                font-size: 16px;
                cursor: pointer;
                transition: all 0.5s ease;
                -moz-transition: all 0.5s ease;
                -o-transition: all 0.5s ease;
                -webkit-transition: all 0.5s ease;
                text-align: center;
            }

            #search .input-search:hover {
                background-color: rgba(26,188,156,0.7);
            }



            /* === RESPONSIVE CSS === */

            @media all and (max-width: 899px) {
                #wrap { width: 90%; }
            }

            @media all and (max-width: 799px) {
                #wrap { width: 90%; height: auto; margin-top: 40px; top: 0%; }
                #wordsearch { width: 90%; float: none; margin:0 auto; }
                #wordsearch ul li { font-size: 4vw; }
                #main-content { float: none; width: 90%; max-width: 90%; margin:0 auto; margin-top: 30px; text-align: justify; }
                #main-content h1 { text-align: left; }
                #search input[type='text'] { width: 84%; }
                #search .input-search { width: 15%; }
            }

            @media all and (max-width: 499px) {
                #main-content h1 { font-size: 28px; }
            }
        </style>
        <script
            src="https://code.jquery.com/jquery-3.4.1.min.js"
            integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous"></script>
        <script>
            $(function () {
                var liWidth = $("li").css("width");
                $("li").css("height", liWidth);
                $("li").css("lineHeight", liWidth);
                var totalHeight = $("#wordsearch").css("width");
                $("#wordsearch").css("height", totalHeight);
            });
            causeRepaintsOn = $("h1, h2, h3, p");
            $(window).resize(function () {
                causeRepaintsOn.css("z-index", 1);
            });
            $(window).on('resize', function () {
                var liWidth = $(".one").css("width");
                $("li").css("height", liWidth);
                $("li").css("lineHeight", liWidth);
                var totalHeight = $("#wordsearch").css("width");
                $("#wordsearch").css("height", totalHeight);
            });



            $(function () {
                /* 4 */
                $(this).delay(1500).queue(function () {
                    $(".one").addClass("selected");
                    $(this).dequeue();
                })
                        /* 0 */
                        .delay(500).queue(function () {
                    $(".two").addClass("selected");
                    $(this).dequeue();
                })
                        /* 4 */
                        .delay(500).queue(function () {
                    $(".three").addClass("selected");
                    $(this).dequeue();
                })
                        /* P */
                        .delay(500).queue(function () {
                    $(".four").addClass("selected");
                    $(this).dequeue();
                })
                        /* A */
                        .delay(500).queue(function () {
                    $(".five").addClass("selected");
                    $(this).dequeue();
                })
                        /* G */
                        .delay(500).queue(function () {
                    $(".six").addClass("selected");
                    $(this).dequeue();
                })
                        /* E */
                        .delay(500).queue(function () {
                    $(".seven").addClass("selected");
                    $(this).dequeue();
                })
                        /* N */
                        .delay(500).queue(function () {
                    $(".eight").addClass("selected");
                    $(this).dequeue();
                })
                        /* O */
                        .delay(500).queue(function () {
                    $(".nine").addClass("selected");
                    $(this).dequeue();
                })
                        /* T */
                        .delay(500).queue(function () {
                    $(".ten").addClass("selected");
                    $(this).dequeue();
                })
                        /* F */
                        .delay(500).queue(function () {
                    $(".eleven").addClass("selected");
                    $(this).dequeue();
                })
                        /* O */
                        .delay(500).queue(function () {
                    $(".twelve").addClass("selected");
                    $(this).dequeue();
                })
                        /* U */
                        .delay(500).queue(function () {
                    $(".thirteen").addClass("selected");
                    $(this).dequeue();
                })
                        /* N */
                        .delay(500).queue(function () {
                    $(".fourteen").addClass("selected");
                    $(this).dequeue();
                })
                        /* D */
                        .delay(500).queue(function () {
                    $(".fifteen").addClass("selected");
                    $(this).dequeue()
                });
            });



        </script>
    </head>

    <body>
        <div id="wrap">
            <div id="wordsearch">
                <ul>
                    <li>k</li>

                    <li>v</li>

                    <li>n</li>

                    <li>z</li>

                    <li>i</li>

                    <li>x</li>

                    <li>m</li>

                    <li>e</li>

                    <li>t</li>

                    <li>a</li>

                    <li>x</li>

                    <li>l</li>

                    <li class="one">4</li>

                    <li class="two">0</li>

                    <li class="three">4</li>

                    <li>y</li>

                    <li>y</li>

                    <li>w</li>

                    <li>v</li>

                    <li>b</li>

                    <li>o</li>

                    <li>q</li>

                    <li>d</li>

                    <li>y</li>

                    <li>p</li>

                    <li>a</li>

                    <li class="four">p</li>

                    <li class="five">a</li>

                    <li class="six">g</li>

                    <li class="seven">e</li>

                    <li>v</li>

                    <li>j</li>

                    <li>a</li>

                    <li class="eight">n</li>

                    <li class="nine">o</li>

                    <li class="ten">t</li>

                    <li>s</li>

                    <li>c</li>

                    <li>e</li>

                    <li>w</li>

                    <li>v</li>

                    <li>x</li>

                    <li>e</li>

                    <li>p</li>

                    <li>c</li>

                    <li>f</li>

                    <li>h</li>

                    <li>q</li>

                    <li>e</li>

                    <li class="eleven">f</li>

                    <li class="twelve">o</li>

                    <li class="thirteen">u</li>

                    <li class="fourteen">n</li>

                    <li class="fifteen">d</li>

                    <li>s</li>

                    <li>w</li>

                    <li>q</li>

                    <li>v</li>

                    <li>o</li>

                    <li>s</li>

                    <li>m</li>

                    <li>v</li>

                    <li>f</li>

                    <li>u</li>
                </ul>
            </div>

            <div id="main-content">
                <center><img src="<?php echo $global['webSiteRootURL']; ?>videos/userPhoto/logo.png?1570636830"> </center>
                <h1>We couldn't find what you were looking for.</h1>

                <p>Unfortunately the page you were looking for could not be found. It may be
                    temporarily unavailable, moved or no longer exist.</p>

                <p>Check the URL you entered for any mistakes and try again. Alternatively, search
                    for whatever is missing or take a look around the rest of our site.</p>

                <div id="search">
                    <form action="<?php echo $global['webSiteRootURL']; ?>">
                        <input type="text" placeholder="Search" name="search" />
                    </form>
                </div>

                <div id="navigation">
                    <a class="navigation" href="<?php echo $global['webSiteRootURL']; ?>">Home</a>
                    <a class="navigation" href="<?php echo $global['webSiteRootURL']; ?>about">About Us</a>
                    <a class="navigation" href="<?php echo $global['webSiteRootURL']; ?>sitemap.xml">Site Map</a>
                    <a class="navigation" href="<?php echo $global['webSiteRootURL']; ?>contact">Contact</a>
                </div>
            </div>
        </div>
    </body>
</html> 