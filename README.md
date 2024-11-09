# DokuWiki Radar Chart Plugin

A flexible DokuWiki plugin for creating radar charts with multiple datasets, custom styling, and extensive configuration options.

## Features

- Multiple datasets support
- Four predefined color schemes (default, pastel, dark, neon)
- Custom colors per dataset
- Configurable chart dimensions and styling
- Responsive design with dark mode support
- Enhanced tooltips and interactions

## Installation

1. Download the plugin
2. Unzip to `lib/plugins/radarchart/` in your DokuWiki installation
3. Configure through DokuWiki's plugin manager

## Usage

Basic usage:
```
<radar>
Speed|85
Accuracy|92
Power|78
Control|95
Endurance|88
</radar>
```

Advanced usage with multiple datasets and custom colors:
```
<radar width="600" height="600" colorScheme="pastel" legendPosition="right">
@dataset Player 1|rgba(255,100,100,0.2)|rgb(255,100,100)
Speed|85
Accuracy|92
Power|78
Control|95
Endurance|88

@dataset Player 2
Speed|75
Accuracy|88
Power|92
Control|85
Endurance|79
</radar>
```

## Configuration Options

| Option | Description | Default |
|--------|-------------|---------|
| width | Chart width in pixels | 400 |
| height | Chart height in pixels | 400 |
| colorScheme | Color scheme (default/pastel/dark/neon) | default |
| minScale | Minimum scale value | 0 |
| maxScale | Maximum scale value | 100 |
| legendPosition | Legend placement (top/bottom/left/right) | top |
| borderWidth | Line thickness | 2 |
| pointRadius | Size of data points | 3 |

## Requirements

- DokuWiki >= 2020-07-29 "Hogfather"
- Modern web browser with JavaScript enabled
- Internet connection (for Chart.js CDN)

## License

GPL 2 (http://www.gnu.org/licenses/gpl-2.0.html)

## Author

Heinrich Krupp (heinrich.krupp@gmil.com)

## Support

- Report issues on GitHub
- Visit the [DokuWiki Plugin Page](https://www.dokuwiki.org/plugin:radarchart)
