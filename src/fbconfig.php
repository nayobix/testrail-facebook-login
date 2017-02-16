<?php
/* 
 * Testrail Authentication config for login with Facebook OAuth2
 *
 */
$GLOBALS['fb_app_id']           = "****";
$GLOBALS['fb_app_secret']       = "****";
$GLOBALS['fb_app_redirecturi']  = "https://your-testrail-address.com/custom/auth/fblogin.php";
$GLOBALS['fb_app_perms']        = array('email');
$GLOBALS['fb_app_fields']       = "id,name,email";

$GLOBALS['testrail_login_redirecturl']  = "https://your-testrail-address.com/index.php?/auth/login/";
