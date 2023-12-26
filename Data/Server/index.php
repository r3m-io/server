<?php
/**
 * @author          Remco van der Velde
 * @since           2020-10-27
 * @version         1.0
 * @changeLog
 *     -    all
 */

use R3m\Io\App;
use R3m\Io\Config;

use R3m\Io\Exception\LocateException;
use R3m\Io\Exception\ObjectException;

$dir = __DIR__;
$dir_vendor =
    dirname($dir, 1) .
    DIRECTORY_SEPARATOR .
    'vendor' .
    DIRECTORY_SEPARATOR;

$autoload = $dir_vendor . 'autoload.php';
$autoload = require $autoload;

try {
    $config = new Config(
        [
            'dir.vendor' => $dir_vendor
        ]
    );
    $app = new App($autoload, $config);
    $result = App::run($app);
    if(is_scalar($result)){
        echo $result;
    }
    elseif(is_array($result)) {
        echo implode(PHP_EOL, $result);
    }
} catch (Exception | LocateException | ObjectException $exception) {
    echo $exception;
}