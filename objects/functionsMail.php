<?php


/**
 *
 * @global array $global
 * @param string $mail
 * call it before send mail to let AVideo decide the method
 */
function setSiteSendMessage(\PHPMailer\PHPMailer\PHPMailer &$mail)
{
    global $global;
    if (empty($mail)) {
        $mail = new \PHPMailer\PHPMailer\PHPMailer();
    }
    if (empty($_POST["comment"])) {
        $_POST["comment"] = '';
    }
    require_once $global['systemRootPath'] . 'objects/configuration.php';
    $config = new AVideoConf();
    $mail->CharSet = 'UTF-8';
    if ($config->getSmtp()) {
        _error_log("Sending SMTP Email");
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP(); // enable SMTP
        if (!empty($_POST) && !empty($_REQUEST['isTest']) && User::isAdmin()) {
            $mail->SMTPDebug = 3;
            $mail->Debugoutput = function ($str, $level) {
                _error_log("SMTP ERROR $level; message: $str", AVideoLog::$ERROR);
            };

            _error_log("Debug enable on the SMTP Email");
        } else {
            _error_log("Debug disabled on the SMTP Email");
        }
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];
        $mail->SMTPAuth = $config->getSmtpAuth(); // authentication enabled
        $mail->SMTPSecure = $config->getSmtpSecure(); // secure transfer enabled REQUIRED for Gmail
        $mail->Host = $config->getSmtpHost();
        $mail->Port = $config->getSmtpPort();
        $mail->Username = $config->getSmtpUsername();
        $mail->Password = $config->getSmtpPassword();
        //_error_log(print_r($config, true));
    } else {
        _error_log("Sending SendMail Email");
        $mail->isSendmail();
    }
    // do not let the system hang on email send
    _session_write_close();
}


function sendSiteEmail($to, $subject, $message, $fromEmail = '', $fromName = '')
{
    global $advancedCustom, $config, $global;
    $resp = false;
    if (empty($to)) {
        _error_log('sendSiteEmail: ERROR: to is empty');
        return false;
    }
    if (is_object($to)) {
        $to = object_to_array($to);
    }
    if (!is_array($to)) {
        $to = [$to];
    }

    if (empty($advancedCustom)) {
        $advancedCustom = AVideoPlugin::loadPlugin("CustomizeAdvanced");
    }

    $subject = UTF8encode($subject);
    $message = UTF8encode($message);
    $message = createEmailMessageFromTemplate($message);

    $total = count($to);
    if ($total == 1) {
        $debug = $to[0];
    } else {
        $debug = "count={$total}";
    }

    _error_log("sendSiteEmail [{$debug}] {$subject}");
    //require_once $global['systemRootPath'] . 'objects/include_phpmailer.php';
    if (empty($fromEmail)) {
        $fromEmail = $config->getContactEmail();
    }
    if (empty($fromName)) {
        $fromName = $config->getWebSiteTitle();
    }
    _error_log("sendSiteEmail: to=" . json_encode($to) . " from={$fromEmail} subject={$subject}");
    $webSiteTitle = $config->getWebSiteTitle();
    if (!is_array($to)) {
        $to = array($to);
    }
    foreach ($to as $key => $value) {
        if (!isValidEmail($value)) {
            _error_log("sendSiteEmail invalid email {$value}");
            unset($to[$key]);
        }
    }
    try {
        $size = intval(@$advancedCustom->splitBulkEmailSend);
        if (empty($size)) {
            $size = 90;
        }
        $to = array_iunique($to);
        $pieces = partition($to, $size);
        $totalEmails = count($to);
        $totalCount = 0;
        _error_log("sendSiteEmail::sending totalEmails=[{$totalEmails}]");
        foreach ($pieces as $piece) {
            $mail = new \PHPMailer\PHPMailer\PHPMailer();
            setSiteSendMessage($mail);
            /**
             * @var \PHPMailer\PHPMailer\PHPMailer $mail
             */
            $mail->setFrom($fromEmail, $fromName);
            if (strpos($subject, $webSiteTitle) === false) {
                $mail->Subject = $subject . " - " . $webSiteTitle;
            } else {
                $mail->Subject = $subject;
            }
            $mail->msgHTML($message);
            $count = 0;
            if(count($piece) > 1){
                foreach ($piece as $value) {
                    $totalCount++;
                    $count++;
                    _error_log("sendSiteEmail::addBCC [{$count}] {$value}");
                    $mail->addBCC($value);
                }
            }else{
                foreach ($piece as $value) {
                    $totalCount++;
                    $count++;
                    _error_log("sendSiteEmail::addAddress [{$count}] {$value}");
                    $mail->addAddress($value);
                }
            }
            //_error_log("sendSiteEmail::sending now count=[{$count}] [{$totalCount}/{$totalEmails}]");

            $resp = $mail->send();
            if (!$resp) {
                _error_log("sendSiteEmail Error Info: {$mail->ErrorInfo} count=[{$count}] [{$totalCount}/{$totalEmails}]");
            } else {
                _error_log("sendSiteEmail Success Info: count=[{$count}] [{$totalCount}/{$totalEmails}]");
            }
        }
        //Set the subject line
        return $resp;
    } catch (Exception $e) {
        _error_log($e->getMessage()); //Boring error messages from anything else!
    }
    return $resp;
}

function partition(array $list, $totalItens)
{
    $listlen = count($list);
    if(empty($listlen)){
        return $list;
    }
    _error_log("partition: listlen={$listlen} totalItens={$totalItens}");
    $p = ceil($listlen / $totalItens);
    $partlen = floor($listlen / $p);

    $partition = [];
    $mark = 0;
    for ($index = 0; $index < $p; $index++) {
        $partition[$index] = array_slice($list, $mark, $totalItens);
        $mark += $totalItens;
    }

    return $partition;
}


function sendSiteEmailAsync($to, $subject, $message)
{
    global $global;
    // If $to is not an array, make it one
    if (!is_array($to)) {
        $to = array($to);
    }
    // Make sure the emails in $to are unique
    $to = array_unique($to);
    $content = ['to' => $to, 'subject' => $subject, 'message' => $message];
    //$tmpFile = getTmpFile();
    $tmpFile = "{$global['systemRootPath']}videos/emails_" . uniqid() . '.log';
    $bytes = file_put_contents($tmpFile, _json_encode($content));
    //outputAndContinueInBackground();
    $command = "php {$global['systemRootPath']}objects/sendSiteEmailAsync.php '$tmpFile' && rm '$tmpFile'";
    $totalEmails = count($to);
    _error_log("sendSiteEmailAsync start [bytes=$bytes] [totalEmails={$totalEmails}] ($command) file_exists=" . file_exists($tmpFile) .' '.json_encode(debug_backtrace()));
    $pid = execAsync($command);
    _error_log("sendSiteEmailAsync end {$pid}");
    return $pid;
}

function sendBulkEmail($users_id_array, $emails_array, $subject, $message)
{

    $obj = AVideoPlugin::getDataObjectIfEnabled('Scheduler');
    if (!empty($users_id_array) && $obj->sendEmails) {
        _error_log("sendBulkEmail Scheduler");
        $Emails_messages = Emails_messages::setOrCreate($message, $subject);
        //var_dump($Emails_messages->getId());
        $count = 0;
        foreach ($users_id_array as $users_id) {
            if (empty($users_id)) {
                continue;
            }
            $Email_to_user = new Email_to_user(0);
            $Email_to_user->setEmails_messages_id($Emails_messages->getId());
            $Email_to_user->setUsers_id($users_id);
            if ($Email_to_user->save()) {
                $count++;
            }
        }
        _error_log("sendBulkEmail Scheduler done total={$count}");
    } else {
        _error_log("sendBulkEmail sendSiteEmailAsync");
        if (empty($emails_array)) {
            $to = array();
            $sql = "SELECT email FROM users WHERE id IN (" . implode(', ', $users_id_array) . ") ";
            $res = sqlDAL::readSql($sql);
            $fullData = sqlDAL::fetchAllAssoc($res);
            sqlDAL::close($res);
            if ($res != false) {
                foreach ($fullData as $row) {
                    if (empty($row['email'])) {
                        continue;
                    }
                    $to[] = $row['email'];
                }
            }
        } else {
            $to = $emails_array;
        }
        // Make sure the emails in $to are unique
        $to = array_unique($to);
        sendSiteEmailAsync($to, $subject, $message);
    }
}

