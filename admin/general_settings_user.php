<?php
$filter = [
    'disableNativeSignUp'=>__('This is useful if you want to use our LDAP plugin or maybe only allow authentication from Social Networks'),
    'disableNativeSignIn'=>__('This is useful if you want to use our LDAP plugin or maybe only allow authentication from Social Networks'),
    'disablePersonalInfo'=>__('Disable the My Account personal info like: First and Last Name and address'),
    'newUsersCanStream'=>__('Automatic allow new users to use your Livestream Platform'),
    'doNotIdentifyByEmail'=>__('Do not show user\'s email on the site'),
    'doNotIdentifyByName'=>__('Do not show user\'s name on the site'),
    'doNotIdentifyByUserName'=>__('Do not show user\'s username on the site'),
    'unverifiedEmailsCanNOTLogin'=>__('Users must verify their emails before login'),
    'onlyVerifiedEmailCanUpload'=>__('Users must verify their emails before upload/submit videos'),
    'sendVerificationMailAutomatic'=>__('After sign up we will automatic send a verification email'),
    'userMustBeLoggedIn'=>__('Hide the website to non logged users'),
];
createTable("CustomizeUser", $filter);
