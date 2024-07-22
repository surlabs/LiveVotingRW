<div alt style="text-align: center; transform: scale(.5);">
	<picture>
		<source media="(prefers-color-scheme: dark)" srcset="https://github.com/surlabs/LiveVotingRW/blob/ilias9/templates/images/GitBannerLiveVoting.png" />
		<img alt="LiveVoting" src="https://github.com/surlabs/LiveVotingRW/blob/ilias9/templates/images/GitBannerLiveVoting.png" />
	</picture>
</div>

# LiveVoting Repository Object Plugin for ILIAS 9
This plugin allows users to embed LiveVoting videos in ILIAS as repository objects

## Installation & Update

### Installation steps
1. Create subdirectories, if necessary for Customizing/global/plugins/Services/Repository/RepositoryObject/ or run the following script from the ILIAS root

```bash
mkdir -p Customizing/global/plugins/Services/Repository/RepositoryObject
cd Customizing/global/plugins/Services/Repository/RepositoryObject
```

3. In Customizing/global/plugins/Services/Repository/RepositoryObject/ **ensure you delete any previous LiveVoting folder**
4. Then, execute:

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