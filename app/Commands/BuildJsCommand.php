<?php
/**
 * Created by PhpStorm.
 * User: exonintrendo
 * Date: 9/28/14
 * Time: 12:55 PM
 */

class BuildJsCommand extends \Primer\Console\BaseCommand
{
    private $_jsPath;

    public function configure()
    {
        $this->setDescription("Compile JavaScript using r.js");
        $this->_jsPath = APP_ROOT . '/public/js';
    }

    public function run()
    {
        $buildTemplate = <<<__JS__
({
      name: 'main',
      baseUrl: './',
      mainConfigFile: './main.js',
      out: './main.min.js',
      optimize: 'uglify2',
      preserveLicenseComments: false,
      wrap: true,
      paths : {
        'js_config' : 'empty:'
      }
})
__JS__;

        $mainTemplate = file_get_contents($this->_jsPath . '/main.js');
        rename($this->_jsPath . '/main.js', $this->_jsPath . '/main.js.template');

        $buildJs = $buildTemplate;
        $mainJs = $mainTemplate;

        file_put_contents($this->_jsPath . '/build.js', $buildJs);
        file_put_contents($this->_jsPath . '/main.js', $mainJs);

        $this->compile($this->_jsPath . '/build.js', $this->_jsPath . '/main.js');

        rename($this->_jsPath . '/main.js.template', $this->_jsPath . '/main.js');
        unlink($this->_jsPath . '/build.js');
    }

    private function compile($buildJs, $builtJs)
    {
        $cmd = "r.js -o $buildJs";
        $output = shell_exec($cmd);

        $lines = explode("\n", $output);

        $uncompressed = 0;
        $numFiles = 0;
        $foundHr = false;

        foreach ($lines as $line) {
            if (preg_match('#----------#', $line)) {
                $foundHr = true;
                continue;
            }

            if (!$foundHr || !$line) {
                continue;
            }

            $numFiles++;
            $size = filesize($line);

            $shortFile = str_replace(getcwd(), '', $line);
            $shortFile = str_replace($this->_jsPath, '', $shortFile);

            printf("%-60s %8d\n", $shortFile, $size);

            $uncompressed += $size;
        }

        $compressedSize = filesize($builtJs);

        $percentage = (($uncompressed - $compressedSize) / ($uncompressed + 0.0)) * 100;

        echo "Uncompressed: $numFiles files, $uncompressed bytes\n";
        echo "Compressed: 1 file, $compressedSize bytes ($percentage% reduction)\n";
    }
}