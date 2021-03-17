# Waterfall

[Waterfall](https://waterfall.social) is a free, open-source social media platform, designed to mimic the tumblelog format. On Waterfall, people can uplod almost anything - essays, images, videos, music - and even has special protections for artists to prevent art theft. 

## Features

Waterfall instances (informally nicknamed "cascades") are self-hosted, and self-contained - they don't interact with other instances (see [here](https://github.com/MasterSteelblade/Waterfall/wiki/Why-not-use-ActivityPub%3F) for reasoning). 

Waterfall allows image, video, and audio uploads, and has a special post type for art that aims to help prevent theft of artwork. 

## Deployment

Tech stack: 
- PHP 7.4+ (7.3 is supported, but recommended against) 
- PostgreSQL 13+
- Redis 5+
- Apache 2.4+

Ubuntu 20.04 LTS is recommended, but any system that can install the above should be fine.

This may change for Waterfall 2.0. Installation instructions can be found in the docs folder. 

## Contributing

Waterfall is licensed under **AGPLv3**. 

Feel free to open issues for bugs or feature requests. You can also submit pull requests to this repository. Get started by looking in [CONTRIBUTING.md](CONTRIBUTING.md). A [Discord server](https://discord.gg/AsH2yDf) is available for developers. 

## License

Copyright (C) 2018 - 2021 Benjamin Clarke, Chaos Ideal Ltd, and other contributors (see [AUTHORS.md](AUTHORS.md)). Individual components may have their own licenses. 

This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License along with this program. If not, see https://www.gnu.org/licenses/.
