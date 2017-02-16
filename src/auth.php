<?php
/* 
 * Testrail Authentication plugin for login with Facebook OAuth2
 *
 */

session_start();
require_once '/var/www/html/php-graph-sdk-5.0.0/src/Facebook/autoload.php';
require_once '/var/www/html/testrail/custom/auth/fbconfig.php';

// Testrail API function - preserve name/arguments
function authenticate_user($fbname, $fbpass)
{
  global $fb_app_redirecturi;

  $referer_get = array();
  $referer_url = $_SERVER['HTTP_REFERER'];
  $referer_array = parse_url($referer_url);
  $referer_query = $referer_array["query"];
  parse_str($referer_query, $referer_get);

  $response = fb_isLogged();
  if ($response == false) {
    // return new AuthResultFallback();
    header("Location: $fb_app_redirecturi");
    exit();
  }

  /*
   * If succesfully logged in Facebook then get the $_SESSION vars
   * and unset them
   *
   * Heads up!
   * $_SESSION vars are populated always when successfully logged in to facebook
   *
   */
  $user_id = $_SESSION["fb_account_id"];
  $user_email = $_SESSION["fb_account_email"];
  $user_name = $_SESSION["fb_account_name"];
  unset($_SESSION["fb_access_token"]);
  unset($_SESSION["fb_account_id"]);
  unset($_SESSION["fb_account_email"]);
  unset($_SESSION["fb_account_name"]);
 
  $result = new AuthResultSuccess($user_email);
  $result->create_account = true;       // Create an account, if needed
  $result->name = $user_name;           // 'Bob Smith'
  // $result->role_id = 3;              // Optional: ID of the user's role
  // $result->group_ids = array(1, 2);  // Optional: IDs of the user's groups    
  // $result->is_admin = true;          // Optional: Admin privileges
    
  return $result;
}

function fb_isLogged() {
  if(isset($_SESSION['fb_access_token'])    && 
    isset($_SESSION["fb_account_id"])       &&
    isset($_SESSION["fb_account_email"])    &&
    isset($_SESSION["fb_account_name"])) {
      return true;
    }

  return false;
}
