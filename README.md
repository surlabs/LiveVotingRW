<div alt style="text-align: center; transform: scale(.5);">
	<picture>
		<source media="(prefers-color-scheme: dark)" srcset="https://github.com/surlabs/LiveVotingRW/blob/ilias9/templates/images/GitBannerLiveVoting.png" />
		<img alt="LiveVoting" src="https://github.com/surlabs/LiveVotingRW/blob/ilias9/templates/images/GitBannerLiveVoting.png" />
	</picture>
</div>

# LiveVoting Repository Object Plugin for ILIAS 9 (Beta)
## **This plugin is on public Beta since 23.07.2024**
This plugin allows to create real time votings within ILIAS.
It is compatible with the previous LiveVoting plugin for ILIAS < 7.0.

## Installation & Update
Please, notice that previous versions of the plugin were numbered with dates (e.g. 2021.01.01). This version is numbered with the ILIAS version it is compatible with (e.g. ilias9 -> 9.x).
**You need to change the plugin version in the il_plugin table of the database to something lower than 9.0.0 before running the following commands.**

1. **Ensure you delete any previous LiveVoting folder** in Customizing/global/plugins/Services/Repository/RepositoryObject/ 

2. Create subdirectories, if necessary for Customizing/global/plugins/Services/Repository/RepositoryObject/ or run the following script from the ILIAS root

```bash
mkdir -p Customizing/global/plugins/Services/Repository/RepositoryObject
cd Customizing/global/plugins/Services/Repository/RepositoryObject
```

3. Then, execute:

```bash
git clone https://github.com/surlabs/LiveVotingRW.git ./LiveVoting
git checkout ilias9
```

Ensure you run npm and composer install at platform root before you install/update the plugin
```bash
npm install
composer install --no-dev
```

Run ILIAS update script at platform root
```bash
php setup/setup.php update
```

**Ensure you don't ignore plugins at the ilias .gitignore files and don't use --no-plugins option at ILIAS setup**

# Authors
* A previous version of this plugin was developed and maintained by Fluxlabs, and it is no longer maintained.
* This plugin is maintained by Jesús Copado, Saúl Díaz and Daniel Cazalla through [SURLABS](https://surlabs.com)

# Bug Reports & Discussion
- Bug Reports: [Mantis](https://www.ilias.de/mantis) (Choose project "ILIAS plugins" and filter by category "LiveVoting")
- SIG Panopto [Forum](https://docu.ilias.de/goto_docu_frm_13755.html)

# Version History
* The version 9.x.x for **ILIAS 9** maintained by SURLABS can be found in the Github branch **ilias9**
* The version 8.x.x for **ILIAS 8** maintained by SURLABS can be found in the Github branch **ilias8**
* The previous plugin versions for ILIAS <8 is archived. It can be found in https://github.com/fluxapps/LiveVoting
