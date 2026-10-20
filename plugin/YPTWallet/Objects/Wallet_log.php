<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';
require_once dirname(__FILE__) . '/../../../objects/bootGrid.php';
require_once dirname(__FILE__) . '/../../../objects/video.php';
require_once dirname(__FILE__) . '/../../../objects/user.php';
require_once $global['systemRootPath'].'plugin/YPTWallet/Objects/Wallet.php';

class WalletLog extends ObjectYPT {

    protected $id, $value, $description, $wallet_id, $json_data, $status, $type, $information, $previous_wallet_balance;


    static function getSearchFieldsNames() {
        return array();
    }

    static function getTableName() {
        return 'wallet_log';
    }

    function getValue() {
        return $this->value;
    }

    function getDescription() {
        return $this->description;
    }

    function getWallet_id() {
        return $this->wallet_id;
    }

    function getJson_data() {
        return $this->json_data;
    }

    function setValue($value) {
        $this->value = $value;
    }

    function setDescription($description) {
        // stored raw description reaches the admin wallet log DataTable unescaped, so sanitize on write
        // description is VARCHAR(255): strict-mode MySQL rejects the whole row (and a payment with it) if longer
        $this->description = mb_substr((string) xss_esc($description), 0, 255, 'UTF-8');
    }

    function setWallet_id($wallet_id) {
        $this->wallet_id = $wallet_id;
    }

    function setJson_data($json_data) {
        // json_data is TEXT (65535 bytes): cut at a UTF-8 character boundary instead of failing the insert
        $this->json_data = is_string($json_data) ? mb_strcut($json_data, 0, 65535, 'UTF-8') : $json_data;
    }

    function getStatus() {
        return $this->status;
    }

