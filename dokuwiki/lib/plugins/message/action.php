<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Etienne M. <emauvaisfr@yahoo.fr>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');

class action_plugin_message extends DokuWiki_Action_Plugin {

    /**
     * Constructor
     */
    function action_plugin_message() {
      $this->setupLocale();
    }
                              
    /**
     * register the eventhandlers
     */
    function register(&$contr) {
        $contr->register_hook('ACTION_ACT_PREPROCESS', 'AFTER', $this, '_display_message', array());
    }

    function _display_message(&$event, $param) {
        global $conf;

        if($event->data == 'show') {
            $fileInvalid=$conf['cachedir'].'/message_invalidsignature.txt';
            if (file_exists($fileInvalid) && filesize($fileInvalid)) {
                msg(@file_get_contents($fileInvalid),-1);
                return;
            }

            $fileValid=$conf['cachedir'].'/message_validsignature.txt';
            if (file_exists($fileValid) && filesize($fileValid)) {
                msg(@file_get_contents($fileValid),1);
            }
        }
        return;
    }
}

// vim:ts=4:sw=4:et:enc=utf-8:
