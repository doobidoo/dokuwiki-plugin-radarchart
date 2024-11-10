; conf/metadata.php
; Configuration metadata
<?php
$meta['width']          = array('numeric', '_min' => 100, '_max' => 1200);
$meta['height']         = array('numeric', '_min' => 100, '_max' => 1200);
$meta['colorScheme']    = array('multichoice', '_choices' => array('default', 'pastel', 'dark', 'neon'));
$meta['minScale']       = array('numeric', '_min' => 0, '_max' => 1000);
$meta['maxScale']       = array('numeric', '_min' => 0, '_max' => 1000);
$meta['legendPosition'] = array('multichoice', '_choices' => array('top', 'bottom', 'left', 'right'));
$meta['borderWidth']    = array('numeric', '_min' => 1, '_max' => 10);
$meta['pointRadius']    = array('numeric', '_min' => 1, '_max' => 10);
$meta['fillOpacity']    = array('numeric', '_min' => 0, '_max' => 1, '_step' => 0.1);
$meta['containerBg']    = array('string');
$meta['chartBg']        = array('string');
