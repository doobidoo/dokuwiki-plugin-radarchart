<?php
/**
 * DokuWiki Plugin RadarChart (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author Your Name
 */

if (!defined('DOKU_INC')) die();

class syntax_plugin_radarchart extends DokuWiki_Syntax_Plugin {

    private $colorSchemes = array(
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
        ),
        'dark' => array(
            array('background' => 'rgba(45, 52, 54, 0.2)', 'border' => 'rgb(45, 52, 54)'),
            array('background' => 'rgba(116, 125, 140, 0.2)', 'border' => 'rgb(116, 125, 140)'),
            array('background' => 'rgba(47, 53, 66, 0.2)', 'border' => 'rgb(47, 53, 66)'),
            array('background' => 'rgba(87, 96, 111, 0.2)', 'border' => 'rgb(87, 96, 111)')
        ),
        'neon' => array(
            array('background' => 'rgba(0, 255, 255, 0.2)', 'border' => 'rgb(0, 255, 255)'),
            array('background' => 'rgba(255, 0, 255, 0.2)', 'border' => 'rgb(255, 0, 255)'),
            array('background' => 'rgba(0, 255, 0, 0.2)', 'border' => 'rgb(0, 255, 0)'),
            array('background' => 'rgba(255, 255, 0, 0.2)', 'border' => 'rgb(255, 255, 0)')
        )
    );

    private $defaultConfig = array(
        'width' => 400,
        'height' => 400,
        'colorScheme' => 'default',
        'minScale' => 0,
        'maxScale' => 100,
        'legendPosition' => 'top',
        'borderWidth' => 2,
        'pointRadius' => 3,
        'fillOpacity' => 0.2
    );

    public function getType() { return 'protected'; }
    public function getPType() { return 'block'; }
    public function getSort() { return 155; }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<radar.*?>.*?</radar>', $mode, 'plugin_radarchart');
    }

    private function parseConfig($configString) {
        $config = $this->defaultConfig;
        if (preg_match('/<radar\s+([^>]+)>/', $configString, $matches)) {
            $attrs = $matches[1];
            preg_match_all('/(\w+)="([^"]*)"/', $attrs, $pairs);
            for ($i = 0; $i < count($pairs[1]); $i++) {
                $key = $pairs[1][$i];
                $value = $pairs[2][$i];
                if (array_key_exists($key, $config)) {
                    $config[$key] = $value;
                }
            }
        }
        return $config;
    }

    public function handle($match, $state, $pos, Doku_Handler $handler) {
        $config = $this->parseConfig($match);
        
        // Extract content between tags
        preg_match('/<radar.*?>(.*?)<\/radar>/s', $match, $matches);
        $content = $matches[1];
        $lines = explode("\n", $content);
        
        $datasets = array();
        $labels = array();
        $currentDataset = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Dataset definition with optional custom colors
            if (preg_match('/^@dataset\s+([^|]+)(?:\|([^|]+))?(?:\|([^|]+))?$/', $line, $matches)) {
                if ($currentDataset !== null) {
                    $datasets[] = $currentDataset;
                }
                $currentDataset = array(
                    'label' => trim($matches[1]),
                    'data' => array()
                );
                // Custom colors if specified
                if (isset($matches[2])) {
                    $currentDataset['customBackground'] = trim($matches[2]);
                }
                if (isset($matches[3])) {
                    $currentDataset['customBorder'] = trim($matches[3]);
                }
                continue;
            }
            
            // Data points
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
    }

    public function render($mode, Doku_Renderer $renderer, $data) {
        if ($mode !== 'xhtml') return false;

        list($labels, $datasets, $config) = $data;
        $chartId = 'radar_' . uniqid();
        
        // Add Chart.js library
        $renderer->doc .= '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
        
        // Add canvas
        $renderer->doc .= '<div class="radar-chart-container">';
        $renderer->doc .= sprintf('<canvas id="%s" width="%d" height="%d"></canvas>',
            $chartId, $config['width'], $config['height']);
        $renderer->doc .= '</div>';
        
        // Prepare datasets with colors
        $colors = $this->colorSchemes[$config['colorScheme']] ?? $this->colorSchemes['default'];
        $chartDatasets = array();
        foreach ($datasets as $index => $dataset) {
            $colorIndex = $index % count($colors);
            $backgroundColor = isset($dataset['customBackground']) 
                ? $dataset['customBackground'] 
                : $colors[$colorIndex]['background'];
            $borderColor = isset($dataset['customBorder'])
                ? $dataset['customBorder']
                : $colors[$colorIndex]['border'];
            
            $chartDatasets[] = array_merge($dataset, array(
                'fill' => true,
                'backgroundColor' => $backgroundColor,
                'borderColor' => $borderColor,
                'pointBackgroundColor' => $borderColor,
                'pointBorderColor' => '#fff',
                'pointHoverBackgroundColor' => '#fff',
                'pointHoverBorderColor' => $borderColor,
                'borderWidth' => intval($config['borderWidth']),
                'pointRadius' => intval($config['pointRadius'])
            ));
        }
        
        // Generate chart
        $renderer->doc .= '<script>';
        $renderer->doc .= 'new Chart(document.getElementById("' . $chartId . '"), {
            type: "radar",
            data: {
                labels: ' . json_encode($labels) . ',
                datasets: ' . json_encode($chartDatasets) . '
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                elements: {
                    line: {
                        borderWidth: ' . $config['borderWidth'] . '
                    }
                },
                scales: {
                    r: {
                        angleLines: {
                            display: true
                        },
                        suggestedMin: ' . $config['minScale'] . ',
                        suggestedMax: ' . $config['maxScale'] . '
                    }
                },
                plugins: {
                    legend: {
                        position: "' . $config['legendPosition'] . '",
                        labels: {
                            padding: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ": " + context.formattedValue;
                            }
                        }
                    }
                }
            }
        });';
        $renderer->doc .= '</script>';
        
        return true;
    }
}