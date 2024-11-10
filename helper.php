<?php
/**
 * DokuWiki Plugin RadarChart (Helper Component)
 */

class helper_plugin_radarchart extends DokuWiki_Plugin {
    
    public function loadConfig() {
        return array(
            'width' => $this->getConf('width'),
            'height' => $this->getConf('height'),
            'colorScheme' => $this->getConf('colorScheme'),
            'minScale' => $this->getConf('minScale'),
            'maxScale' => $this->getConf('maxScale'),
            'legendPosition' => $this->getConf('legendPosition'),
            'borderWidth' => $this->getConf('borderWidth'),
            'pointRadius' => $this->getConf('pointRadius'),
            'fillOpacity' => $this->getConf('fillOpacity'),
            'containerBg' => $this->getConf('containerBg'),
            'chartBg' => $this->getConf('chartBg')
        );
    }
}
