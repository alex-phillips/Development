<?php
/**
 * @author Alex Phillips <exonintrendo@gmail.com>
 * Date: 10/2/14
 * Time: 8:11 PM
 */
class MonitorCommand extends \Primer\Console\BaseCommand
{
    private $_watchPath;
    private $_commandPath;
    private $_command;

    private $_files = array();
    private $_changes = false;

    public function configure()
    {
        $this->setName('monitor');
        $this->setDescription("Monitors a directory and runs a specified command when files change");
        $this->addParameter('w', 'watch', \Primer\Console\Input\DefinedInput::VALUE_REQUIRED);
        $this->addParameter('p', 'path', \Primer\Console\Input\DefinedInput::VALUE_REQUIRED);
        $this->addParameter('c', 'command', \Primer\Console\Input\DefinedInput::VALUE_REQUIRED);
    }

    public function run()
    {
        $this->_watchPath = rtrim($this->getParameterValue('w'), '/') . '/';
        $this->_commandPath = rtrim($this->getParameterValue('p'), '/') . '/';
        $this->_command = $this->getParameterValue('c');

        $this->init();

        while (true) {
            $this->checkFiles();
            sleep(1);
        }
    }

    private function init()
    {
        // Get files initial md5
        $path = realpath($this->_watchPath);
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($files as $file) {
            /* @var $file SplFileInfo */
            $pathName = $file->getPath();
            $fileName = $file->getFilename();
            if ($fileName === '.' || $fileName === '..' || is_dir($pathName . '/' . $fileName)) {
                continue;
            }
            $this->_files[$pathName . '/' . $fileName] = md5_file($pathName . '/' . $fileName);
        }

        echo "\nStarting\n";
        echo "Monitoring $this->_watchPath to run $this->_command...\n";
    }

    private function checkFiles()
    {
        $this->_changes = false;
        $xary = array();
        $path = realpath($this->_watchPath);
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($files as $file) {
            /* @var $file SplFileInfo */
            $pathName = $file->getPath();
            $fileName = $file->getFilename();
            $fullPath = $pathName . '/' . $fileName;
            if ($fileName === '.' || $fileName === '..' || is_dir($fullPath)) {
                continue;
            }
            if (array_key_exists($fullPath, $this->_files)) {
                if ($this->_files[$fullPath] !== md5_file($fullPath)) {
                    $this->_changes = true;
                    $this->_files[$fullPath] = md5_file($fullPath);
                    echo "\n >> File $fileName has changed\n";
                }
                $xary[$fullPath] = $this->_files[$fullPath];
                unset($this->_files[$fullPath]);
            }
            else {
                $this->_changes = true;
                $xary[$fullPath] = md5_file($fullPath);
                echo "\n >> New file $fileName\n";
            }
        }
        foreach ($this->_files as $deleted => $hash) {
            echo "\n >> Deleted file $deleted\n";
            $this->_changes = true;
        }

        if ($this->_changes == true) {
            echo "Running {$this->_command}\n";
            `cd {$this->_commandPath}; {$this->_command};`;
            echo "Done.\n\n";
        }

        $this->_files = $xary;
    }
}