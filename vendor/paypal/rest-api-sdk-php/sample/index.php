<!-- Welcome to PayPal REST SDK -- >
<?php
if (PHP_SAPI == 'cli') {
    // If the index.php is called using console, we would try to host
    // the built in PHP Server
    if (version_compare(phpversion(), '5.4.0', '>=') === true) {
        //exec('php -S -t ' . __DIR__ . '/');
        $cmd = "php -S localhost:5000 -t " . __DIR__;
        $descriptors = array(
            0 => array("pipe", "r"),
            1 => STDOUT,
            2 => STDERR,
        );
        $process = proc_open($cmd, $descriptors, $pipes);
        if ($process === false) {
            fprintf(STDERR,
                "Unable to launch PHP's built-in web server.\n");
            exit(2);
        }
        fclose($pipes[0]);
        $exit = proc_close($process);
        exit($exit);
    } else {
        echo "You must be running PHP version less than 5.4. You would have to manually host the website on your local web server.\n";
        exit(2);
    }
} ?>
-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="images/favicon.ico">

    <title>PayPal REST API Samples</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
    <style>
        /* Header Links */
        .header-link {
            position: absolute;
            left: 7px;
            opacity: 0;

        }

        h2:hover .header-link,
        h3:hover .header-link,
        h4:hover .header-link,
        h5:hover .header-link,
        h6:hover .header-link {
            opacity: 1;
        }
        .list-group-item h5 {
            padding-left: 10px;
        }

        /* End Header Links */

        li.list-group-item:hover {
            background-color: #EEE;
        }

        .jumbotron{
            background: #fff url("https://www.paypalobjects.com/webstatic/developer/banners/Braintree_desktop_BG_2X.jpg") no-repeat top right;
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            background-size: cover;
        }
        .jumbotron{
            margin-bottom: 0px;
            padding-bottom: 20px;
        }

        .jumbotron h2, .jumbotron p, h5{
            font-family: Menlo,Monaco,Consolas,"Courier New",monospace;
        }

        .footer-links a {
            font-family: Menlo,Monaco,Consolas,"Courier New",monospace;
        }

        @media (max-width: 992px) {
            .jumbotron {
                background-color: #FFF;
            }

            .logo {
                position: relative;
            }

            #leftNavigation {
                visibility: hidden;
            }
        }

        @media (min-width: 992px) {
            .jumbotron {
                background-color: #FFF;
            }

            .footer-div a {
                text-decoration: none;
            }

            .img-div {
                position: fixed;
                margin-left: 0px;
                padding-left: 0px;
            }

            .logo {
                top: 80px;
            }
        }

        html {
            position: relative;
            min-height: 100%;
        }

        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            /* Margin bottom by footer height */
            margin-bottom: 60px;
        }

        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            min-height: 60px;
            padding-top: 15px;
        }

        .footer-links, .footer-links li {
            display: inline-block;
            font-size: 110%;
            padding-left: 0px;
            padding-right: 0px;
        }

        .footer-links li {
            padding-top: 5px;
            padding-left: 5px;
        }

        .footer-links a {
            color: #428bca;
        ;
        }

        .fixed {
            position: fixed;
        }

        .nav a {
            font-weight: bold;
        }

    </style>
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body data-spy="scroll" data-target="#leftNavigation" data-offset="15" class="scrollspy-example">
<!-- Main component for a primary marketing message or call to action -->
<div class="jumbotron">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 pull-left img-div">
                <img src="https://www.paypalobjects.com/webstatic/developer/logo2_paypal_developer_2x.png" class="logo img-responsive"/>
            </div>
            <div class="col-md-9 pull-right">
                <h2>// REST API Samples</h2>

                <p>These examples are created to experiment with the PayPal-PHP-SDK capabilities. Each examples
                    are
                    designed to demonstrate the default use-cases in each segment.</p>
                <br />
                <div class="footer-div">
                    <ul class="footer-links">
                        <li>
                            <a href="http://paypal.github.io/PayPal-PHP-SDK/" target="_blank"><i
                                    class="fa fa-github"></i>
                                PayPal PHP SDK</a></li>
                        <li>
                            <a href="https://developer.paypal.com/webapps/developer/docs/api/"
                               target="_blank"><i
                                    class="fa fa-book"></i> REST API Reference</a>
                        </li>
                        <li>
                            <a href="https://github.com/paypal/PayPal-PHP-SDK/issues" target="_blank"><i
                                    class="fa fa-exclamation-triangle"></i> Report Issues </a>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 ">
            <div class="row-fluid fixed col-md-3" id="leftNavigation" role="navigation">
                <ul class="nav nav-pills nav-stacked">
                    <li><a href="#payments">Payments</a></li>
                    <li><a href="#payouts">Payouts</a></li>
                    <li><a href="#authorization">Authorization and Capture</a></li>
                    <li><a href="#sale">Sale</a></li>
                    <li><a href="#order">Order</a></li>
                    <li><a href="#billing">Billing Plan & Agreements</a></li>
                    <li><a href="#vault">Vault</a></li>
                    <li><a href="#experience">Payment Experience</a></li>
                    <li><a href="#notifications">Notifications</a></li>
                    <li><a href="#invoice">Invoice</a></li>
                    <li><a href="#identity">Identity (LIPP)</a></li>
                </ul>

            </div>
        </div>
        <div class="col-md-9 samples">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 id="tls" class="panel-title"><a
                            href="https://developer.paypal.com/webapps/developer/docs/api/#payments"
                            target="_blank">TLS CHECK</a></h3>
                </div>
                <!-- List group -->
                <ul class="list-group">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Check if your system supports TLSv1.2<h5></div>
                            <div class="col-md-4">
                                <a href="tls/TlsCheck.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/tls/TlsCheck.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                                <a href="https://github.paypal.com/SDK-R/tls-update/blob/master/readme.md" class="btn btn-default pull-right">Read More <i
                                        class="fa fa-exclamation"></i></a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 id="payments" class="panel-title"><a
                            href="https://developer.paypal.com/webapps/developer/docs/api/#payments"
                            target="_blank">Payments</a></h3>
                </div>
                <!-- List group -->
                <ul class="list-group">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>PayPal Payments - similar to Express Checkout in Classic APIs</small></h5>
                            </div>
                            <div class="col-md-4">
                                <a href="payments/CreatePaymentUsingPayPal.php" class="btn btn-primary pull-left execute">
                                    Try It
                                    <i class="fa fa-play-circle-o"></i></a><a
                                    href="doc/payments/CreatePaymentUsingPayPal.html"
                                    class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <h6>Step II: Execute after Success
                                    <small>(required step after user approval)</small></h6>
                            </div>
                            <div class="col-md-4">
                                <a
                                    href="doc/payments/ExecutePayment.html"
                                    class="btn btn-default pull-right">Part II : Source <i
                                        class="fa fa-file-code-o"></i></a>

                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Payments using credit card information</h5></div>
                            <div class="col-md-4">
                                <a href="payments/CreatePayment.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/payments/CreatePayment.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Payments using saved credit card <small>(using Vault APIs)</small></h5></div>
                            <div class="col-md-4">
                                <a href="payments/CreatePaymentUsingSavedCard.php"
                                   class="btn btn-primary pull-left execute">
                                    Try It <i class="fa fa-play-circle-o"></i></a>
                                <a href="doc/payments/CreatePaymentUsingSavedCard.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8">
                                <h5>Future payments*
                                    <small> (needs Authorization Code from Mobile SDK)</small>
                                </h5>
                            </div>
                            <div class="col-md-4"></div>
                            <div class="col-md-4">
                                <a href="doc/payments/CreateFuturePayment.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>

                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Update payment details</h5></div>
                            <div class="col-md-4">
                                <a href="payments/UpdatePayment.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/payments/UpdatePayment.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Get payment details</h5></div>
                            <div class="col-md-4">
                                <a href="payments/GetPayment.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/payments/GetPayment.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Get payment history</h5></div>
                            <div class="col-md-4">
                                <a href="payments/ListPayments.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/payments/ListPayments.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 id="payouts" class="panel-title"><a
                            href="https://developer.paypal.com/docs/integration/direct/payouts-overview/"
                            target="_blank">Payouts</a></h3>
                </div>
                <!-- List group -->
                <ul class="list-group">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Create Single Payout (Synchronously)</h5></div>
                            <div class="col-md-4">
                                <a href="payouts/CreateSinglePayout.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/payouts/CreateSinglePayout.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Create Batch Payout</h5></div>
                            <div class="col-md-4">
                                <a href="payouts/CreateBatchPayout.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/payouts/CreateBatchPayout.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Get Payout Batch Status</h5></div>
                            <div class="col-md-4">
                                <a href="payouts/GetPayoutBatchStatus.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/payouts/GetPayoutBatchStatus.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Get Payout Item Status</h5></div>
                            <div class="col-md-4">
                                <a href="payouts/GetPayoutItemStatus.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/payouts/GetPayoutItemStatus.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Cancel Uncliamed Payout Item</h5></div>
                            <div class="col-md-4">
                                <a href="payouts/CancelPayoutItem.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/payouts/CancelPayoutItem.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 id="authorization" class="panel-title"><a
                            href="https://developer.paypal.com/webapps/developer/docs/api/#authorizations"
                            target="_blank">Authorization and capture</a></h3>
                </div>
                <!-- List group -->
                <ul class="list-group">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Authorize Payment</h5></div>
                            <div class="col-md-4">
                                <a href="payments/AuthorizePayment.php" class="btn btn-primary pull-left execute"> Try It
                                    <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/payments/AuthorizePayment.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <h6>Step II: Execute after Success
                                    <small>(required step after user approval)</small></h6>
                            </div>
                            <div class="col-md-4">
                                <a
                                    href="doc/payments/ExecutePayment.html"
                                    class="btn btn-default pull-right">Part II : Source <i
                                        class="fa fa-file-code-o"></i></a>

                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Get details of an authorized payment</h5></div>
                            <div class="col-md-4">
                                <a href="payments/GetAuthorization.php" class="btn btn-primary pull-left execute"> Try It
                                    <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/payments/GetAuthorization.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Capture an authorized payment</h5></div>
                            <div class="col-md-4">
                                <a href="payments/AuthorizationCapture.php" class="btn btn-primary pull-left execute">
                                    Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/payments/AuthorizationCapture.html" class="btn btn-default pull-right">Source
                                    <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Get details of a captured payment</h5></div>
                            <div class="col-md-4">
                                <a href="payments/GetCapture.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/payments/GetCapture.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Void an authorized payment</h5></div>
                            <div class="col-md-4">
                                <a href="payments/VoidAuthorization.php" class="btn btn-primary pull-left execute"> Try It
                                    <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/payments/VoidAuthorization.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Reauthorize a payment</h5></div>
                            <div class="col-md-4">
                                <a href="payments/Reauthorization.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/payments/Reauthorization.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Refund captured payment</h5></div>
                            <div class="col-md-4">
                                <a href="payments/RefundCapture.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/payments/RefundCapture.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 id="sale" class="panel-title"><a
                            href="https://developer.paypal.com/webapps/developer/docs/api/#sale-transactions"
                            target="_blank">Sale</a></h3>
                </div>
                <!-- List group -->
                <ul class="list-group">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Get Sale details</h5></div>
                            <div class="col-md-4">
                                <a href="sale/GetSale.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/sale/GetSale.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Refund a Sale</h5></div>
                            <div class="col-md-4">
                                <a href="sale/RefundSale.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/sale/RefundSale.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 id="order" class="panel-title"><a
                            href="https://developer.paypal.com/webapps/developer/docs/api/#orders"
                            target="_blank">Order</a></h3>
                </div>
                <!-- List group -->
                <ul class="list-group">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Create/Retrieve Order Using PayPal</small></h5>
                            </div>
                            <div class="col-md-4">
                                <a href="payments/OrderCreateUsingPayPal.php" class="btn btn-primary pull-left execute">
                                    Try It
                                    <i class="fa fa-play-circle-o"></i></a><a
                                    href="doc/payments/OrderCreateUsingPayPal.html"
                                    class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <h6>Step II: Execute after Success
                                    <small>(required step after user approval)</small></h6>
                            </div>
                            <div class="col-md-4">
                                <a
                                    href="doc/payments/OrderGet.html"
                                    class="btn btn-default pull-right">Part II : Source <i
                                        class="fa fa-file-code-o"></i></a>

                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Authorize Order Using PayPal</small></h5>
                            </div>
                            <div class="col-md-4">
                                <a href="payments/OrderCreateForAuthorization.php" class="btn btn-primary pull-left execute">
                                    Try It
                                    <i class="fa fa-play-circle-o"></i></a><a
                                    href="doc/payments/OrderCreateForAuthorization.html"
                                    class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <h6>Step II: Authorize after Executing Order
                                    <small>(required step after user approval)</small></h6>
                            </div>
                            <div class="col-md-4">
                                <a
                                    href="doc/payments/OrderAuthorize.html"
                                    class="btn btn-default pull-right">Part II : Source <i
                                        class="fa fa-file-code-o"></i></a>

                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Capture Order Using PayPal</small></h5>
                            </div>
                            <div class="col-md-4">
                                <a href="payments/OrderCreateForCapture.php" class="btn btn-primary pull-left execute">
                                    Try It
                                    <i class="fa fa-play-circle-o"></i></a><a
                                    href="doc/payments/OrderCreateForCapture.html"
                                    class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <h6>Step II: Capture after Executing Order
                                    <small>(required step after user approval)</small></h6>
                            </div>
                            <div class="col-md-4">
                                <a
                                    href="doc/payments/OrderCapture.html"
                                    class="btn btn-default pull-right">Part II : Source <i
                                        class="fa fa-file-code-o"></i></a>

                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Void Order Using PayPal</small></h5>
                            </div>
                            <div class="col-md-4">
                                <a href="payments/OrderCreateForVoid.php" class="btn btn-primary pull-left execute">
                                    Try It
                                    <i class="fa fa-play-circle-o"></i></a><a
                                    href="doc/payments/OrderCreateForVoid.html"
                                    class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <h6>Step II: Void after Executing Order
                                    <small>(required step after user approval)</small></h6>
                            </div>
                            <div class="col-md-4">
                                <a
                                    href="doc/payments/OrderDoVoid.html"
                                    class="btn btn-default pull-right">Part II : Source <i
                                        class="fa fa-file-code-o"></i></a>

                            </div>
                        </div>
                    </li>

                </ul>
            </div>

            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 id="billing" class="panel-title"><a
                            href="https://developer.paypal.com/webapps/developer/docs/api/#billing-plans-and-agreements"
                            target="_blank">Billing Plan & Agreements</a></h3>
                </div>
                <!-- List group -->
                <ul class="list-group">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Create Billing Plan</h5></div>
                            <div class="col-md-4">
                                <a href="billing/CreatePlan.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/billing/CreatePlan.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Get Billing Plan</h5></div>
                            <div class="col-md-4">
                                <a href="billing/GetPlan.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/billing/GetPlan.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Update/Activate Plan</h5></div>
                            <div class="col-md-4">
                                <a href="billing/UpdatePlan.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/billing/UpdatePlan.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Update Plan Payment Definitions/Amount</h5></div>
                            <div class="col-md-4">
                                <a href="billing/UpdatePlanPaymentDefinitions.php"
                                   class="btn btn-primary pull-left execute">
                                    Try It <i class="fa fa-play-circle-o"></i></a>
                                <a href="doc/billing/UpdatePlanPaymentDefinitions.html" class="btn btn-default pull-right">Source
                                    <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Delete Billing Plan</h5></div>
                            <div class="col-md-4">
                                <a href="billing/DeletePlan.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/billing/DeletePlan.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>List Billing Plans</h5></div>
                            <div class="col-md-4">
                                <a href="billing/ListPlans.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/billing/ListPlans.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Create Billing Agreement With Credit Card</h5></div>
                            <div class="col-md-4">
                                <a href="billing/CreateBillingAgreementWithCreditCard.php"
                                   class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/billing/CreateBillingAgreementWithCreditCard.html"
                                   class="btn btn-default pull-right">Source
                                    <i class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Create Billing Agreement With PayPal</h5></div>
                            <div class="col-md-4">
                                <a href="billing/CreateBillingAgreementWithPayPal.php"
                                   class="btn btn-primary pull-left execute">
                                    Try It <i class="fa fa-play-circle-o"></i></a>
                                <a href="doc/billing/CreateBillingAgreementWithPayPal.html"
                                   class="btn btn-default pull-right">Source
                                    <i class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <h6>Step II: Execute after Success
                                    <small>(required step after user approval)</small></h6>
                            </div>
                            <div class="col-md-4">
                                <a
                                    href="doc/billing/ExecuteAgreement.html"
                                    class="btn btn-default pull-right">Part II : Source <i
                                        class="fa fa-file-code-o"></i></a>

                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Get Billing Agreement</h5></div>
                            <div class="col-md-4">
                                <a href="billing/GetBillingAgreement.php" class="btn btn-primary pull-left execute"> Try It
                                    <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/billing/GetBillingAgreement.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Search for Transactions</h5></div>
                            <div class="col-md-4">
                                <a href="billing/SearchBillingTransactions.php" class="btn btn-primary pull-left execute"> Try It
                                    <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/billing/SearchBillingTransactions.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Update Billing Agreement</h5></div>
                            <div class="col-md-4">
                                <a href="billing/UpdateBillingAgreement.php" class="btn btn-primary pull-left execute">
                                    Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/billing/UpdateBillingAgreement.html" class="btn btn-default pull-right">Source
                                    <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Suspend Billing Agreement</h5></div>
                            <div class="col-md-4">
                                <a href="billing/SuspendBillingAgreement.php" class="btn btn-primary pull-left execute">
                                    Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/billing/SuspendBillingAgreement.html" class="btn btn-default pull-right">Source
                                    <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Reactivate Billing Agreement</h5></div>
                            <div class="col-md-4">
                                <a href="billing/ReactivateBillingAgreement.php" class="btn btn-primary pull-left execute">
                                    Try It
                                    <i class="fa fa-play-circle-o"></i></a>
                                <a href="doc/billing/ReactivateBillingAgreement.html" class="btn btn-default pull-right">Source
                                    <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 id="vault" class="panel-title"><a href="https://developer.paypal.com/webapps/developer/docs/api/#vault"
                                                          target="_blank">Vault</a></h3>
                </div>
                <!-- List group -->
                <ul class="list-group">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Credit Card - Save</h5></div>
                            <div class="col-md-4">
                                <a href="vault/CreateCreditCard.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/vault/CreateCreditCard.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Credit Card - Retrieve</h5></div>
                            <div class="col-md-4">
                                <a href="vault/GetCreditCard.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/vault/GetCreditCard.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Credit Card - List All</h5></div>
                            <div class="col-md-4">
                                <a href="vault/ListCreditCards.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/vault/ListCreditCards.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Credit Card - Update</h5></div>
                            <div class="col-md-4">
                                <a href="vault/UpdateCreditCard.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/vault/UpdateCreditCard.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Credit Card - Delete</h5></div>
                            <div class="col-md-4">
                                <a href="vault/DeleteCreditCard.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/vault/DeleteCreditCard.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Bank Account - Save</h5></div>
                            <div class="col-md-4">
                                <a href="vault/CreateBankAccount.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/vault/CreateBankAccount.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Bank Account - Retrieve</h5></div>
                            <div class="col-md-4">
                                <a href="vault/GetBankAccount.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/vault/GetBankAccount.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Bank Account - Delete</h5></div>
                            <div class="col-md-4">
                                <a href="vault/DeleteBankAccount.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/vault/DeleteBankAccount.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 id="experience" class="panel-title"><a
                            href="https://developer.paypal.com/webapps/developer/docs/api/#payment-experience"
                            target="_blank">Payment Experience</a></h3>
                </div>
                <!-- List group -->
                <ul class="list-group">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Create a web experience profile</h5></div>
                            <div class="col-md-4">
                                <a href="payment-experience/CreateWebProfile.php" class="btn btn-primary pull-left execute">
                                    Try It
                                    <i class="fa fa-play-circle-o"></i></a>
                                <a href="doc/payment-experience/CreateWebProfile.html" class="btn btn-default pull-right">Source
                                    <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Retrieve a web experience profile</h5></div>
                            <div class="col-md-4">
                                <a href="payment-experience/GetWebProfile.php" class="btn btn-primary pull-left execute">
                                    Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/payment-experience/GetWebProfile.html" class="btn btn-default pull-right">Source
                                    <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>List web experience profiles</h5></div>
                            <div class="col-md-4">
                                <a href="payment-experience/ListWebProfiles.php" class="btn btn-primary pull-left execute">
                                    Try It
                                    <i class="fa fa-play-circle-o"></i></a>
                                <a href="doc/payment-experience/ListWebProfiles.html" class="btn btn-default pull-right">Source
                                    <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Update a web experience profile</h5></div>
                            <div class="col-md-4">
                                <a href="payment-experience/UpdateWebProfile.php" class="btn btn-primary pull-left execute">
                                    Try It
                                    <i class="fa fa-play-circle-o"></i></a>
                                <a href="doc/payment-experience/UpdateWebProfile.html" class="btn btn-default pull-right">Source
                                    <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Partially update a web experience profile</h5></div>
                            <div class="col-md-4">
                                <a href="payment-experience/PartiallyUpdateWebProfile.php"
                                   class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/payment-experience/PartiallyUpdateWebProfile.html"
                                   class="btn btn-default pull-right">Source
                                    <i class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Delete a web experience profile</h5></div>
                            <div class="col-md-4">
                                <a href="payment-experience/DeleteWebProfile.php" class="btn btn-primary pull-left execute">
                                    Try It
                                    <i class="fa fa-play-circle-o"></i></a>
                                <a href="doc/payment-experience/DeleteWebProfile.html" class="btn btn-default pull-right">Source
                                    <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 id="notifications" class="panel-title"><a
                            href="https://developer.paypal.com/webapps/developer/docs/api/#notifications"
                            target="_blank">Notifications</a></h3>
                </div>
                <!-- List group -->
                <ul class="list-group">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Get a reference list of webhook event types</h5></div>
                            <div class="col-md-4">
                                <a href="notifications/WebhookEventTypesList.php" class="btn btn-primary pull-left execute"> Try It
                                    <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/notifications/WebhookEventTypesList.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Create a webhook</h5></div>
                            <div class="col-md-4">
                                <a href="notifications/CreateWebhook.php" class="btn btn-primary pull-left execute"> Try It
                                    <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/notifications/CreateWebhook.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Get a webhook</h5></div>
                            <div class="col-md-4">
                                <a href="notifications/GetWebhook.php" class="btn btn-primary pull-left execute"> Try It
                                    <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/notifications/GetWebhook.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>List subscribed webhook event types</h5></div>
                            <div class="col-md-4">
                                <a href="notifications/ListSubscribedWebhookEventTypes.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/notifications/ListSubscribedWebhookEventTypes.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>List all webhooks</h5></div>
                            <div class="col-md-4">
                                <a href="notifications/ListWebhooks.php" class="btn btn-primary pull-left execute">
                                    Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/notifications/ListWebhooks.html" class="btn btn-default pull-right">Source
                                    <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Update a webhook</h5></div>
                            <div class="col-md-4">
                                <a href="notifications/UpdateWebhook.php" class="btn btn-primary pull-left execute"> Try It
                                    <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/notifications/UpdateWebhook.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Delete a webhook</h5></div>
                            <div class="col-md-4">
                                <a href="notifications/DeleteWebhook.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/notifications/DeleteWebhook.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Delete all webhooks</h5></div>
                            <div class="col-md-4">
                                <a href="notifications/DeleteAllWebhooks.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/notifications/DeleteAllWebhooks.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Search webhook events</h5></div>
                            <div class="col-md-4">
                                <a href="notifications/SearchWebhookEvents.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/notifications/SearchWebhookEvents.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 id="invoice" class="panel-title"><a
                            href="https://developer.paypal.com/webapps/developer/docs/api/#invoicing"
                            target="_blank">Invoice</a></h3>
                </div>
                <!-- List group -->
                <ul class="list-group">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Create an Invoice</h5></div>
                            <div class="col-md-4">
                                <a href="invoice/CreateInvoice.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/invoice/CreateInvoice.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Send an Invoice</h5></div>
                            <div class="col-md-4">
                                <a href="invoice/SendInvoice.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/invoice/SendInvoice.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Update an Invoice</h5></div>
                            <div class="col-md-4">
                                <a href="invoice/UpdateInvoice.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/invoice/UpdateInvoice.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Retrieve an Invoice</h5></div>
                            <div class="col-md-4">
                                <a href="invoice/GetInvoice.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/invoice/GetInvoice.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Get Invoices of a Merchant</h5></div>
                            <div class="col-md-4">
                                <a href="invoice/ListInvoice.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/invoice/ListInvoice.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Search for Invoices</h5></div>
                            <div class="col-md-4">
                                <a href="invoice/SearchInvoices.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/invoice/SearchInvoices.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Send an Invoice Reminder</h5></div>
                            <div class="col-md-4">
                                <a href="invoice/RemindInvoice.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/invoice/RemindInvoice.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Cancel an Invoice</h5></div>
                            <div class="col-md-4">
                                <a href="invoice/CancelInvoice.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/invoice/CancelInvoice.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Delete an Invoice</h5></div>
                            <div class="col-md-4">
                                <a href="invoice/DeleteInvoice.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/invoice/DeleteInvoice.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Retrieve a QR Code</h5></div>
                            <div class="col-md-4">
                                <a href="invoice/RetrieveQRCode.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/invoice/RetrieveQRCode.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Record a Payment</h5></div>
                            <div class="col-md-4">
                                <a href="invoice/RecordPayment.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/invoice/RecordPayment.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Record a Refund</h5></div>
                            <div class="col-md-4">
                                <a href="invoice/RecordRefund.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/invoice/RecordRefund.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 id="identity" class="panel-title"><a
                            href="https://developer.paypal.com/webapps/developer/docs/api/#identity"
                            target="_blank">Identity (LIPP)</a></h3>
                </div>
                <!-- List group -->
                <ul class="list-group">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Obtain User's Consent</h5></div>
                            <div class="col-md-4">
                                <a href="lipp/ObtainUserConsent.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/lipp/ObtainUserConsent.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>User Consent Redirect</h5></div>
                            <div class="col-md-4">
                                <a href="doc/lipp/UserConsentRedirect.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Get User Info</h5></div>
                            <div class="col-md-4">
                                <a href="lipp/GetUserInfo.php" class="btn btn-primary pull-left execute"> Try It <i
                                        class="fa fa-play-circle-o"></i></a>
                                <a href="doc/lipp/GetUserInfo.html" class="btn btn-default pull-right">Source <i
                                        class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-8"><h5>Obtain Access Token From Refresh Token</h5></div>
                            <div class="col-md-4">
                                <a href="lipp/GenerateAccessTokenFromRefreshToken.php"
                                   class="btn btn-primary pull-left execute">
                                    Try It <i class="fa fa-play-circle-o"></i></a>
                                <a href="doc/lipp/GenerateAccessTokenFromRefreshToken.html"
                                   class="btn btn-default pull-right">Source
                                    <i class="fa fa-file-code-o"></i></a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- /container -->
