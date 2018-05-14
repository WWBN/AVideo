<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/YPTWallet/Objects/Wallet.php';
require_once $global['systemRootPath'] . 'plugin/YPTWallet/Objects/Wallet_log.php';

class YPTWallet extends PluginAbstract {
    const MANUAL_WITHDRAW = "Manual Withdraw Funds";
    const MANUAL_ADD = "Manual Add Funds";
    
    public function getDescription() {
        return "Wallet for YouPHPTube";
    }

    public function getName() {
        return "YPTWallet";
    }

    public function getUUID() {
        return "2faf2eeb-88ac-48e1-a098-37e76ae3e9f3";
    }

    public function getEmptyDataObject() {
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
        $obj->withdraw_funds_text = "<h1>Withdraw money from your</h1>Transfer funds from your account to your credit card or bank account";
        $obj->withdraw_funds_success_success = "<h1>Thank you,<br> Your request was submited<h1>";
        $obj->withdraw_funds_success_fail = "<h1>Sorry,<br> Your funds withdraw request has been fail<h1>";
        $obj->currency = "USD";
        $obj->currency_symbol = "$";
        $obj->addFundsOptions = "[5,10,20,50]";
        $obj->showWalletOnlyToAdmin = false;
        $obj->CryptoWalletName = "Bitcoin Wallet Address";
        $obj->enableAutomaticAddFundsPage = true;        
        // add funds
        $obj->enableManualAddFundsPage = false;        
        $obj->manualAddFundsMenuTitle = "Add Funds/Deposit";
        $obj->manualAddFundsPageButton = "Notify Deposit Made";
        $obj->manualAddFundsNotifyEmail = "yourEmail@yourDomain.com";
        $obj->manualAddFundsTransferFromUserId = 1;
        // sell funds        
        $obj->enableManualWithdrawFundsPage = true;    
        $obj->withdrawFundsOptions = "[5,10,20,50,100,1000]";    
        $obj->manualWithdrawFundsMenuTitle = "Withdraw Funds";
        $obj->manualWithdrawFundsPageButton = "Request Withdraw";
        $obj->manualWithdrawFundsNotifyEmail = "yourEmail@yourDomain.com";
        $obj->manualWithdrawFundsminimum = 1;
        $obj->manualWithdrawFundsmaximum = 100;
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

    public function getTags() {
        return array('free', 'monetization');
    }

    public function getBalance($users_id) {
        $wallet = $this->getWallet($users_id);
        return $wallet->getBalance();
    }
    
    public function getBalanceText($users_id) {
        $balance = $this->getBalanceFormated($users_id);
        return self::formatCurrency($balance);
    }
    
    public function getBalanceFormated($users_id) {
        $balance = $this->getBalance($users_id);
        $obj = $this->getDataObject();
        return number_format($balance, $obj->decimalPrecision);
    }
    
    static function formatCurrency($value){
        $value = floatval($value);
        $obj = YouPHPTubePlugin::getObjectData('YPTWallet');
        return "{$obj->currency_symbol} ".number_format($value, $obj->decimalPrecision)." {$obj->currency}";
    }

    public function getWallet($users_id) {
        $wallet = new Wallet(0);
        $wallet->setUsers_id($users_id);
        return $wallet;
    }
    
    public function getOrCreateWallet($users_id) {
        $wallet = new Wallet(0);
        $wallet->setUsers_id($users_id);
        if(empty($wallet->getId())){
            $wallet_id = $wallet->save();
            $wallet = new Wallet($wallet_id);
        }
        return $wallet;
        
    }
    
    function getAllUsers($activeOnly = true) {
        global $global;
        $sql = "SELECT w.*, u.*, u.id as user_id, IFNULL(balance, 0) FROM users u "
                . " LEFT JOIN wallet w ON u.id = w.users_id WHERE 1=1 ";

        if($activeOnly){
            $sql .= " AND status = 'a' ";
        }
        
        $sql .= BootGrid::getSqlFromPost(array('name', 'email', 'user'));

        $res = $global['mysqli']->query($sql);
        $user = array();
        
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $row['name'] = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F]/u', '', $row['name']);
                $row['identification'] = User::getNameIdentificationById($row['user_id']);
                $row['identification'] = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F]/u', '', $row['identification']);
                $row['background'] = User::getBackground($row['user_id']);
                $row['photo'] = User::getPhoto($row['user_id']);
                $user[] = $row['id'];
            }
            //$user = $res->fetch_all(MYSQLI_ASSOC);
        } else {
            $user = false;
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $user;
    }
    
    static function getTotalBalance() {
        global $global;
        $sql = "SELECT sum(balance) as total FROM wallet ";

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

    static function getTotalBalanceText() {
        $value = self::getTotalBalance();
        return self::formatCurrency($value);
    }
    
    public function getHistory($user_id) {
        $wallet = $this->getWallet($user_id);
        $log = new WalletLog(0);
        $rows = $log->getAllFromWallet($wallet->getId());
        return $rows;
    }
    
    /**
     * 
     * @param type $users_id
     * @param type $value
     * @param type $description
     * @param type $json_data
     * @param type $mainWallet_user_id A user ID where the money comes from and where the money goes for
     */
    public function addBalance($users_id, $value, $description="", $json_data="{}", $mainWallet_user_id=0) {
        global $global;
        $wallet = $this->getWallet($users_id);
        $balance = $wallet->getBalance();
        $balance+=$value;
        $wallet->setBalance($balance);
        $wallet_id = $wallet->save();     
        
        WalletLog::addLog($wallet_id, $value, $description, $json_data, "success", "addBalance");
        
        if(!empty($mainWallet_user_id)){
            $wallet = $this->getWallet($mainWallet_user_id);
            $balance = $wallet->getBalance();
            $balance+=($value*-1);
            $wallet->setBalance($balance);
            $wallet_id = $wallet->save();     
            $user = new User($users_id);
            WalletLog::addLog($wallet_id, ($value*-1), " From user ($users_id) ".$user->getUser()." - ".$description , $json_data, "success", "addBalance to main wallet");
        }
        
    }

    public function saveBalance($users_id, $value) {
        if(!User::isAdmin()){
            return false;
        }
        $wallet = $this->getWallet($users_id);
        $balance = $wallet->getBalance();
        $wallet->setBalance($value);
        $wallet_id = $wallet->save();     
        $description = "Admin set your balance, from {$balance} to {$value}";
        WalletLog::addLog($wallet_id, $value, $description, "{}", "success", "saveBalance");
    }
    
    public function transferBalance($users_id_from,$users_id_to, $value) {
        global $global;
        if(!User::isAdmin()){
            if($users_id_from != User::getId()){
                error_log("transferBalance: you are not admin, $users_id_from,$users_id_to, $value");
                return false;
            }            
        }
        if(!User::idExists($users_id_from) || !User::idExists($users_id_to) ){
            error_log("transferBalance: user does not exists, $users_id_from,$users_id_to, $value");
            return false;
        }        
        $value = floatval($value);
        if($value<=0){
            error_log("transferBalance: invalid value, $users_id_from,$users_id_to, $value");
            return false;
        }
        $wallet = $this->getWallet($users_id_from);
        $balance = $wallet->getBalance();
        $newBalance = $balance-$value;
        if($newBalance<0){
            error_log("transferBalance: you dont have balance, $users_id_from,$users_id_to, $value");
            return false;
        }
        $identificationFrom = User::getNameIdentificationById($users_id_from);
        $identificationTo = User::getNameIdentificationById($users_id_to);
        
        $wallet->setBalance($newBalance);
        $wallet_id = $wallet->save();   
        $description = "Transfer Balance {$value} from <strong>YOU</strong> to user <a href='{$global['webSiteRootURL']}channel/{$users_id_to}'>{$identificationTo}</a>";
        WalletLog::addLog($wallet_id, $value, $description, "{}", "success", "transferBalance to");
        
        
        $wallet = $this->getWallet($users_id_to);
        $balance = $wallet->getBalance();
        $newBalance = $balance+$value;
        $wallet->setBalance($newBalance);
        $wallet_id = $wallet->save();   
        $description = "Transfer Balance {$value} from user <a href='{$global['webSiteRootURL']}channel/{$users_id_from}'>{$identificationFrom}</a> to <strong>YOU</strong>";
        WalletLog::addLog($wallet_id, $value, $description, "{}", "success", "transferBalance from");
        return true;
    }

    public function getHTMLMenuRight() {
        global $global;
        if (!User::isLogged()) {
            return "";
        }
        $obj = $this->getDataObject();
        if($obj->showWalletOnlyToAdmin && !User::isAdmin()){
            return "";
        }
        include $global['systemRootPath'] . 'plugin/YPTWallet/view/menuRight.php';
    }

    static function getAvailablePayments() {
        global $global;
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
    }
    
    static function getAvailablePlugins() {
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
    
    static function getEnabledPlugins() {
        global $global;
        $plugins = self::getAvailablePlugins();
        $wallet = new YPTWallet();
        $obj = $wallet->getDataObject();
        foreach ($plugins as $key => $value) {
            $eval = "\$val = \$obj->enablePlugin_{$value};";
            eval($eval);
            if(empty($val)){
                unset($plugins[$key]);
            }
        }
        return $plugins;
    }
    
    static function getPluginDataObject($pluginName){
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
    
    static function getPluginDir(){
        global $global;
        $dir = $global['systemRootPath'] . "plugin/YPTWallet/plugins";
        return $dir;
    }
    
    function sendEmails($emailsArray, $subject, $message){
        global $global, $config;
        $siteTitle = $config->getWebSiteTitle();
        $footer = $config->getWebSiteTitle();
        $body = $this->replaceTemplateText($siteTitle,$footer,$message);
        return $this->send($emailsArray, $subject, $body);
        
    }
    
    private function replaceTemplateText($siteTitle,$footer,$message){
        global $global, $config;
        $text = file_get_contents("{$global['systemRootPath']}plugin/YPTWallet/template.html");        
        $words = array($siteTitle,$footer,$message);
        $replace = array('{siteTitle}', '{footer}','{message}');
        
        return str_replace($replace, $words, $text);
    }
    
    private function send($emailsArray, $subject, $body){
        if(empty($emailsArray)){
            return false;
        }
        $emailsArray = array_unique($emailsArray);
        
        global $global, $config;
        
        require_once $global['systemRootPath'] . 'objects/PHPMailer/PHPMailerAutoload.php';
        
        //Create a new PHPMailer instance
        $mail = new PHPMailer;
        setSiteSendMessage($mail);
        //Set who the message is to be sent from
        $mail->setFrom($config->getContactEmail(), $config->getWebSiteTitle());
        //Set who the message is to be sent to
        foreach ($emailsArray as $value) {
            if(empty($value)){
                continue;
            }
            $mail->addBCC($value);
        }        
        //Set the subject line
        $mail->Subject = $subject;
        $mail->msgHTML($body);

        //send the message, check for errors
        if (!$mail->send()) {
            error_log("Wallet email FAIL [{$subject}] {$mail->ErrorInfo}");
            return false;
        } else {
            error_log("Wallet email sent [{$subject}]");
            return true;
        }
    }
    
    /**
     * 
     * @param type $wallet_log_id
     * @param type $new_status
     * return true if balance is enought
     */
    function processStatus($wallet_log_id, $new_status){
        $obj = $this->getDataObject();
        $walletLog = new WalletLog($wallet_log_id);
        $wallet = new Wallet($walletLog->getWallet_id());
        $oldStatus = $walletLog->getStatus();
        if($walletLog->getType() == self::MANUAL_WITHDRAW){
            if($new_status != $oldStatus){
                if($oldStatus=="success" || $oldStatus=="pending"){
                    if($new_status=="canceled"){
                        // return the value
                        return $this->transferBalance($obj->manualWithdrawFundsTransferToUserId, $wallet->getUsers_id(), $walletLog->getValue());
                    }else{
                        // keep the value
                        return true;
                    }
                }
                // get the value again
                if($oldStatus=="canceled"){
                    return $this->transferBalance($wallet->getUsers_id(), $obj->manualWithdrawFundsTransferToUserId, $walletLog->getValue());
                }
            }
        }else if($walletLog->getType() == self::MANUAL_ADD){
            if($oldStatus=="pending"){
                if($new_status=="canceled"){
                    // do nothing
                    return true;                    
                }else if($new_status=="success"){
                    // transfer the value
                    return $this->transferBalance($obj->manualAddFundsTransferFromUserId,$wallet->getUsers_id(), $walletLog->getValue());
                }
            }else if($oldStatus=="success"){
                //get the money back
                return $this->transferBalance($wallet->getUsers_id(),$obj->manualAddFundsTransferFromUserId, $walletLog->getValue());
            }else if($oldStatus=="canceled"){
                if($new_status=="pending"){
                    // do nothing
                    return true;                    
                }else if($new_status=="success"){
                    // transfer the value
                    return $this->transferBalance($obj->manualAddFundsTransferFromUserId,$wallet->getUsers_id(), $walletLog->getValue());
                }
            }
        }
        return true;
    }

}
