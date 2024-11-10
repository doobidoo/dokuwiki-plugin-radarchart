<?php
/**
 * DokuWiki Plugin RadarChart (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author Heinrich Krupp
 */

use dokuwiki\Extension\SyntaxPlugin;

class syntax_plugin_radarchart extends SyntaxPlugin {
    /** @var array */
    protected array $colorSchemes;
    
    /** @var array */
    protected array $defaultConfig;

    /**
     * Constructor. Initializes properties.
     */
    public function __construct() {
        $this->colorSchemes = array(
            'default' => array(
                array('background' => 'rgba(54, 162, 235, 0.2)', 'border' => 'rgb(54, 162, 235)'),
                array('background' => 'rgba(255, 99, 132, 0.2)', 'border' => 'rgb(255, 99, 132)'),
                array('background' => 'rgba(75, 192, 192, 0.2)', 'border' => 'rgb(75, 192, 192)'),
                array('background' => 'rgba(255, 159, 64, 0.2)', 'border' => 'rgb(255, 159, 64)')
            ),
            'pastel' => array(
                array('background' => 'rgba(190, 227, 219, 0.2)', 'border' => 'rgb(137, 207, 191)'),
                array('background' => 'rgba(255, 214, 214, 0.2)', 'border' => 'rgb(255, 169, 169)'),
                array('background' => 'rgba(214, 229, 250, 0.2)', 'border' => 'rgb(159, 190, 237)'),
                array('background' => 'rgba(255, 234, 214, 0.2)', 'border' => 'rgb(255, 202, 149)')
            )
        );

        $this->defaultConfig = array(
            'width' => 400,
            'height' => 400,
            'colorScheme' => 'default',
            'minScale' => 0,
            'maxScale' => 100,
            'legendPosition' => 'top',
            'borderWidth' => 2,
            'pointRadius' => 3,
            'fillOpacity' => 0.2,
            'containerBg' => 'default',
            'chartBg' => 'transparent'
        );
    }

    public function getType(): string {
        return 'protected';
    }

    public function getPType(): string {
        return 'block';
    }

    public function getSort(): int {
        return 155;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<radar.*?>.*?</radar>', $mode, 'plugin_radarchart');
    }

    protected function parseConfig($configString): array {
        $config = $this->defaultConfig;
        
        try {
            if (preg_match('/<radar\s+([^>]+)>/', $configString, $matches)) {
                $attrs = $matches[1];
                if (preg_match_all('/(\w+)="([^"]*)"/', $attrs, $pairs)) {
                    for ($i = 0; $i < count($pairs[1]); $i++) {
                        $key = $pairs[1][$i];
                        $value = $pairs[2][$i];
                        if (array_key_exists($key, $config)) {
                            $config[$key] = $value;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            msg('RadarChart config parsing error: ' . hsc($e->getMessage()), -1);
        }
        
        return $config;
    }

    public function handle($match, $state, $pos, $handler): array {
        try {
            $config = $this->parseConfig($match);
            
            if (!preg_match('/<radar.*?>(.*?)<\/radar>/s', $match, $matches)) {
                return array(array(), array(), $config);
            }
            
            $content = trim($matches[1]);
            if (empty($content)) {
                return array(array(), array(), $config);
            }
            
            $lines = explode("\n", $content);
            $datasets = array();
            $labels = array();
            $currentDataset = null;
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                if (preg_match('/^@dataset\s+([^|]+)(?:\|([^|]+))?(?:\|([^|]+))?$/', $line, $matches)) {
                    if ($currentDataset !== null) {
                        $datasets[] = $currentDataset;
                    }
                    $currentDataset = array(
                        'label' => trim($matches[1]),
                        'data' => array()
                    );
                    continue;
                }
                
                $parts = array_map('trim', explode('|', $line));
                if (count($parts) >= 2) {
                    if (!in_array($parts[0], $labels)) {
                        $labels[] = $parts[0];
                    }
                    if ($currentDataset === null) {
                        $currentDataset = array(
                            'label' => 'Dataset 1',
                            'data' => array()
                        );
                    }
                    $currentDataset['data'][] = floatval($parts[1]);
                }
            }
            
            if ($currentDataset !== null) {
                $datasets[] = $currentDataset;
            }
            
            return array($labels, $datasets, $config);
            
        } catch (Exception $e) {
            msg('RadarChart error: ' . hsc($e->getMessage()), -1);
            return array(array(), array(), $this->defaultConfig);
        }
    }

    public function render($mode, $renderer, $data): bool {
        if ($mode !== 'xhtml') return false;

        try {
            list($labels, $datasets, $config) = $data;
            
            if (empty($labels) || empty($datasets)) {
                return false;
            }

            $chartId = 'radar_' . md5(uniqid('', true));
            
            // Container style with specific dimensions
            $containerStyle = sprintf('width: %dpx; height: %dpx;', 
                intval($config['width']), 
                intval($config['height'])
            );
            
            $renderer->doc .= sprintf(
                '<div class="radar-chart-container" style="%s">',
                $containerStyle
            );
            $renderer->doc .= sprintf(
                '<canvas id="%s" width="%d" height="%d"></canvas>',
                $chartId,
                intval($config['width']),
                intval($config['height'])
            );
            $renderer->doc .= '</div>';
            
            $chartData = array(
                'labels' => $labels,
                'datasets' => array()
            );
            
            $colors = $this->colorSchemes[$config['colorScheme']] ?? $this->colorSchemes['default'];
            foreach ($datasets as $index => $dataset) {
                $colorIndex = $index % count($colors);
                $chartData['datasets'][] = array(
                    'label' => $dataset['label'],
                    'data' => $dataset['data'],
                    'backgroundColor' => $colors[$colorIndex]['background'],
                    'borderColor' => $colors[$colorIndex]['border'],
                    'borderWidth' => intval($config['borderWidth']),
                    'fill' => true
                );
            }

            $renderer->doc .= '<script>';
            $renderer->doc .= 'window.addEventListener("load", function() {';
            $renderer->doc .= '  if (typeof Chart === "undefined") {';
            $renderer->doc .= '    const script = document.createElement("script");';
            $renderer->doc .= '    script.src = "https://cdn.jsdelivr.net/npm/chart.js";';
            $renderer->doc .= '    script.onload = function() { createChart(); };';
            $renderer->doc .= '    document.head.appendChild(script);';
            $renderer->doc .= '  } else {';
            $renderer->doc .= '    createChart();';
            $renderer->doc .= '  }';
            $renderer->doc .= '  function createChart() {';
            $renderer->doc .= '    new Chart(document.getElementById("' . $chartId . '"), {';
            $renderer->doc .= '      type: "radar",';
            $renderer->doc .= '      data: ' . json_encode($chartData) . ',';
            $renderer->doc .= '      options: {';
            $renderer->doc .= '        responsive: true,';
            $renderer->doc .= '        maintainAspectRatio: false,';
            $renderer->doc .= '        scales: {r: {suggestedMin: ' . intval($config['minScale']) . ',';
            $renderer->doc .= '                    suggestedMax: ' . intval($config['maxScale']) . '}},';
            $renderer->doc .= '        plugins: {legend: {position: "' . $config['legendPosition'] . '"}}';
            $renderer->doc .= '      }';
            $renderer->doc .= '    });';
            $renderer->doc .= '  }';
            $renderer->doc .= '});';
            $renderer->doc .= '</script>';

            return true;
        } catch (Exception $e) {
            msg('RadarChart render error: ' . hsc($e->getMessage()), -1);
            return false;
        }
    }
}