    function getType() {
        return $this->type;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setType($type) {
        $this->type = $type;
    }

    function getInformation() {
        return $this->information;
    }

    function setInformation($information) {
        if(!is_string($information)){
            $information = _json_encode($information);
        }
        $this->information = $information;
    }

    function getPrevious_wallet_balance() {
        return $this->previous_wallet_balance;
    }

    function setPrevious_wallet_balance($previous_wallet_balance) {
        $this->previous_wallet_balance = $previous_wallet_balance;
    }


    static function getAllFromWallet($wallet_id, $dontReturnEmpty = true, $status="") {
        global $global;
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE 1=1 ";

        if(!empty($wallet_id)){
            $sql .= " AND wallet_id=$wallet_id ";
        }

        if($dontReturnEmpty){
            $sql .= " AND value != 0.0 ";
        }

        if(!empty($status)){
            $sql .= " AND status = '$status' ";
        }

        $sql .= self::getSqlFromPost();
        $obj = AVideoPlugin::getObjectData("YPTWallet");
        $res = $global['mysqli']->query($sql);
        $rows = array();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $row['valueText'] = YPTWallet::formatCurrency($row['value']);
                $row['previous_wallet_balance_formated'] = YPTWallet::formatCurrency($row['previous_wallet_balance']);
                $row['wallet'] = Wallet::getFromWalletId($row['wallet_id']);
                $row['user'] = $row['wallet']['user'];
                $row['balance'] = (float)$row['previous_wallet_balance'] + (float)$row['value'];
                $row['balance_formated'] = YPTWallet::formatCurrency($row['balance']);
                $row['crypto_wallet_address'] = "";
                $rows[] = $row;
            }
        } else {
            die($sql . '\nError : (' . $global['mysqli']->errno . ') ' . $global['mysqli']->error);
        }
        return $rows;
    }

    static function getTotalFromWallet($wallet_id, $dontReturnEmpty = true, $status="") {
        global $global;
        $sql = "SELECT * FROM  " . static::getTableName() . " WHERE 1=1 ";

        if(!empty($wallet_id)){
            $sql .= " AND wallet_id=$wallet_id ";
        }

        if($dontReturnEmpty){
            $sql .= " AND value != 0.0 ";
        }

        if(!empty($status)){
            $sql .= " AND status = '$status' ";
        }

        $sql .= self::getSqlSearchFromPost();
        $res = $global['mysqli']->query($sql);
        if(!$res){
            return 0;
        }

        return $res->num_rows;
    }

    static function getAllFromUser($users_id, $dontReturnEmpty = true) {

        $wallet = Wallet::getFromUser($users_id);
        if(empty($wallet)){
            return false;
        }
        return self::getAllFromWallet($wallet['id'], $dontReturnEmpty);
    }

    static function getTotalFromUser($users_id, $dontReturnEmpty = true) {

        $wallet = Wallet::getFromUser($users_id);

        if(empty($wallet)){
            return false;
        }
        return self::getTotalFromWallet($wallet['id'], $dontReturnEmpty);
    }

    /**
     * Every gateway uses the same ledger markers. For pre-marker payments, callers may supply
     * legacyDescriptions (wallet credit LIKE patterns) and/or legacyCheck (their own history lookup).
     * legacyCheck must return true for an already processed payment, false otherwise. It runs
     * under processPaymentOnce()'s lock and transaction; gateway tables stay in their own plugins.
     */
    static function hasProcessedPayment($provider, array $paymentIds, $wallet_id, array $options = [])
    {
        global $global;
        // Preserve the historical three-argument API for Stripe/Razorpay integrations.
        $legacyDefaults = ['stripe' => ['Stripe %'], 'razorpay' => ['RazorPay %']];
        $descriptions = $options['legacyDescriptions'] ?? ($legacyDefaults[$provider] ?? []);
        foreach ($paymentIds as $paymentId) {
            $key = hash('sha256', $provider . ':' . $paymentId);
            $stmt = $global['mysqli']->prepare("SELECT id FROM wallet_log WHERE type = 'paymentProcessed' AND information = ? LIMIT 1 FOR UPDATE");
            if (!$stmt) {
                throw new RuntimeException('Cannot check processed payment: ' . ($global['mysqli']->error ?? ''));
            }
            $stmt->bind_param('s', $key);
            if (!$stmt->execute()) {
                throw new RuntimeException('Cannot check processed payment');
            }
            $stmt->bind_result($id);
            $found = $stmt->fetch();
            $stmt->close();
            if ($found === false) {
                throw new RuntimeException('Cannot read processed payment');
            }
            if ($found) {
                return true;
            }
            // Provider IDs are looked up as JSON strings, never interpolated into SQL.
            $needle = json_encode($paymentId);
            foreach ($descriptions as $description) {
                $stmt = $global['mysqli']->prepare("SELECT id FROM wallet_log WHERE wallet_id = ? AND type = 'addBalance' AND status = 'success' AND description LIKE ? AND LOCATE(?, json_data) > 0 LIMIT 1 FOR UPDATE");
                if (!$stmt) {
                    throw new RuntimeException('Cannot check processed payment: ' . ($global['mysqli']->error ?? ''));
                }
                $stmt->bind_param('iss', $wallet_id, $description, $needle);
                if (!$stmt->execute()) {
                    throw new RuntimeException('Cannot check processed payment');
                }
                $stmt->bind_result($id);
                $found = $stmt->fetch();
                $stmt->close();
                if ($found === false) {
                    throw new RuntimeException('Cannot read processed payment');
                }
                if ($found) {
                    return true;
                }
            }
        }
        return isset($options['legacyCheck']) && $options['legacyCheck']() === true;
    }

    /** Record a gateway payment in the caller's transaction, alongside its credit and own logs. */
    static function addProcessedPayment($provider, $paymentId, $wallet_id, $balance)
    {
        return self::addLog($wallet_id, 0, $balance, "Processed {$provider} payment {$paymentId}",
            '{}', 'success', 'paymentProcessed', hash('sha256', $provider . ':' . $paymentId));
    }

    static function addLog($wallet_id, $value, $previous_wallet_balance, $description="", $json_data="{}", $status="success", $type="", $information=''){
        global $global;
        $log = new WalletLog(0);
        $log->setWallet_id($wallet_id);
        $log->setValue($value);
        $log->setDescription($description);
        $log->setJson_data($json_data);
        $log->setStatus($status);
        $log->setType($type);
        $log->setInformation($information);
        $log->setPrevious_wallet_balance($previous_wallet_balance);
        _error_log('Wallet add log start');
        $id = $log->save();
        if(empty($id)){
            //var_dump($wallet_id, $value, $description, $json_data, $status, $type, $information, $global['lastQuery'], $global['mysqli']->error, debug_backtrace());
        }
        _error_log('Wallet add log end '.$id);
        return $id;
    }

    function save() {
        global $global;
        $id = parent::save();
        return $id;
    }


}
