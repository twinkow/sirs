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
        $controller->register_hook('TOOLBAR_DEFINE', 'AFTER', $this, 'insert_button', array());
    }

    /**
     * Inserts the toolbar button
     */
    public function insert_button(&$event, $param) {

        $event->data[] = array (
            'type' => 'Digsig',
            'title' => $this->getLang('qb_abutton'),
            'icon' => '../../plugins/digitalsignature/test.png',
        );
    }
}

// vim:ts=4:sw=4:et:
