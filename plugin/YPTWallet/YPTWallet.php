<?php

global $global;
if (empty($global)) {
    $global = [];
}
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/YPTWallet/Objects/Wallet.php';
require_once $global['systemRootPath'] . 'plugin/YPTWallet/Objects/Wallet_log.php';
require_once $global['systemRootPath'] . 'objects/autoload.php';

class YPTWallet extends PluginAbstract
{
    const MANUAL_WITHDRAW = "Manual Withdraw Funds";
    const MANUAL_ADD = "Manual Add Funds";
    const PERMISSION_CAN_SEE_WALLET = 0;

    public function getTags()
    {
        return array(
            PluginTags::$MONETIZATION,
            PluginTags::$NETFLIX,
            PluginTags::$FREE,
        );
    }
    public function getDescription()
    {
        $txt = "Wallet for AVideo";
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/YPTWallet-Usage' target='_blank'><i class='fas fa-question-circle'></i> Help</a></small>";

        return $txt . $help;
    }

    public function getName()
    {
        return "YPTWallet";
    }

    public function getUUID()
    {
        return "2faf2eeb-88ac-48e1-a098-37e76ae3e9f3";
    }

    public function getPluginVersion()
    {
        return "6.0";
    }


    public static function getDataObjectAdvanced()
    {
        return array(
            'decimalPrecision',
            'wallet_button_title',
            'add_funds_text',
            'add_funds_success_success',
            'add_funds_success_cancel',
            'add_funds_success_fail',
            'transfer_funds_text',
            'transfer_funds_success_success',
            'transfer_funds_success_fail',
            'withdraw_funds_text',
            'withdraw_funds_success_success',
            'withdraw_funds_success_fail',
            'virtual_currency',
            'virtual_currency_symbol',
            'virtual_currency_exchange_rate',
            'virtual_currency_decimalPrecision',
            'virtual_currency_enable',
            'showWalletOnlyToAdmin',
            'showWalletOnProfile',
            'showWalletOnTopMenu',
            'CryptoWalletName',
            'CryptoWalletEnabled',
            'hideConfiguration',
            'manualAddFundsMenuTitle',
            'manualAddFundsPageButton',
            'manualAddFundsNotifyEmail',
            'manualAddFundsMenuTitle',
            'manualWithdrawFundsMenuTitle',
            'manualWithdrawFundsPageButton',
            'manualWithdrawFundsNotifyEmail',
            'enableAutomaticAddFundsPage',
            'enableManualAddFundsPage',
            'enableManualWithdrawFundsPage',
            'enableAutoWithdrawFundsPagePaypal',
            'manualWithdrawFundsTransferToUserId',
            'manualAddFundsTransferFromUserId'
        );
    }

    public static function getDataObjectDeprecated()
    {
        return array(
            'RedirectURL',
            'CancelURL',
        );
    }


    public static function getDataObjectExperimental()
    {
        return array(
            'enablePlugin_YPTWalletRazorPay',
            'enablePlugin_YPTWalletBlockonomics'
        );
    }

    public function getEmptyDataObject()
    {
        $obj = new stdClass();
        $obj->decimalPrecision = 2;
        $obj->wallet_button_title = "My Wallet";
        $obj->add_funds_text = "<h1>Adding money instantly from credit/debit card</h1>Add funds on your Account Balance, to support our videos";
        $obj->add_funds_success_success = "<h1>Thank you,<br> Your funds has been added<h1>";
        $obj->add_funds_success_cancel = "<h1>Ops,<br> You have cancel it<h1>";
        $obj->add_funds_success_fail = "<h1>Sorry,<br> Your funds request has been fail<h1>";
        $obj->transfer_funds_text = "<h1>Transfer money for other users</h1>Transfer funds from your account to another user account";
        $obj->transfer_funds_success_success = "<h1>Thank you,<br> Your funds has been transfered<h1>";
        $obj->transfer_funds_success_fail = "<h1>Sorry,<br> Your funds transfer request has been fail<h1>";
        $obj->withdraw_funds_text = "Please initiate a withdrawal from your account by entering the destination account details in the provided information text area.";
        $obj->withdraw_funds_success_success = "<h1>Thank you,<br> Your request was submited<h1>";
        $obj->withdraw_funds_success_fail = "<h1>Sorry,<br> Your funds withdraw request has been fail<h1>";
        $obj->virtual_currency = "HEART";  // we will show this currency on the wallet but we will not make transactions on the payment gateway with it
        $obj->virtual_currency_symbol = "❤";  // we will show this currency on the wallet but we will not make transactions on the payment gateway with it
        $obj->virtual_currency_exchange_rate = "2"; // means 1 real currency will be 2 virtual currencies
        $obj->virtual_currency_decimalPrecision = 2.5; // the value 2.5 on it means if you purchase 2 real currency it will worth 5 virtual currencies
        $obj->virtual_currency_enable = false;
        $obj->currency = "USD";
        $obj->currency_symbol = "$";
        $obj->addFundsOptions = "[5,10,20,50]";
        $obj->showWalletOnlyToAdmin = false;
        self::addDataObjectHelper('showWalletOnlyToAdmin', 'Show Wallet to Admin and selected user groups', 'If you check this you will need to specify what user group will be able to see the Wallet');

        $obj->showWalletOnProfile = true;
        $obj->showWalletOnTopMenu = true;
        $obj->CryptoWalletName = "Bitcoin Wallet Address";
        $obj->CryptoWalletEnabled = false;
        $obj->hideTransferFunds = false;
        $obj->hideConfiguration = false;
        $obj->enableAutomaticAddFundsPage = true;
        // add funds
        $obj->enableManualAddFundsPage = false;
        $obj->manualAddFundsMenuTitle = "Add Funds/Deposit";
        $obj->manualAddFundsPageButton = "Notify Deposit Made";
        $obj->manualAddFundsNotifyEmail = "yourEmail@yourDomain.com";
        $obj->manualAddFundsTransferFromUserId = 1;
        // sell funds
        $obj->enableManualWithdrawFundsPage = true;
        $obj->enableAutoWithdrawFundsPagePaypal = false;
        $obj->withdrawFundsOptions = "[5,10,20,50,100,1000]";

        $o = new stdClass();
        $o->type = array();
        for ($i = 0; $i < 100; $i++) {
            $o->type[$i] = "{$i}%";
        }
        $o->value = 10;
        $obj->withdrawFundsSiteCutPercentage = $o;
        self::addDataObjectHelper('withdrawFundsSiteCutPercentage', 'Withdraw Funds Site Cut Percentage', 'This percentage helps cover transaction fees charged by payment gateways when users load their wallets or make withdrawals.');

        $obj->manualWithdrawFundsMenuTitle = "Withdraw Funds";
        $obj->manualWithdrawFundsPageButton = "Request Withdraw";
        $obj->manualWithdrawFundsNotifyEmail = "yourEmail@yourDomain.com";

        //$obj->manualWithdrawFundsminimum = 1;
        //$obj->manualWithdrawFundsmaximum = 100;

        $obj->manualWithdrawFundsTransferToUserId = 1;

        $plugins = self::getAvailablePlugins();
        foreach ($plugins as $value) {
            $eval = "\$obj->enablePlugin_{$value} = false;";
            eval($eval);
            $dataObj = self::getPluginDataObject($value);
            $obj = (object) array_merge((array) $obj, (array) $dataObj);
        }

        return $obj;
    }

    public function getBalance($users_id)
    {
        $wallet = self::getWallet($users_id);
        return $wallet->getBalance();
    }

    public function getBalanceText($users_id)
    {
        $balance = $this->getBalanceFormated($users_id);
        return self::formatCurrency($balance);
    }

    public function getBalanceFormated($users_id)
    {
        $balance = $this->getBalance($users_id);
        $obj = $this->getDataObject();
        return number_format($balance, $obj->decimalPrecision);
    }

    public static function formatCurrency($value, $addHTML = false, $doNotUseVirtualCurrency = false, $currency = false)
    {
        $value = floatval($value);
        $obj = AVideoPlugin::getObjectData('YPTWallet');
        $currency_symbol = $obj->currency_symbol;
        $decimalPrecision = $obj->decimalPrecision;
        if ($currency === false) {
            $currency = $obj->currency;
        }
        if (empty($doNotUseVirtualCurrency) && $obj->virtual_currency_enable) {
            $currency_symbol = $obj->virtual_currency_symbol;
            $decimalPrecision = $obj->virtual_currency_decimalPrecision;
            $currency = $obj->virtual_currency;
        }
        $value = number_format($value, $decimalPrecision);

        if ($addHTML) {
            return "{$currency_symbol}<span class=\"walletBalance\">{$value}</span> {$currency}";
        } else {
            return "{$currency_symbol}{$value} {$currency}";
        }
    }

    public static function getStep($doNotUseVirtualCurrency = false)
    {
        $obj = AVideoPlugin::getObjectData('YPTWallet');
        $decimalPrecision = $obj->decimalPrecision;
        if ($obj->virtual_currency_enable) {
            $decimalPrecision = $obj->virtual_currency_decimalPrecision;
        }
        if (empty($decimalPrecision)) {
            return 1;
        }
        return "0." . str_repeat("0", $decimalPrecision - 1) . "1";
    }

    public static function formatFloat($value)
    {
        $value = floatval($value);
        $obj = AVideoPlugin::getObjectData('YPTWallet');
        return number_format($value, $obj->decimalPrecision);
    }

    public static function getWallet($users_id)
    {
        $wallet = new Wallet(0);
        $wallet->setUsers_id($users_id);
        return $wallet;
    }

    public function getOrCreateWallet($users_id)
    {
        $wallet = new Wallet(0);
        $wallet->setUsers_id($users_id);
        if (empty($wallet->getId())) {
            $wallet_id = $wallet->save();
            $wallet = new Wallet($wallet_id);
        }
        return $wallet;
    }

    public function getAllUsers($activeOnly = true)
    {
        global $global;
        if (empty($global)) {
            $global = [];
        }
        $sql = "SELECT w.*, u.*, u.id as user_id, IFNULL(balance, 0) as balance FROM users u "
            . " LEFT JOIN wallet w ON u.id = w.users_id WHERE 1=1 ";

        if ($activeOnly) {
            $sql .= " AND status = 'a' ";
        }

        $sql .= BootGrid::getSqlFromPost(array('name', 'email', 'user'));

        /**
         * Global variables.
         *
         * @var array $global An array of global variables.
         * @property \mysqli $global['mysqli'] A MySQLi connection object.
         * @property mixed $global[] Dynamically loaded variables.
         */
        $res = $global['mysqli']->query($sql);
        $user = array();

        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $row = cleanUpRowFromDatabase($row);
                $row['name'] = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F]/u', '', $row['name']);
                $row['identification'] = User::getNameIdentificationById($row['user_id']);
                $row['identification'] = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F]/u', '', $row['identification']);
                unset($row['about']);
                $row['background'] = User::getBackground($row['user_id']);
                $row['photo'] = User::getPhoto($row['user_id']);
                $row['crypto_wallet_address'] = "";
                $user[] = $row;
            }
            //$user = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            $user = false;
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $user;
    }

    public static function getTotalBalance()
    {
        global $global;
        if (empty($global)) {
            $global = [];
        }
        $sql = "SELECT sum(balance) as total FROM wallet ";

        /**
         * Global variables.
         *
         * @var array $global An array of global variables.
         * @property \mysqli $global['mysqli'] A MySQLi connection object.
         * @property mixed $global[] Dynamically loaded variables.
         */
        $res = $global['mysqli']->query($sql);
        $user = array();

        if ($res) {
            if ($row = $res->fetch_assoc()) {
                return $row['total'];
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return 0;
    }

    public static function getTotalBalanceText()
    {
        $value = self::getTotalBalance();
        return self::formatCurrency($value);
    }

    public function getHistory($user_id)
    {
        $wallet = self::getWallet($user_id);
        $log = new WalletLog(0);
        $rows = $log->getAllFromWallet($wallet->getId());
        return $rows;
    }

    public static function exchange($value)
    {
        $obj = AVideoPlugin::getObjectData('YPTWallet');
        $value = floatval($value);
        $virtual_currency_exchange_rate = floatval($obj->virtual_currency_exchange_rate);
        if (!empty($virtual_currency_exchange_rate)) {
            $value *= $virtual_currency_exchange_rate;
        }
        return $value;
    }

    /**
     *
     * @param string $users_id
     * @param string $value
     * @param string $description
     * @param string $json_data
     * @param string $mainWallet_user_id A user ID where the money comes from and where the money goes for
     */
    public function addBalance($users_id, $value, $description = "", $json_data = "{}", $mainWallet_user_id = 0, $noNotExchangeValue = false)
    {
        global $global;
        $obj = $this->getDataObject();
        if (empty($noNotExchangeValue) && !empty($obj->virtual_currency_enable)) {
            $originalValue = $value;
            $value = self::exchange($value);

            $originalValueFormated = self::formatCurrency($originalValue, false, true);
            $valueFormated = self::formatCurrency($value);
            $description .= " Rate Exchanged {$originalValueFormated} => {$valueFormated} ";
        }
        $wallet = $this->getOrCreateWallet($users_id);
        $balance = $wallet->getBalance();
        _error_log("YPTWallet::addBalance BEFORE (user_id={$users_id}) (balance={$balance})");
        $newBalance = $balance + $value;
        $wallet->setBalance($newBalance);
        $wallet_id = $wallet->save();

        WalletLog::addLog($wallet_id, $value, $balance, $description, $json_data, "success", "addBalance");

        if (!empty($mainWallet_user_id)) {
            $wallet = $this->getOrCreateWallet($mainWallet_user_id);
            $balance = $wallet->getBalance();
            $newBalance = $balance + ($value * -1);
            $wallet->setBalance($newBalance);
            $wallet_id = $wallet->save();
            $user = new User($users_id);
            WalletLog::addLog($wallet_id, ($value * -1), $balance, " From user ($users_id) " . $user->getUser() . " - " . $description, $json_data, "success", "addBalance to main wallet");
        }

        $wallet = $this->getOrCreateWallet($users_id);
        $balance = $wallet->getBalance();
        _error_log("YPTWallet::addBalance AFTER (user_id={$users_id}) (balance={$balance})");
        //_error_log("YPTWallet::addBalance $wallet_id, $value, $description, $json_data");
    }

    public function saveBalance($users_id, $value)
    {
        if (!User::isAdmin()) {
            return false;
        }
        $wallet = self::getWallet($users_id);
        $balance = $wallet->getBalance();
        $wallet->setBalance($value);
        $wallet_id = $wallet->save();
        $description = "Admin set your balance, from {$balance} to {$value}";
        _error_log("saveBalance($users_id, $value) " . json_encode(debug_backtrace()));
        WalletLog::addLog($wallet_id, $value, $balance, $description, "{}", "success", "saveBalance");
    }

    public static function transferBalanceToSiteOwner($users_id_from, $value, $description = "", $forceTransfer = false)
    {
        $obj = AVideoPlugin::getObjectData('YPTWallet');
        if (empty($obj->manualWithdrawFundsTransferToUserId)) {
            _error_log("YPTWallet::transferBalanceToSiteOwner site owner is not defined in the plugin, define it on the option manualWithdrawFundsTransferToUserId", AVideoLog::$ERROR);
        }
        return self::transferBalance($users_id_from, $obj->manualWithdrawFundsTransferToUserId, $value, $description, $forceTransfer);
    }

    public static function transferBalanceFromSiteOwner($users_id_from, $value, $description = "", $forceTransfer = false)
    {
        $obj = AVideoPlugin::getObjectData('YPTWallet');
        return self::transferBalance($obj->manualWithdrawFundsTransferToUserId, $users_id_from, $value, $description, $forceTransfer);
    }

    public static function transferBalanceFromMeToSiteOwner($value)
    {
        if (!User::isLogged()) {
            return false;
        }
        return self::transferBalanceToSiteOwner(User::getId(), $value);
    }

    public static function transferBalanceFromOwnerToMe($value)
    {
        if (!User::isLogged()) {
            return false;
        }
        return self::transferBalanceFromSiteOwner(User::getId(), $value);
    }

    public static function transferBalance($fromUserId, $toUserId, $amount, $customDescription = "", $forceTransfer = false)
    {
        global $global;
        _error_log("transferBalance: $fromUserId, $toUserId, $amount, $customDescription, $forceTransfer");

        if (!User::isAdmin()) {
            if ($fromUserId != User::getId() && !$forceTransfer) {
                _error_log("transferBalance: not admin, $fromUserId, $toUserId, $amount " . json_encode(debug_backtrace()));
                return false;
            }
        }

        if (!User::idExists($fromUserId) || !User::idExists($toUserId)) {
            _error_log("transferBalance: user does not exist, $fromUserId, $toUserId, $amount " . json_encode(debug_backtrace()));
            return false;
        }

        $amount = floatval($amount);
        if ($amount <= 0) {
            return false;
        }

        // Sender wallet
        $senderWallet = self::getWallet($fromUserId);
        $senderBalance = $senderWallet->getBalance();
        $senderNewBalance = $senderBalance - $amount;

        if ($senderNewBalance < 0) {
            _error_log("transferBalance: insufficient balance, $fromUserId, $toUserId, $amount (Balance: {$senderBalance}) (New Balance: {$senderNewBalance}) " . json_encode(debug_backtrace()));
            return false;
        }

        $senderIdentification = User::getNameIdentificationById($fromUserId);
        $receiverIdentification = User::getNameIdentificationById($toUserId);

        // Update sender balance
        $senderWallet->setBalance($senderNewBalance);
        $senderWalletId = $senderWallet->save();

        $descriptionFrom = "Transfer Balance {$amount} from <strong>YOU</strong> to user <a href='{$global['webSiteRootURL']}channel/{$toUserId}'>{$receiverIdentification}</a>";
        if (!empty($customDescription)) {
            $descriptionFrom = $customDescription;
        }

        $logIdFrom = WalletLog::addLog($senderWalletId, "-" . $amount, $senderBalance, $descriptionFrom, "{}", "success", "transferBalance to");

        // Receiver wallet
        $receiverWallet = self::getWallet($toUserId);
        $receiverBalance = $receiverWallet->getBalance();
        $receiverNewBalance = $receiverBalance + $amount;

        $receiverWallet->setBalance($receiverNewBalance);
        $receiverWalletId = $receiverWallet->save();

        $descriptionTo = "Transfer Balance {$amount} from user <a href='{$global['webSiteRootURL']}channel/{$fromUserId}'>{$senderIdentification}</a> to <strong>YOU</strong>";
        if (!empty($customDescription)) {
            $descriptionTo = $customDescription;
        }

        ObjectYPT::clearSessionCache();

        $logIdTo = WalletLog::addLog($receiverWalletId, $amount, $receiverBalance, $descriptionTo, "{}", "success", "transferBalance from");

        return [
            'log_id_from' => $logIdFrom,
            'log_id_to' => $logIdTo,
        ];
    }


    public static function transferAndSplitBalanceWithSiteOwner($users_id_from, $users_id_to, $value, $siteowner_percentage, $forceDescription = "")
    {

        $response1 = self::transferBalance($users_id_from, $users_id_to, $value, $forceDescription, true);
        $response2 = true;
        if (!empty($siteowner_percentage)) {
            $siteowner_value = ($value / 100) * $siteowner_percentage;
            if ($response1) {
                $response2 = self::transferBalanceToSiteOwner($users_id_to, $siteowner_value, $forceDescription . " {$siteowner_percentage}% fee", true);
            }
        }

        return $response1 && $response2;
    }

    public function getHTMLMenuRight()
    {
        global $global;
        if (!User::isLogged()) {
            return "";
        }
        $obj = $this->getDataObject();
        if (empty($obj->showWalletOnTopMenu) || !YPTWallet::canSeeWallet()) {
            return '';
        }
        include $global['systemRootPath'] . 'plugin/YPTWallet/view/menuRight.php';
    }

    public static function profileTabName($users_id)
    {
        global $global;
        $obj = AVideoPlugin::getDataObject('YPTWallet');
        if (empty($obj->showWalletOnProfile) || !YPTWallet::canSeeWallet()) {
            return '';
        }
        return getIncludeFileContent($global['systemRootPath'] . 'plugin/YPTWallet/view/menuRight.php', array('profileTab' => 1));
    }

    public static function getAvailablePayments()
    {
        global $global;

        if (!User::isLogged()) {
            echo getButtonSignInAndUp();
            return false;
        }

        $dir = self::getPluginDir();
        $plugins = self::getEnabledPlugins();
        foreach ($plugins as $value) {
            $subdir = $dir . DIRECTORY_SEPARATOR . $value . DIRECTORY_SEPARATOR;
            $file = $subdir . "{$value}.php";
            if (is_dir($subdir) && file_exists($file)) {
                require_once $file;
                $eval = "\$obj = new {$value}();\$obj->getAprovalButton();";
                eval($eval);
            }
        }
        return true;
    }

    public static function getAvailableRecurrentPayments()
    {
        global $global;

        if (!User::isLogged()) {
            $redirectUri = getSelfURI();
            if (!empty($redirectUri)) {
                $redirectUri = "&redirectUri=" . urlencode($redirectUri);
            }
            echo getButtonSignUp() . getButtonSignIn();;
            return false;
        }

        $dir = self::getPluginDir();
        $plugins = self::getEnabledPlugins();
        foreach ($plugins as $value) {
            $subdir = $dir . DIRECTORY_SEPARATOR . $value . DIRECTORY_SEPARATOR;
            $file = $subdir . "{$value}.php";
            if (is_dir($subdir) && file_exists($file)) {
                require_once $file;
                $eval = "\$obj = new {$value}();\$obj->getRecurrentAprovalButton();";
                eval($eval);
            }
        }
    }

    public static function getAvailableRecurrentPaymentsV2($total = '1.00', $currency = "USD", $frequency = "Month", $interval = 1, $name = '', $json = '', $addFunds_Success = '', $trialDays = 0)
    {
        global $global;

        if (!User::isLogged()) {
            $redirectUri = getSelfURI();
            if (!empty($redirectUri)) {
                $redirectUri = "&redirectUri=" . urlencode($redirectUri);
            }
            echo getButtonSignUp() . getButtonSignIn();;
            return false;
        }

        $dir = self::getPluginDir();
        $plugins = self::getEnabledPlugins();
        foreach ($plugins as $value) {
            $subdir = $dir . DIRECTORY_SEPARATOR . $value . DIRECTORY_SEPARATOR;
            $file = $subdir . "{$value}.php";
            if (is_dir($subdir) && file_exists($file)) {
                require_once $file;
                $eval = "\$obj = new {$value}();\$obj->getRecurrentAprovalButtonV2(\$total, \$currency, \$frequency, \$interval, \$name, \$json, \$addFunds_Success, \$trialDays);";
                eval($eval);
            }
        }
    }

    public static function getAvailablePlugins()
    {
        $dir = self::getPluginDir();
        $dirs = scandir($dir);
        $plugins = array();
        foreach ($dirs as $key => $value) {
            if (!in_array($value, array(".", ".."))) {
                $subdir = $dir . DIRECTORY_SEPARATOR . $value . DIRECTORY_SEPARATOR;
                $file = $subdir . "{$value}.php";
                if (is_dir($subdir) && file_exists($file)) {
                    $plugins[] = $value;
                }
            }
        }
        return $plugins;
    }

    public static function getEnabledPlugins()
    {
        global $global;
        $plugins = self::getAvailablePlugins();
        $wallet = new YPTWallet();
        $obj = $wallet->getDataObject();
        foreach ($plugins as $key => $value) {
            $eval = "\$val = \$obj->enablePlugin_{$value};";
            eval($eval);
            if (empty($val)) {
                unset($plugins[$key]);
            }
        }
        return $plugins;
    }

    public static function getPluginDataObject($pluginName)
    {
        $dir = self::getPluginDir();
        $file = $dir . "/{$pluginName}/{$pluginName}.php";
        if (file_exists($file)) {
            require_once $file;
            $eval = "\$obj = new {$pluginName}();";
            eval($eval);
            return $obj->getEmptyDataObject();
        }
        return array();
    }

    public static function getPluginDir()
    {
        global $global;
        $dir = $global['systemRootPath'] . "plugin/YPTWallet/plugins";
        return $dir;
    }

    public function sendEmails($emailsArray, $subject, $message)
    {
        global $global, $config;
        $siteTitle = $config->getWebSiteTitle();
        $footer = $config->getWebSiteTitle();
        $body = $this->replaceTemplateText($siteTitle, $footer, $message);
        return $this->send($emailsArray, $subject, $body);
    }

    private function replaceTemplateText($siteTitle, $footer, $message)
    {
        global $global, $config;
        $text = file_get_contents("{$global['systemRootPath']}plugin/YPTWallet/template.html");
        $words = array($siteTitle, $footer, $message);
        $replace = array('{siteTitle}', '{footer}', '{message}');

        return str_replace($replace, $words, $text);
    }

    private function send($emailsArray, $subject, $body)
    {
        if (empty($emailsArray)) {
            return false;
        }
        $emailsArray = array_unique($emailsArray);

        global $global, $config;
        sendSiteEmail($emailsArray, $subject, $body, $config->getContactEmail(), $config->getWebSiteTitle());
    }
    /* TODO */
    public static function transactionNotification($from_users_id, $to_users_id, $value, $status)
    {
        global $global;
        $identification = User::getNameIdentificationById($from_users_id);
        $element_id = "transactionNotification" . uniqid();

        // Set default values for title, message, icon, and type
        $title = 'Transaction Notification';
        $msg = 'Transaction update';
        $icon = 'fa-solid fa-info-circle';
        $type = UserNotifications::type_info;

        $valueFormated = YPTWallet::formatCurrency($value);
        $href = "{$global['webSiteRootURL']}plugin/YPTWallet/view/history.php";

        // Handle different statuses
        switch ($status) {
            case 'pending':
                $title = 'Transaction Pending';
                $msg = 'Your have a pending transaction of ' . $valueFormated;
                $icon = 'fa-solid fa-hourglass-half'; // Icon for pending
                $type = UserNotifications::type_warning;
                $href = "{$global['webSiteRootURL']}plugin/YPTWallet/view/pendingRequests.php";
                break;
            case 'canceled':
                $title = 'Transaction Canceled';
                $msg = 'Your transaction of ' . $valueFormated . ' was canceled';
                $icon = 'fa-solid fa-times-circle'; // Icon for canceled
                $type = UserNotifications::type_danger;
                break;
            case 'success':
                $title = 'Transaction Successful';
                $msg = 'Your transaction of ' . $valueFormated . ' was successful complete';
                $icon = 'fa-solid fa-check-circle'; // Icon for success
                $type = UserNotifications::type_success;
                break;
            case 'credit':
                // If it is a credit
                $title = 'Funds Received';
                $msg = 'You have received a credit of ' . $valueFormated . ' from ' . $identification;
                $icon = 'fa-solid fa-hand-holding-usd'; // Credit icon
                $type = UserNotifications::type_success;
                break;
            case 'debit':
                // If it is a debit
                $title = 'Funds Deducted';
                $msg = 'A debit of ' . $valueFormated . ' has been processed to ' . $identification;
                $icon = 'fa-solid fa-money-bill-wave'; // Debit icon
                $type = UserNotifications::type_danger;
                break;
            default:
                $msg = 'Unknown status update for your transaction.';
                break;
        }

        // Create the notification
        return self::createNotification($from_users_id, $to_users_id, $title, $msg, $type, $element_id, $icon, $href);
    }


    /* TODO */
    public static function createNotification($from_users_id, $to_users_id, $title, $msg, $type, $element_id, $icon, $href)
    {
        global $global;
        $image = User::getPhoto($from_users_id, false, true);
        $element_id = "{$element_id}_{$from_users_id}_{$to_users_id}";
        //sendSocketSuccessMessageToUsers_id($msg, $friend_users_id);
        return UserNotifications::createNotification($title, $msg, $to_users_id, $image, $href, $type, $element_id, $icon);
    }

    /**
     *
     * @param string $wallet_log_id
     * @param string $new_status
     * return true if balance is enought
     */
    public function processStatus($wallet_log_id, $new_status)
    {
        $obj = $this->getDataObject();
        $walletLog = new WalletLog($wallet_log_id);
        $wallet = new Wallet($walletLog->getWallet_id());
        $oldStatus = $walletLog->getStatus();
        $value = $walletLog->getValue();
        $json = json_decode($walletLog->getJson_data());

        if (!empty($json->value) && $json->value > $value) {
            $value = $json->value;
        }

        if ($walletLog->getType() == self::MANUAL_WITHDRAW) {
            // Notify status change
            if ($new_status != $oldStatus) {
                if ($new_status == "canceled") {
                    // Notification for canceled withdrawal
                    self::transactionNotification($obj->manualWithdrawFundsTransferToUserId, $wallet->getUsers_id(), $value, 'canceled');
                } elseif ($new_status == "success") {
                    // Notification for successful withdrawal
                    self::transactionNotification($obj->manualWithdrawFundsTransferToUserId, $wallet->getUsers_id(), $value, 'success');
                } elseif ($new_status == "pending") {
                    // Notification for pending withdrawal
                    self::transactionNotification($obj->manualWithdrawFundsTransferToUserId, $wallet->getUsers_id(), $value, 'pending');
                }

                if ($oldStatus == "success" || $oldStatus == "pending") {
                    if ($new_status == "canceled") {
                        // Return the value on cancel
                        return self::transferBalance($obj->manualWithdrawFundsTransferToUserId, $wallet->getUsers_id(), $value);
                    } else {
                        // Keep the value on other statuses
                        return true;
                    }
                }

                if ($oldStatus == "canceled") {
                    // Transfer value when moving from canceled to another status
                    return self::transferBalance($wallet->getUsers_id(), $obj->manualWithdrawFundsTransferToUserId, $value);
                }
            }
        } elseif ($walletLog->getType() == self::MANUAL_ADD) {
            // Handle MANUAL_ADD notifications
            if ($oldStatus == "pending") {
                if ($new_status == "canceled") {
                    // Notify canceled add funds
                    self::transactionNotification($obj->manualAddFundsTransferFromUserId, $wallet->getUsers_id(), $value, 'canceled');
                    return true;
                } elseif ($new_status == "success") {
                    // Notify successful add funds
                    self::transactionNotification($obj->manualAddFundsTransferFromUserId, $wallet->getUsers_id(), $value, 'success');
                    return self::transferBalance($obj->manualAddFundsTransferFromUserId, $wallet->getUsers_id(), $value);
                }
            } elseif ($oldStatus == "success") {
                // Get the money back on cancel
                return self::transferBalance($wallet->getUsers_id(), $obj->manualAddFundsTransferFromUserId, $value);
            } elseif ($oldStatus == "canceled") {
                if ($new_status == "pending") {
                    // Do nothing
                    return true;
                } elseif ($new_status == "success") {
                    // Notify success on manual add funds
                    self::transactionNotification($obj->manualAddFundsTransferFromUserId, $wallet->getUsers_id(), $value, 'success');
                    return self::transferBalance($obj->manualAddFundsTransferFromUserId, $wallet->getUsers_id(), $value);
                }
            }
        }

        // Default return if no status change occurs
        return true;
    }


    public static function getUserBalance($users_id = 0)
    {
        if (empty($users_id)) {
            $users_id = User::getId();
        }
        if (empty($users_id)) {
            return 0;
        }
        $wallet = self::getWallet($users_id);
        return $wallet->getBalance();
    }

    public function getFooterCode()
    {
        global $global;
        $obj = $this->getDataObject();
        $js = "";
        $js .= "<script src=\"" . getURL('plugin/YPTWallet/script.js') . "\"></script>";

        return $js;
    }

    static function setAddFundsSuccessRedirectURL($url)
    {
        _session_start();
        $_SESSION['addFunds_Success'] = $url;
    }

    static function getAddFundsSuccessRedirectURL()
    {
        return @$_SESSION['addFunds_Success'];
    }

    static function setAddFundsSuccessRedirectToVideo($videos_id)
    {
        self::setAddFundsSuccessRedirectURL(getRedirectToVideo($videos_id));
    }

    public static function showAdminMessage()
    {
        global $global;
        if (User::isAdmin()) {
            if (empty($global['getWalletConfigurationHTMLAdminMessageShowed'])) {
                echo '<div class="alert alert-info" role="alert">
                    <i class="fa fa-info-circle"></i>
                    <strong>Admin Notice:</strong> This message is visible only to administrators.
                </div>';
                $global['getWalletConfigurationHTMLAdminMessageShowed'] = 1;
            }
        }
    }

    public function getWalletConfigurationHTML($users_id, $wallet, $walletDataObject)
    {
        global $global;
        if (empty($walletDataObject->CryptoWalletEnabled)) {
            if (User::isAdmin()) {
                YPTWallet::showAdminMessage();
                echo '<div class="alert alert-warning" role="alert">
                    <i class="fa fa-exclamation-triangle"></i>
                    YPTWallet configuration will only appear if <strong>CryptoWalletEnabled</strong> is enabled in the plugin parameters.
                    <br>If you have an empty configuration menu, please hide this button by checking the <strong>hideConfiguration</strong> option in the YPTWallet parameters.
                </div>';
            }
            return '';
        }
        include_once $global['systemRootPath'] . 'plugin/YPTWallet/getWalletConfigurationHTML.php';
    }

    static function setLogInfo($wallet_log_id, $information)
    {
        if (!is_array($wallet_log_id)) {
            $wallet_log_id = array($wallet_log_id);
        }
        foreach ($wallet_log_id as $id) {
            $w = new WalletLog($id);
            $w->setInformation($information);
            $w->save();
        }
    }

    static function setLogDescription($wallet_log_id, $description)
    {
        if (!is_array($wallet_log_id)) {
            $wallet_log_id = array($wallet_log_id);
        }
        foreach ($wallet_log_id as $id) {
            $w = new WalletLog($id);
            $w->setDescription($description);
            $w->save();
        }
    }


    public function getPluginMenu()
    {
        global $global;
        $filename = $global['systemRootPath'] . 'plugin/YPTWallet/pluginMenu.html';
        return file_get_contents($filename);
    }


    function getPermissionsOptions()
    {
        $permissions = array();
        $permissions[] = new PluginPermissionOption(YPTWallet::PERMISSION_CAN_SEE_WALLET, __("Wallet"), __("Can see wallet"), 'YPTWallet');
        return $permissions;
    }

    static function canSeeWallet()
    {

        $obj = AVideoPlugin::getDataObjectIfEnabled('YPTWallet');
        if (!empty($obj)) {
            if ($obj->showWalletOnlyToAdmin) {
                return User::isAdmin() || Permissions::hasPermission(YPTWallet::PERMISSION_CAN_SEE_WALLET, 'YPTWallet');
            }
            return true;
        }
        return false;
    }
}
