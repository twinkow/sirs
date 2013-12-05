<?php
/**
 * DokuWiki Plugin digitalsignature (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Alex Almeida <alexcp.almeida@gmail.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_digitalsignature_digitalsignature extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler &$controller) {
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle_act', array());
    }

    function handle_act(Doku_Event &$event, $param) {

        if(is_array($event->data) && isset($event->data['save'])) {

            // var_dump($event);
        }
        else return;
    }
}

// vim:ts=4:sw=4:et:
