<?php

namespace Terminus\Commands;

use Terminus\Commands\TerminusCommand;
use Terminus\Exceptions\TerminusException;
use Terminus\Utils;

/**
 * Manage Terminus plugins
 *
 * @command plugin
 */
class PluginCommand extends TerminusCommand {

  /**
   * Object constructor
   *
   * @param array $options Elements as follow:
   * @return PluginCommand
   */
  public function __construct(array $options = []) {
    parent::__construct($options);
  }

  /**
   * Install plugin(s)
   *
   * @param array $args A list of URLs to plugin Git repositories
   *
   * @subcommand install
   * @alias add
   */
  public function install($args = array()) {
    if (empty($args)) {
      $message = "Usage: terminus plugin install | add";
      $message .= " <URL to plugin Git repository 1>";
      $message .= " [<URL to plugin Git repository 2>] ...";
      $this->failure($message);
    }

    $plugins_dir = $this->getPluginDir();

    foreach ($args as $arg) {
      $is_url = (filter_var($arg, FILTER_VALIDATE_URL) !== false);
      if (!$is_url) {
        $message = "$arg is not a valid plugin Git repository.";
        $this->log()->error($message);
      } else {
        $parts = parse_url($arg);
        $path = explode('/', $parts['path']);
        $plugin = array_pop($path);
        $repository = $parts['scheme'] . '://' . $parts['host'] . implode('/', $path);
        if (!$this->isValidPlugin($repository, $plugin)) {
          $message = "$arg is not a valid plugin Git repository.";
          $this->log()->error($message);
        } else {
          if (is_dir($plugins_dir . $plugin)) {
            $message = "$plugin plugin already installed.";
            $this->log()->notice($message);
          } else {
            exec("cd \"$plugins_dir\" && git clone $arg", $output);
            foreach ($output as $message) {
              $this->log()->notice($message);
            }
          }
        }
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
    $plugins_dir = $this->getPluginDir();
    exec("ls \"$plugins_dir\"", $plugins);
    if (empty($plugins[0])) {
      $message = "No plugins installed.";
      $this->log()->notice($message);
    } else {
      $rows = array();
      $labels = [
        'name'        => 'Name',
        'location'    => 'Location',
        'description' => 'Description',
      ];
      $windows = Utils\isWindows();
      if ($windows) {
        $slash = '\\\\';
      } else {
        $slash = '/';
      }
      $message = "Plugins are installed in $plugins_dir.";
      $this->log()->notice($message);
      foreach ($plugins as $plugin) {
        $plugin_dir = $plugins_dir . $plugin;
        $git_dir = $plugin_dir . $slash . '.git';
        if (is_dir("$plugin_dir") && is_dir("$git_dir")) {
          $remotes = array();
          exec("cd \"$plugin_dir\" && git remote -v | xargs", $remotes);
          foreach ($remotes as $line) {
            $parts = explode(' ', $line);
            if (isset($parts[1])) {
              $repo = $parts[1];
              $parts = parse_url($repo);
              $path = explode('/', $parts['path']);
              $base = array_pop($path);
              $repository = $parts['scheme'] . '://' . $parts['host'] . implode('/', $path);
              if ($title = $this->isValidPlugin($repository, $base)) {
                $description = '';
                $parts = explode(':', $title);
                if (isset($parts[1])) {
                  $description = trim($parts[1]);
                }
                $rows[] = [
                  'name'        => $plugin,
                  'location'    => $repository,
                  'description' => $description,
                ];
              } else {
                $message = "$repo is not a valid plugin Git repository.";
                $this->log()->error($message);
              }
              break;
            } else {
              $message = "$plugin_dir is not a valid plugin Git repository.";
              $this->log()->error($message);
            }
          }
        }
      }
      // Output the plugin list in table format.
      $this->output()->outputRecordList($rows, $labels);
      $message = "Use 'terminus plugin install' to add more plugins.";
      $this->log()->notice($message);
    }
  }

  /**
   * Update plugin(s)
   *
   * @param array $args 'all' or a list of one or more installed plugin names
   *
   * @subcommand update
   * @alias up
   */
  public function update($args = array()) {
    if (empty($args)) {
      $message = "Usage: terminus plugin update | up all | plugin-name-1";
      $message .= " [plugin-name-2] ...";
      $this->failure($message);
    }

    if ($args[0] == 'all') {
      $plugins_dir = $this->getPluginDir();
      exec("ls \"$plugins_dir\"", $output);
      if (empty($output[0])) {
        $message = "No plugins installed.";
        $this->log()->notice($message);
      } else {
        foreach ($output as $plugin) {
          $this->updatePlugin($plugin);
        }
      }
    } else {
      foreach ($args as $arg) {
        $this->updatePlugin($arg);
      }
    }
  }

  /**
   * Remove plugin(s)
   *
   * @param array $args A list of one or more installed plugin names
   *
   * @subcommand uninstall
   * @alias remove
   */
  public function uninstall($args = array()) {
    if (empty($args)) {
      $message = "Usage: terminus plugin uninstall | remove plugin-name-1";
      $message .= " [plugin-name-2] ...";
      $this->failure($message);
    }

    foreach ($args as $arg) {
      $plugin = $this->getPluginDir($arg);
      if (!is_dir("$plugin")) {
        $message = "$arg plugin is not installed.";
        $this->log()->error($message);
      } else {
        exec("rm -rf \"$plugin\"", $output);
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
   * @param string $arg Plugin name
   * @return string Plugin directory
   */
  private function getPluginDir($arg = '') {
    $plugins_dir = getenv('TERMINUS_PLUGINS_DIR');
    $windows = Utils\isWindows();
    if (!$plugins_dir) {
      // Determine the correct $plugins_dir based on the operating system
      $home = getenv('HOME');
      if ($windows) {
        $system = '';
        if (getenv('MSYSTEM') !== null) {
          $system = strtoupper(substr(getenv('MSYSTEM'), 0, 4));
        }
        if ($system != 'MING') {
          $home = getenv('HOMEPATH');
        }
        $home = str_replace('\\', '\\\\', $home);
        $plugins_dir = $home . '\\\\terminus\\\\plugins\\\\';
      } else {
        $plugins_dir = $home . '/terminus/plugins/';
      }
    } else {
      // Make sure the proper trailing slash(es) exist
      if ($windows) {
        $slash = '\\\\';
        $chars = 2;
      } else {
        $slash = '/';
        $chars = 1;
      }
      if (substr("$plugins_dir", -$chars) != $slash) {
        $plugins_dir .= $slash;
      }
    }
    // Create the directory if it doesn't already exist
    if (!is_dir("$plugins_dir")) {
      mkdir("$plugins_dir", 0755, true);
    }
    return $plugins_dir . $arg;
  }

  /**
   * Update a specific plugin
   *
   * @param string $arg Plugin name
   */
  private function updatePlugin($arg) {
    $plugin = $this->getPluginDir($arg);
    if (is_dir("$plugin")) {
      $windows = Utils\isWindows();
      if ($windows) {
        $slash = '\\\\';
      } else {
        $slash = '/';
      }
      $git_dir = $plugin . $slash . '.git';
      $message = "Updating $arg plugin...";
      $this->log()->notice($message);
      if (!is_dir("$git_dir")) {
        $messages = array();
        $message = "Unable to update $arg plugin.";
        $message .= "  Git repository does not exist.";
        $messages[] = $message;
        $message = "The recommended way to install plugins";
        $message .= " is git clone <URL to plugin Git repository>.";
        $messages[] = $message;
        $message = "See https://github.com/pantheon-systems/terminus/";
        $message .= "wiki/Plugins.";
        $messages[] = $message;
        foreach ($messages as $message) {
          $this->log()->error($message);
        }
      } else {
        exec("cd \"$plugin\" && git pull", $output);
        foreach ($output as $message) {
          $this->log()->notice($message);
        }
      }
    }
  }

  /**
   * Check whether a plugin is valid
   *
   * @param string Repository URL
   * @param string Plugin name
   * @return string Plugin title, if found
   */
  private function isValidPlugin($repository, $plugin) {
    // Make sure the URL is valid
    $is_url = (filter_var($repository, FILTER_VALIDATE_URL) !== false);
    if (!$is_url) {
      return '';
    }
    // Make sure a subpath exists
    $parts = parse_url($repository);
    if (!isset($parts['path']) || ($parts['path'] == '/')) {
      return '';
    }
    // Search for a plugin title
    $plugin_data = @file_get_contents($repository . '/' . $plugin);
    if (!empty($plugin_data)) {
      preg_match('|<title>(.*)</title>|', $plugin_data, $match);
      if (isset($match[1])) {
        $title = $match[1];
        if (stripos($title, 'terminus') && stripos($title, 'plugin')) {
          return $title;
        }
        return '';
      }
      return '';
    }
    return '';
  }

  /**
   * Check whether a URL is valid
   *
   * @param string $url The URL to check
   * @return bool True if the URL returns a 200 status
   */
  private function isValidUrl($url = '') {
    if (!$url) {
      return false;
    }
    $headers = @get_headers($url);
    if (!isset($headers[0])) {
      return false;
    }
    return (strpos($headers[0], '200') !== false);
  }

}