function createEmailMessageFromTemplate($message)
{
    //check if the message already have a HTML body
    if (preg_match("/html>/i", $message)) {
        return $message;
    }

    global $global, $config;
    $text = file_get_contents("{$global['systemRootPath']}view/include/emailTemplate.html");
    $config = new AVideoConf();
    $siteTitle = $config->getWebSiteTitle();
    $logo = "<img src=\"" . getURL($config->getLogo()) . "\" alt=\"{$siteTitle}\"/>";

    $words = [$logo, $message, $siteTitle];
    $replace = ['{logo}', '{message}', '{siteTitle}'];

    return str_replace($replace, $words, $text);
}

function sendEmailToSiteOwner($subject, $message)
{
    global $advancedCustom, $global;
    $subject = UTF8encode($subject);
    $message = UTF8encode($message);
    _error_log("sendEmailToSiteOwner {$subject}");
    global $config, $global;
    //require_once $global['systemRootPath'] . 'objects/include_phpmailer.php';
    $contactEmail = $config->getContactEmail();
    $webSiteTitle = $config->getWebSiteTitle();
    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        setSiteSendMessage($mail);
        /**
         * @var \PHPMailer\PHPMailer\PHPMailer $mail
         */
        $mail->setFrom($contactEmail, $webSiteTitle);
        $mail->Subject = $subject . " - " . $webSiteTitle;
        $mail->msgHTML($message);
        $mail->addAddress($contactEmail);
        $resp = $mail->send();
        if (!$resp) {
            _error_log("sendEmailToSiteOwner Error Info: {$mail->ErrorInfo}");
        } else {
            _error_log("sendEmailToSiteOwner Success Info: $subject ");
        }
        return $resp;
    } catch (Exception $e) {
        _error_log($e->getMessage()); //Boring error messages from anything else!
    }
}


function isValidEmail($email, $checkHost = false)
{
    global $_email_hosts_checked;
    if (empty($email)) {
        _error_log("isValidEmail email is empty");
        return false;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        _error_log("isValidEmail not FILTER_VALIDATE_EMAIL {$email}");
        return false;
    }
    if (preg_match('/@teste?\./i', $email)) {
        _error_log("isValidEmail wrong domain {$email}");
        return false;
    }
    if (preg_match('/@yourDomain?\./i', $email)) {
        _error_log("isValidEmail wrong domain {$email}");
        return false;
    }
    if (!isset($_email_hosts_checked)) {
        $_email_hosts_checked = [];
    }

    if (empty($checkHost)) {
        return true;
    }

    //Get host name from email and check if it is valid
    $email_host = array_slice(explode("@", $email), -1)[0];

    if (isset($_email_hosts_checked[$email_host])) {
        return $_email_hosts_checked[$email_host];
    }

    $_email_hosts_checked[$email_host] = true;
    // Check if valid IP (v4 or v6). If it is we can't do a DNS lookup
    if (!filter_var($email_host, FILTER_VALIDATE_IP, [
        'flags' => FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
    ])) {
        //Add a dot to the end of the host name to make a fully qualified domain name
        // and get last array element because an escaped @ is allowed in the local part (RFC 5322)
        // Then convert to ascii (http://us.php.net/manual/en/function.idn-to-ascii.php)
        $email_host = idn_to_ascii($email_host . '.');

        //Check for MX pointers in DNS (if there are no MX pointers the domain cannot receive emails)
        if (!checkdnsrr($email_host, "MX")) {
            $_email_hosts_checked[$email_host] = false;
        }
    }

    return $_email_hosts_checked[$email_host];
}

/**
 *
 * @param string $strOrArray
 * @return string return an array with the valid emails.
 */
function is_email($strOrArray)
{
    if (empty($strOrArray)) {
        return [];
    }
    if (!is_array($strOrArray)) {
        $strOrArray = [$strOrArray];
    }
    $valid_emails = [];
    foreach ($strOrArray as $email) {
        if (is_numeric($email)) {
            $email = User::getEmailDb($email);
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $valid_emails[] = $email;
        }
    }
    return $valid_emails;
}
