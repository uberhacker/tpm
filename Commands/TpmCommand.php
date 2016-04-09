<?php

namespace Terminus\Commands;

use Terminus\Commands\TerminusCommand;
use Terminus\Exceptions\TerminusException;
use Terminus\Utils;

/**
 * Terminus plugin to manage all your Terminus plugins
 *
 * @command plugin
 */
class TpmCommand extends TerminusCommand {

  /**
   * Object constructor
   *
   * @param array $options Elements as follow:
   * @return TpmCommand
   */
  public function __construct(array $options = []) {
    parent::__construct($options);
  }

  /**
   * Install plugin(s)
   *
   * @param array $args
   *   A list of one or more URLs to plugin Git repositories
   *
   * @subcommand install
   * @alias add
   */
  public function install($args) {
    if (!isset($args)) {
      $message = "Usage: terminus plugin install [URL to plugin Git repository 1] [URL to plugin Git repository 2] ...";
      $this->failure($message);
    }

    $plugins_dir = $this->getPluginDir('');

    foreach ($args as $arg) {
      $plugin_data = file_get_contents($arg);
      $terminus_plugin = !empty($plugin_data) ? stripos($plugin_data, 'terminus plugin') : false;
      if (empty($plugin_data) || !$terminus_plugin) {
        $message = "$arg is not a valid plugin Git repository.";
        $this->failure($message);
      }
      exec("cd $plugins_dir && git clone $arg", $output);
      foreach ($output as $message) {
        $this->log()->notice($message);
      }
    }
  }

  /**
   * List all installed plugins
   *
   * @subcommand show
   * @alias list
   */
  public function show() {
    $plugins_dir = $this->getPluginDir('');
    exec("ls $plugins_dir | xargs 2> /dev/null", $output);
    if (empty($output[0])) {
      $message = "No plugins installed.";
      $this->log()->notice($message);
    }
    else {
      $message = "Plugins are installed in $plugins_dir.";
      $this->log()->notice($message);
      $message = "The following plugins are installed:";
      $this->log()->notice($message);
      $plugins = explode(" ", $output[0]);
      $this->log()->notice($plugins);
    }
  }

  /**
   * Update plugin(s)
   *
   * @param array $args
   *   'all' or a list of one or more installed plugin names
   *
   * @subcommand update
   * @alias up
   */
  public function update($args) {
    if (!isset($args)) {
      $message = "Usage: terminus plugin update [all | plugin-name-1] [plugin-name-2] ...";
      $this->failure($message);
    }

    if ($args[0] == 'all') {
      $plugins_dir = $this->getPluginDir('');
      exec("ls $plugins_dir | xargs 2> /dev/null", $output);
      if (empty($output[0])) {
        $message = "No plugins installed.";
        $this->log()->notice($message);
      }
      else {
        $plugins = explode(" ", $output[0]);
        foreach ($plugins as $plugin) {
          $this->updatePlugin($plugin);
        }
      }
    }
    else {
      foreach ($args as $arg) {
        $this->updatePlugin($arg);
      }
    }
  }

  /**
   * Remove plugin(s)
   *
   * @param array $args
   *   A list of one or more installed plugin names
   *
   * @subcommand uninstall
   * @alias remove
   */
  public function uninstall($args) {
    if (!isset($args)) {
      $message = "Usage: terminus plugin uninstall [plugin-name-1] [plugin-name-2] ...";
      $this->failure($message);
    }

    foreach ($args as $arg) {
      $plugin = $this->getPluginDir($arg);
      if (!is_dir($plugin)) {
        $message = "$arg plugin is not installed.";
        $this->failure($message);
      }
      else {
        exec("rm -rf $plugin", $output);
        foreach ($output as $message) {
          $this->log()->notice($message);
        }
        $message = "$arg plugin was removed successfully.";
        $this->log()->notice($message);
      }
    }
  }

  /**
   * Get the plugin directory
   *
   * @param string
   *   Plugin name
   * @return string
   *   Plugin directory
   */
  private function getPluginDir($arg) {
    $plugins_dir = getenv('TERMINUS_PLUGINS_DIR');
    if (!$plugins_dir) {
      $plugins_dir = \Terminus\Utils\isWindows() ? getenv('HOMEPATH') . '\\terminus\\plugins\\' : getenv('HOME') . '/terminus/plugins/';
    }
    else {
      // Make sure the proper trailing slash exists
      $slash = \Terminus\Utils\isWindows() ? '\\' : '/';
      $plugins_dir .= (substr($plugins_dir, -1) == $slash ? '' : $slash);
    }
    return $plugins_dir . $arg;
  }

  /**
   * Update a specific plugin
   *
   * @param string
   *   Plugin name
   */
  private function updatePlugin($arg) {
    $plugin = $this->getPluginDir($arg);
    if (!is_dir($plugin)) {
      $message = "$arg plugin is not installed.";
      $this->failure($message);
    }
    else {
      $message = "Updating $arg plugin...";
      $this->log()->notice($message);
      exec("cd $plugin && git pull", $output);
      foreach ($output as $message) {
        $this->log()->notice($message);
      }
    }
  }
}
