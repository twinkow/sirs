<?php
/**
 * DokuWiki Plugin contentencryption (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Alex <alexcp.almeida@gmail.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();
require_once DOKU_INC.'sirs/common/encryptcommon.php';
require_once DOKU_INC.'sirs/dokuwikisecretinfo.php';
require DOKU_INC.'sirs/aws/aws-autoloader.php';
use Aws\Glacier\GlacierClient;
use Aws\S3\S3Client;


// [SIRS]
class action_plugin_contentencryption_contentencryption extends DokuWiki_Action_Plugin {

    private $pageszippedpath;

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler &$controller) {
        $this->pageszippedpath = DOKU_INC.'data/pageszipped';
        $controller->register_hook('IO_WIKIPAGE_WRITE', 'BEFORE', $this, 'handle_io_wikipage_write_before');
        $controller->register_hook('IO_WIKIPAGE_WRITE', 'AFTER', $this, 'handle_io_wikipage_write_after');
        $controller->register_hook('IO_WIKIPAGE_READ', 'AFTER', $this, 'handle_io_wikipage_read');
    }

    /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */

    public function handle_io_wikipage_write_before(Doku_Event &$event, $param) {

        if(($event->data[2] == 'welcome') || ($event->data[2] == 'syntax') ||
            ($event->data[2] == 'dokuwiki') || ($event->data[2] == 'playground'))
            return;

        $key = DokuWikiSecretInfo::retrieveDokuWikiKey();
        $var = ContentEncryptionCBC::encrypt($event->data[0][1], $key);
        $event->data[0][1] = $var;
    }

    public function handle_io_wikipage_write_after(Doku_Event &$event, $param) {

        // makes backup zip
        $pathToPages = DOKU_INC . 'data/pages/';
        $filename = "pages-" . date_format(date_create(), 'YmdHis') . ".zip";
        $output = exec("zip -r $this->pageszippedpath/$filename \"$pathToPages\"");

        $this->writeToS3($filename);
    }

    private function writeToS3($filename)
    {
        $filenameWithPath = "$this->pageszippedpath/$filename";

        $client = S3Client::factory(array(
            'key'      => 'AKIAIY7ZJJCCPV2MDTTQ',
            'secret'   => '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',
        ));

        $acl = 'private';
        $bucket = 'sirs';
        $client->upload($bucket, $filename, fopen($filenameWithPath, 'r'), $acl);
    }

    private function writeToGlacier($filename)
    {
        $filenameWithPath = "$this->pageszippedpath/$filename";
        $vaultname = 'sirs';

        // Glacier connect
        $glacierClient = GlacierClient::factory(array(
            'key'    => 'AKIAIY7ZJJCCPV2MDTTQ',
            'secret' => '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',
            'region' => 'eu-west-1',
        ));

        $result = $glacierClient->uploadArchive(array(
            'accountId' => '-',
            'vaultName' => $vaultname,
            'body'      => fopen($filenameWithPath, 'r'),
        ));

    }

     /**
     * [Custom event handler which performs action]
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return void
     */

    public function handle_io_wikipage_read(Doku_Event &$event, $param) {

        $lang = strstr($event->data[0][0], "/lang/");
        if(($event->data[2] == 'welcome') || ($event->data[2] == 'syntax') ||
            ($event->data[2] == 'dokuwiki') || ($event->data[2] == 'playground') ||
            !empty($lang))
            return;

        $key = DokuWikiSecretInfo::retrieveDokuWikiKey();
        $var = ContentEncryptionCBC::decrypt($event->result, $key);
        $event->result = $var;
    }
}

// vim:ts=4:sw=4:et:
