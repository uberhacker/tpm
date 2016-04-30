# Terminus Plugin Manager

A Terminus plugin to manage all your Terminus plugins

## Installation:

Refer to the [Terminus Wiki](https://github.com/pantheon-systems/terminus/wiki/Plugins).

Windows users should install and run `terminus` in [Git for Windows](https://git-for-windows.github.io/).

## Usage:
Install plugin(s):
```
$ terminus plugin install | add <URL to plugin Git repository 1> [<URL to plugin Git repository 2>] ...
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

## Examples:
Install awesome-plugin:
```
$ terminus plugin install https://github.com/username/awesome-plugin.git
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

## Search:
To search for available plugins, see the companion plugin [Terminus Plugin Search](https://github.com/uberhacker/tps).
