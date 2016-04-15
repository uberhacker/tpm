# Terminus Plugin Manager

A Terminus plugin to manage all your Terminus plugins

## Installation:

Refer to the [Terminus Wiki](https://github.com/pantheon-systems/terminus/wiki/Plugins).

Windows users should install and run `terminus` in [Git for Windows](https://git-for-windows.github.io/).

## Usage:
Install plugin(s):
```
$ terminus plugin install | add plugin-name-1 | <URL to plugin Git repository 1> [plugin-name-2 | <URL to plugin Git repository 2>] ...
```
List all installed plugins:
```
$ terminus plugin show | list
```
Update installed plugin(s):
```
$ terminus plugin update | up all | plugin-name-1 [plugin-name-2] ...
```
Remove installed plugin(s):
```
$ terminus plugin uninstall | remove plugin-name-1 [plugin-name-2] ...
```
Search for plugin(s) *(Partial strings perform a fuzzy search)*:
```
$ terminus plugin search all | plugin-name-1 [plugin-name-2] ...
```
Add plugin Git repositories:
```
$ terminus plugin repository | repo add <URL to plugin Git repository 1> [<URL to plugin Git repository 2>] ...
```
List plugin Git repositories:
```
$ terminus plugin repository | repo list
```
Remove plugin Git repositories:
```
$ terminus plugin repository | repo remove <URL to plugin Git repository 1> [<URL to plugin Git repository 2>] ...
```

**_Note: In order to search for plugins, at least one plugin must be installed which will, in turn, install a searchable plugin repository._**

## Examples:
Install awesome-plugin:
```
$ terminus plugin install https://github.com/username/awesome-plugin.git
```
Install awesome-plugin from a searchable plugin Git repository:
```
$ terminus plugin install awesome-plugin
```
List all installed plugins:
```
$ terminus plugin list
```
Update awesome-plugin:
```
$ terminus plugin update awesome-plugin
```
Update all installed plugins:
```
$ terminus plugin up all
```
Remove awesome-plugin:
```
$ terminus plugin uninstall awesome-plugin
```
Search for all plugins with the word `awesome` in the plugin name:
```
$ terminus plugin search awesome
```
Search for all plugins in searchable plugin Git repositories:
```
$ terminus plugin search all
```
Add a plugin Git repository:
```
$ terminus plugin repo add https://github.com/path/to/plugins
```
List plugin Git repositories:
```
$ terminus plugin repo list
```
Remove a plugin Git repository:
```
$ terminus plugin repo remove https://github.com/path/to/plugins
```
