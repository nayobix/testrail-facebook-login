<?php
/* 
 * Testrail Authentication plugin for login with Facebook OAuth2
 *
 */

session_start();
require_once '/var/www/html/php-graph-sdk-5.0.0/src/Facebook/autoload.php';
require_once '/var/www/html/testrail/custom/auth/fbconfig.php';

// Facebook login function implementation - start
function fb_auth_user_start()
{
  global $fb_app_id;
  global $fb_app_secret;
  global $fb_app_redirecturi;
  global $fb_app_perms;
  $fb_code = isset($_GET['code']) ? $_GET['code'] : "";

  if (empty($fb_code)) {
    $fb = new Facebook\Facebook([
      'app_id' => $fb_app_id,
      'app_secret' => $fb_app_secret,
    ]);
    $helper = $fb->getRedirectLoginHelper();
    $loginUrl = $helper->getLoginUrl($fb_app_redirecturi, $fb_app_perms);
    header("Location: $loginUrl");
    exit();
  } else {
    //echo "SERVER: " . var_dump($_SERVER);
    //echo "GET: " . var_dump($_GET);
    //echo "POST: " . var_dump($_POST);
    //echo "REQUEST: " . var_dump($_REQUEST);
    //echo "SESSION: " . var_dump($_SESSION);
    //exit();
    fb_auth_user_cb();
  }
}

// Facebook login function implementation - callback
function fb_auth_user_cb()
{
  global $fb_app_id;
  global $fb_app_secret;
  global $fb_app_redirecturi;
  global $fb_app_perms;
  global $fb_app_fields;
  global $testrail_login_redirecturl;

  $response = array();

  $fb = new Facebook\Facebook([
    'app_id' => $fb_app_id,
    'app_secret' => $fb_app_secret,
  ]);

  $helper = $fb->getRedirectLoginHelper();
  $urlhelper = $helper->getUrlDetectionHandler();

  try {
    $accessToken = $helper->getAccessToken();
  } catch(Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    echo 'Graph returned an error1: ' . $e->getMessage();
    exit;
  } catch(Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
  }

  if (! isset($accessToken)) {
    if ($helper->getError()) {
      header('HTTP/1.0 401 Unauthorized');
      echo "Error: " . $helper->getError() . "\n";
      echo "Error Code: " . $helper->getErrorCode() . "\n";
      echo "Error Reason: " . $helper->getErrorReason() . "\n";
      echo "Error Description: " . $helper->getErrorDescription() . "\n";
    } else {
      header('HTTP/1.0 400 Bad Request');
      echo 'Bad request';
    }
    exit;
  }

  // Logged in
  // echo '<h3>Access Token</h3>';
  // var_dump($accessToken->getValue());
  // The OAuth 2.0 client handler helps us manage access tokens
 
  $oAuth2Client = $fb->getOAuth2Client();

  // Get the access token metadata from /debug_token
  $tokenMetadata = $oAuth2Client->debugToken($accessToken);

  // Validation (these will throw FacebookSDKException's when they fail)
  $tokenMetadata->validateAppId($fb_app_id);
  // If you know the user ID this access token belongs to, you can validate it here
  //$tokenMetadata->validateUserId('123');
  $tokenMetadata->validateExpiration();

  if (! $accessToken->isLongLived()) {
    // Exchanges a short-lived access token for a long-lived one
    try {
      $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
    } catch (Facebook\Exceptions\FacebookSDKException $e) {
      echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
      exit;
    }
  }

  $_SESSION['fb_access_token'] = (string) $accessToken;

  // User is logged in with a long-lived access token.

  try {
    $response = $fb->get('/me?fields=id,name,email', $accessToken);
  } catch(Facebook\Exceptions\FacebookResponseException $e) {
    echo 'Graph returned an error2: ' . $e->getMessage();
    exit;
  } catch(Facebook\Exceptions\FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
  }

  $user = $response->getGraphUser();

  $_SESSION["fb_account_id"] = $user['id']; 
  $_SESSION["fb_account_name"] = $user['name']; 
  $_SESSION["fb_account_email"] = $user['email']; 

  header("Location: $testrail_login_redirecturl");
  exit();
}

fb_auth_user_start();
