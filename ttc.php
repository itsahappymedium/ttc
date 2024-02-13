<?php
use splitbrain\phpcli\CLI;
use Symfony\Component\Yaml\Yaml;

class TTC extends CLI {
  protected $arguments = array();
  protected $files = array();
  protected $template_dir = null;

  protected function setup($options) {
    $options->setHelp('A PHP Command Line tool for compiling Twig template files.');

    $options->registerOption('arguments-file', 'A file to load twig arguments/variables from.', 'f', true);
    $options->registerOption('arguments-file-type', 'The type of file `arguments-file` is. (Possible values: `env`,`json`,`yml`)', 't', true);
    $options->registerOption('arguments-file-base', 'A dot-notation path inside of `arguments-file` where the arguments/variables should be loaded from.', 'b', true);
    $options->registerOption('destination', 'The directory to place the generated files in.', 'd', true);
    $options->registerOption('input', 'An individual twig file to compile.', 'i', true);
    $options->registerOption('output', 'The path where the individual compiled file should go.', 'o', true);
    $options->registerOption('source', 'The directory where the twig files are.', 's', true);

    $options->registerOption('recursive', 'Sets whether or not the `source` directory should be searched recursively.', 'r', false);
  }

  protected function main($options) {
    $this->setup_opts($options);
    $this->compile();
  }

  protected function compile() {
    foreach($this->files as $file) {
      $input = $file['input'];
      $output = $file['output'];
      $input_path = substr($input, strlen($this->template_dir));

      $loader = new \Twig\Loader\FilesystemLoader($this->template_dir);
      $twig = new \Twig\Environment($loader);

      try {
        $this->print(" - <cyan>Compiling</cyan> <brown>{$input}</brown> to <brown>{$output}</brown>...");
        $template = $twig->render($input_path, $this->arguments);

        if (!is_dir(dirname($output))) {
          mkdir(dirname($output), 0777, true);
        }

        file_put_contents($output, $template);
      } catch(Exception $e) {
        $error_message = $e->getMessage();
        $this->print("<lightred>Error</lightred>: {$error_message}", STDERR);
        exit(1);
      }
    }

    $this->print('Done!');
  }

  protected function glob_recursive($pattern, $flags = null) {
    $files = glob($pattern, $flags); 

    foreach(glob(dirname($pattern) . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
      $files = array_merge(
        [],
        ...[$files, $this->glob_recursive($dir . DIRECTORY_SEPARATOR . basename($pattern), $flags)]
      );
    }

    return $files;
  }

  protected function print($text, $channel = STDOUT) {
    $active_colors = array();

    $text = preg_replace_callback('/\<(.[^\>]*?)\>/', function($matches) use(&$active_colors) {
      $new_color = $matches[1];
      $colors = array();

      if (substr($new_color, 0, 1) === '/') {
        array_pop($active_colors);
        $colors[] = 'reset';
      } else {
        $active_colors[] = $new_color;
      }

      $colors = array_merge($colors, $active_colors);

      return implode('', array_map(function($color) {
        return $this->colors->getColorCode($color);
      }, $colors));
    }, $text);

    fwrite($channel, $text . "\n");

    if (end($active_colors) !== 'reset') {
      $this->colors->reset($channel);
    }
  }

  protected function setup_opts($options) {
    $arguments_file = $options->getOpt('arguments-file');
    $arguments_file_base = $options->getOpt('arguments-file-base');
    $arguments_file_type = $options->getOpt('arguments-file-type');
    $destination = $options->getOpt('destination');
    $input = $options->getOpt('input');
    $output = $options->getOpt('output');
    $recursive = $options->getOpt('recursive');
    $source = $options->getOpt('source');

    if ($arguments_file) {
      if (file_exists($arguments_file)) {
        $arguments = array();

        if (!$arguments_file_type) $arguments_file_type = pathinfo($arguments_file, PATHINFO_EXTENSION);

        try {
          $arguments_file_contents = file_get_contents($arguments_file);
        } catch(Exception $e) {
          $error_message = $e->getMessage();
          $this->print("<lightred>Error</lightred>: {$error_message}", STDERR);
          exit(1);
        }

        switch($arguments_file_type) {
          case 'env':
            foreach($arguments_file_contents as $arg) {
              preg_match("/([^#]+)\=(.*)/", $arg, $arg_matches);
              $arguments[$arg_matches[1]] = $arg_matches[2];
            }
            break;
          case 'json':
            $arguments = json_decode($arguments_file_contents, true);
            break;
          case 'yaml':
          case 'yml':
            $arguments = Yaml::parse($arguments_file_contents);
            break;
          default:
            $this->print("<lightred>Error</lightred>: Invalid arguments-file-type <yellow>{$arguments_file_type}</yellow>.", STDERR);
            exit(1);
        }

        if ($arguments_file_base) {
          $arguments_file_base_path = explode('.', $arguments_file_base);

          foreach($arguments_file_base_path as $base_path) {
            if (isset($arguments[$base_path])) {
              $arguments = $arguments[$base_path];
            } else {
              $this->print("<lightred>Error</lightred>: Invalid arguments-file-base <yellow>{$arguments_file_base}</yellow>.", STDERR);
              exit(1);
            }
          }
        }

        $this->arguments = $arguments;
      } else {
        $this->print("<lightred>Error</lightred>: <yellow>{$arguments_file}</yellow> does not exist.", STDERR);
        exit(1);
      }
    }

    if ($source) {
      $source = rtrim($source, DIRECTORY_SEPARATOR);
      $destination = $destination ? rtrim($destination, DIRECTORY_SEPARATOR) : $source;
      $this->template_dir = $source;
      $glob_pattern = $source . DIRECTORY_SEPARATOR . '*.twig';
      $files = $recursive ? $this->glob_recursive($glob_pattern) : glob($glob_pattern);
      $this->files = array_map(function($file) use($destination) {
        return array(
          'input'   => $file,
          'output'  => $destination . DIRECTORY_SEPARATOR . ltrim(substr($file, strlen($this->template_dir), -5), DIRECTORY_SEPARATOR)
        );
      }, $files);
    }

    if ($input) {
      if (!$output) $output = preg_replace('/\.twig$/', '', $input);
      $this->template_dir = dirname($input);
      $this->files[] = compact('input', 'output');
    }
  }
}

$cli = new TTC();
$cli->run();
?>