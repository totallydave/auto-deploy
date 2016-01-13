<?php
/**
 * @package   AutoDeploy
 *
 * @copyright 2014 Totally Communications (http://www.totallycommunications.com)
 * @license   http://www.totallycommunications.com/license/bsd.txt New BSD License
 * @version   $Id:$
 */
namespace AutoDeploy\Service\Dm;

use AutoDeploy\Exception\InvalidArgumentException;
use Zend\Json\Json;

class Composer extends Service
{
    /**
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function run()
    {
        // get project root
        $projectRoot = $this->findProjectRoot();
        if (!$projectRoot) {
            $message = 'Could not determine project root directory';
            throw new InvalidArgumentException($message);
        }

        // swap to project root
        chdir($projectRoot);

        /**
         * @todo investigate a way to do this without writing to the filesystem
         */
        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'composer_update_log';

        if (file_exists($tmpFile)) {
            unlink($tmpFile);
        }

        // update composer
        exec("composer update 2>$tmpFile");

        if (file_exists($tmpFile)) {
            $composerUpdate = file_get_contents($tmpFile);
        }

        $log = "\nResult of composer update:\n";
        if (is_array($composerUpdate)) {
            $log .= implode("\n", $composerUpdate) . "\n";
        } elseif (is_string($composerUpdate)) {
            $log .= $composerUpdate . "\n";
        }

        $this->log = $log;
    }

    /**
     * Find root of project
     *
     * Starts in current folder and recursively works upwards looking
     * for .git/config
     *
     * @return string
     */
    protected function findProjectRoot()
    {
        $config = $this->getConfig();

        chdir(dirname(__DIR__));
        $dir = realpath(dirname(__DIR__));

        while ($dir) {
            $composerConfig = $dir . DIRECTORY_SEPARATOR
                            . 'composer.json';

            if (file_exists($composerConfig)) {
                $composerConfig = Json::decode(file_get_contents($composerConfig));

                if (!empty($composerConfig->name) && $composerConfig->name == $config['name']) {
                    echo "found composer.json at $dir";

                    break;
                }
            }

            // lets move up a level
            $dir = realpath($dir . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
            chdir($dir);
        }

        return $dir;
    }
}