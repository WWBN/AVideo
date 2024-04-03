<?php
require_once '../../videos/configuration.php';
if (!User::isAdmin()) {
    forbiddenPage('Not admin');
}

$global['paymentsTest'] = 1;
$myWallet = AVideoPlugin::loadPlugin('YPTWallet');
$objWallet = $myWallet->getDataObject();
$myBalance = $myWallet->getBalance(User::getId());
$planTitle = 'Test payment ' . date('Y-m-d h:i:s');
$_GET['plans_id'] = -1;

$_page = new Page(array('Payment tests'));
?>
<div class="container-fluid">

    <div class="panel panel-default" id="plans">
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-4">
                    <input type="number" class="form-control" id="value<?php echo $_GET['plans_id']; ?>" value="<?php echo YPTWallet::formatFloat(1); ?>">

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Subscription</h3>
                        </div>
                        <div class="panel-body">
                            <?php
                            $myWallet->getAvailableRecurrentPayments();
                            ?>
                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">One time payment</h3>
                        </div>
                        <div class="panel-body">
                            <?php
                            $myWallet->getAvailablePayments();
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Test Card Numbers for Stripe</h3>
                        </div>
                        <div class="panel-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Card Number</th>
                                        <th>Brand</th>
                                        <th>Response</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>4242 4242 4242 4242</td>
                                        <td>Visa</td>
                                        <td>Success</td>
                                    </tr>
                                    <tr>
                                        <td>5555 5555 5555 4444</td>
                                        <td>Mastercard</td>
                                        <td>Success</td>
                                    </tr>
                                    <tr>
                                        <td>4000 0025 0000 3155</td>
                                        <td>Visa (debit)</td>
                                        <td>Success</td>
                                    </tr>
                                    <tr>
                                        <td>4000 0000 0000 9995</td>
                                        <td>Visa (declined)</td>
                                        <td>Card declined</td>
                                    </tr>
                                    <tr>
                                        <td>4000 0000 0000 0127</td>
                                        <td>Visa (processing error)</td>
                                        <td>Processing error</td>
                                    </tr>
                                    <tr>
                                        <td>4000 0000 0000 0069</td>
                                        <td>Visa (fraudulent)</td>
                                        <td>Card declined</td>
                                    </tr>
                                    <tr>
                                        <td>4000 0027 6000 3184</td>
                                        <td>Visa</td>
                                        <td>3D Secure authentication required</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-sm-4">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Test Card Numbers for PayPal</h3>
                        </div>
                        <div class="panel-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Card Number</th>
                                        <th>Brand</th>
                                        <th>Response</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>4111 1111 1111 1111</td>
                                        <td>Visa</td>
                                        <td>Success</td>
                                    </tr>
                                    <tr>
                                        <td>5555 5555 5555 4444</td>
                                        <td>Mastercard</td>
                                        <td>Success</td>
                                    </tr>
                                    <tr>
                                        <td>4022 0028 8000 6242</td>
                                        <td>Visa (debit)</td>
                                        <td>Success</td>
                                    </tr>
                                    <tr>
                                        <td>4000 0000 0000 0002</td>
                                        <td>Visa (declined)</td>
                                        <td>Card declined</td>
                                    </tr>
                                    <tr>
                                        <td>4000 0000 0000 0127</td>
                                        <td>Visa (processing error)</td>
                                        <td>Processor declined</td>
                                    </tr>
                                    <tr>
                                        <td>4000 0000 0000 0069</td>
                                        <td>Visa (fraudulent)</td>
                                        <td>Card declined</td>
                                    </tr>
                                    <tr>
                                        <td>4000 0027 6000 3184</td>
                                        <td>Visa</td>
                                        <td>3D Secure authentication required</td>
                                    </tr>
                                </tbody>
                            </table>


                        </div>
                    </div>

                </div>
                <div class="col-sm-12"><strong>Note:</strong>
                    These are test credit card numbers and should only be used in a test environment and should not be used for real transactions.
                    CSC can be any 3-digit number and expiration date can be any future date.</div>

            </div>
        </div>
    </div>
</div>
<?php
$_page->print();
?>