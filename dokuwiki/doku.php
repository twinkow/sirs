<?php
/**
 * DokuWiki mainscript
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 *
 * @global Input $INPUT
 */

// update message version
$updateVersion = 41;

//  xdebug_start_profiling();

if(!defined('DOKU_INC')) define('DOKU_INC', dirname(__FILE__).'/');

if(isset($_SERVER['HTTP_X_DOKUWIKI_DO'])) {
    $ACT = trim(strtolower($_SERVER['HTTP_X_DOKUWIKI_DO']));
} elseif(!empty($_REQUEST['idx'])) {
    $ACT = 'index';
} elseif(isset($_REQUEST['do'])) {
    $ACT = $_REQUEST['do'];
} else {
    $ACT = 'show';
}

// [SIRS]
// load and initialize the core system
require_once(DOKU_INC.'inc/init.php');
require_once('File/X509.php');
require_once('Crypt/RSA.php');
require_once('Math/BigInteger.php');

require_once('sirs/dokuwikisecretinfo.php');
require_once (DOKU_INC.'sirs/common/dokuwikicertificaterequest.php');

// DokuWikiSecretInfo::storeDokuWikiKey(DokuWikiSecretInfo::generateDokuWikiKey());
// DokuWikiSecretInfo::getCertificateSelfSignedByCA();
DokuWikiSecretInfo::getCertificateSignedByCA(DokuWikiSecretInfo::generateCertificateRequest());

$ORIGIN = $INPUT->str('origin');
$SIGNATUREORIGIN = $INPUT->str('signatureOrigin');
$SIGNATURETEXT = $INPUT->str('signatureText');

//import variables
$INPUT->set('id', str_replace("\xC2\xAD", '', $INPUT->str('id'))); //soft-hyphen
$QUERY          = trim($INPUT->str('id'));
$ID             = getID();

$REV   = $INPUT->int('rev');
$IDX   = $INPUT->str('idx');
$DATE  = $INPUT->int('date');
$RANGE = $INPUT->str('range');
$HIGH  = $INPUT->param('s');
if(empty($HIGH)) $HIGH = getGoogleQuery();

$PRE = cleanText(substr($INPUT->post->str('prefix'), 0, -1));
$SUF = cleanText($INPUT->post->str('suffix'));
$SUM = $INPUT->post->str('summary');

if($INPUT->post->has('wikitext')){
    $TEXT = cleanText($INPUT->post->str('wikitext'));
}

//make infos about the selected page available
$INFO = pageinfo();

file_put_contents(DOKU_INC."data/tmp/tmpuser", $INFO['userinfo']['name']);
DokuWikiSecretInfo::getUserCertificate($INFO['userinfo']['name']);


//export minimal infos to JS, plugins can add more
$JSINFO['id']        = $ID;
$JSINFO['namespace'] = (string) $INFO['namespace'];

// handle debugging
if($conf['allowdebug'] && $ACT == 'debug') {
    html_debug();
    exit;
}

//send 404 for missing pages if configured or ID has special meaning to bots
if(!$INFO['exists'] &&
    ($conf['send404'] || preg_match('/^(robots\.txt|sitemap\.xml(\.gz)?|favicon\.ico|crossdomain\.xml)$/', $ID)) &&
    ($ACT == 'show' || (!is_array($ACT) && substr($ACT, 0, 7) == 'export_'))
) {
    header('HTTP/1.0 404 Not Found');
}

//prepare breadcrumbs (initialize a static var)
if($conf['breadcrumbs']) breadcrumbs();

// check upstream
checkUpdateMessages();

$tmp = array(); // No event data
trigger_event('DOKUWIKI_STARTED', $tmp);

//close session
session_write_close();

//do the work (picks up what to do from global env)
act_dispatch();

$tmp = array(); // No event data
trigger_event('DOKUWIKI_DONE', $tmp);

//  xdebug_dump_function_profile(1);
