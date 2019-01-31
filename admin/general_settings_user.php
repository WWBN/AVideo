<?php
    $filter = array(
        'disableNativeSignUp'=>'This is usefull if you want to use our LDAP plugin or maybe only allow authentication from Social Networks',
        'disableNativeSignIn'=>'This is usefull if you want to use our LDAP plugin or maybe only allow authentication from Social Networks',
        'disablePersonalInfo'=>'Disable the My Account personal info like: First and Last Name and address',
        'newUsersCanStream'=>'Automatic allow new users to use your Livestream Platform',
        'doNotIndentifyByEmail'=>'Do not show user\'s email on the site',
        'doNotIndentifyByName'=>'Do not show user\'s name on the site',
        'doNotIndentifyByUserName'=>'Do not show user\'s username on the site',
        'unverifiedEmailsCanNOTLogin'=>'Users must verify their emails before login',
        'onlyVerifiedEmailCanUpload'=>'Users must verify their emails before upload/submit videos',
        'sendVerificationMailAutomaic'=>'After sign up we will automatic send a verification email',
        'userMustBeLoggedIn'=>'Hide the website to non logged users');
createTable("CustomizeUser", $filter);