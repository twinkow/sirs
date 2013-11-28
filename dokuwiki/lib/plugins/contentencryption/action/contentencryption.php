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

// [SIRS]
class action_plugin_contentencryption_contentencryption extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler &$controller) {
        $controller->register_hook('IO_WIKIPAGE_WRITE', 'BEFORE', $this, 'handle_io_wikipage_write');
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

    public function handle_io_wikipage_write(Doku_Event &$event, $param) {

        if(($event->data[2] == 'welcome') || ($event->data[2] == 'syntax') ||
            ($event->data[2] == 'dokuwiki') || ($event->data[2] == 'playground'))
            return;

        $key = DokuWikiSecretInfo::retrieveDokuWikiKey();
        $var = ContentEncryptionCBC::encrypt($event->data[0][1], $key);
        $event->data[0][1] = $var;
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
