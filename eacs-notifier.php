<?PHP 
# POC to update JAMF PRO Extension Attributes without authentication on client side.
# Tested in PHP7 and require php-curl (apt install php-curl)
# Michael Rieder 15.11.2021 https://macos.it-profs.de
require_once('include/client_config.inc.php');



function adios () {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

// ==== Check user agent and method - please define your own agent string in your custom client script
if (!strpos($_SERVER['HTTP_USER_AGENT'], $AGENT)  !== false || $_SERVER['REQUEST_METHOD'] != "POST") { adios ();}


if (isset($_POST['UDID'])){
  require_once('include/client_config.inc.php');
  $UDID = escapeshellcmd($_POST['UDID']);
  // check if string match RFC UUID v4 if its not match we stop the script.
  if (!is_string($UDID) || (preg_match('/^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$/', $UDID) !== 1)) { adios (); }}
  else { adios (); }


// =================================== CHECK UDID IN JAMF PRO ====================================
// # At this point we are sure:
// -> User agent does match
// -> UUID POST String is valid
// -> UUID string is secure

        $ch = curl_init($JAMFPRO.'/JSSResource/computers/udid/'.$UDID."/subset/general");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERPWD, "$APIUSER:$APIPASS");
        //curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('accept: application/json'));
        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch,CURLINFO_HTTP_CODE);

        // check the HTTP status is not 200 in this case we stop the script
        if ($httpcode != 200) { adios (); }
 
        // json_decode array to access keys
        $obj = json_decode($data, true);
        curl_close($ch);

        // access the json array and pickup the computer id.
        $COMPUTERID= $obj['computer']['general']['id'];

        // double check if the computerid is a numeric variable
        if (!is_numeric($COMPUTERID) ) { adios (); }


// =================================== UPDATE EA IN JAMF PRO ====================================          

      $TIMESTAMP = time();
      $WIPEDATETIME = date("Y-m-d H:i:s", $TIMESTAMP);
      $APIXML="
      <computer><extension_attributes><extension_attribute>
      <name>".$EA_NAME."</name><value>".$WIPEDATETIME."</value>
      </extension
      _attribute></extension_attributes></computer>";

      $ch = curl_init($JAMFPRO.'/JSSResource/computers/id/'.$COMPUTERID);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $APIXML);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_USERPWD, "$APIUSER:$APIPASS");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
      $result = curl_exec($ch);
      curl_close($ch);