<hr />
<footer class="footer">
    <div class="container">
        <div class="footer-div">
            <ul class="footer-links">
                <li>
                    <a href="http://paypal.github.io/PayPal-PHP-SDK/" target="_blank"><i
                            class="fa fa-github"></i>
                        PayPal PHP SDK</a></li>
                <li>
                    <a href="https://developer.paypal.com/webapps/developer/docs/api/" target="_blank"><i
                            class="fa fa-book"></i> REST API Reference</a>
                </li>
                <li>
                    <a href="https://github.com/paypal/PayPal-PHP-SDK/issues" target="_blank"><i
                            class="fa fa-exclamation-triangle"></i> Report Issues </a>
                </li>

            </ul>
        </div>
    </div>
</footer>
<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.2.0/js/scrollspy.min.js"></script>
<script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/md5.js"></script>

<script>
    $(document).ready(function () {
        if (window.location.href.indexOf("paypal.github.io") >= 0) {
            $(".execute").hide();
        }
    });
    $(function() {
        return $(".samples h5, h6").each(function(i, el) {
            var $el, icon, id;
            $el = $(el);
            id = CryptoJS.MD5(($el.html())).toString();
            //id = $el.attr('id');
            icon = '<i class="fa fa-link"></i>';
            if (id) {
                $el.parent().parent().parent().attr('id', id);
                return $el.prepend($("<a />").addClass("header-link").attr('title', "Anchor Link for this Sample").attr("href", "#" + id).html(icon));
            }
        });
    });
</script>
</body>
</html>
