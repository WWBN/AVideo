<div class="row">
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo __('Monetize User') ?> <div class="pull-right"><?php echo getPluginSwitch('MonetizeUsers'); ?></div></div>
            <div class="panel-body">
                <?php
                $filter = array(
                    'rewardPerView' => 'How much will your users receive per each video view',
                    'rewardOnlyLoggedUsersView' => 'Check this to allow only count rewards from logged users');
                
                createTable("MonetizeUsers",$filter);
                ?>

            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo __('Wallet') ?> <div class="pull-right"><?php echo getPluginSwitch('YPTWallet'); ?></div></div>
            <div class="panel-body">
                <?php
                $filter = array(
                    'decimalPrecision' => 'Usually for USD we use 2, for cryptocurrencies we use more the 2',
                    'currency' => 'Australian Dollar = AUD, Brazilian Real = BRL, Canadian Dollar = CAD, Euro = EUR, U.S. Dollar = USD, etc',
                    'currency_symbol' => '$, R$, etc, the format will be  {currency} {value} {currency_symbol} for example ($ 10.00 USD) or (R$ 10.00 BRL)',
                    'manualAddFundsTransferFromUserId' => 'When some one buy something on your web site, the wallet balance will be transferred to this user ID',
                    'enablePlugin_YPTWalletPayPal' => 'You need to enable it to be able to use PayPal to add funds on your wallet',
                    'enableManualWithdrawFundsPage' => 'Let users request withdraws from his wallet. the withdraw mus be done manually');
                createTable("YPTWallet", $filter);
                ?>
            </div>
        </div>
    </div>
</div>