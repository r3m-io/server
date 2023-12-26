<?php
namespace Package\R3m\Io\Server\Trait;

use R3m\Io\Config;
use R3m\Io\Module\Core;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Event;

use Exception;

use R3m\Io\Exception\ObjectException;

trait Server {

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public function public_create($options): void
    {
        $object = $this->object();
        $options = Core::object($options, Core::OBJECT_ARRAY);
        $id = $object->config(Config::POSIX_ID);
        if(
            !in_array(
                $id,
                [
                    0,
                    33
                ],
                true
            )
        ){
            $exception = new Exception('Only root and after that www-data can configure public create...');
            Event::trigger($object, 'cli.configure.public.create', [
                'options' => $options,
                'exception' => $exception
            ]);
            throw $exception;
        }
        if(!array_key_exists('public', $options)){
            $options['public'] = $object->config('project.dir.public');
        }
        if(strstr($options['public'], '/') === false){
            $options['public'] = $object->config('project.dir.root') . $options['public'] . $object->config('ds');
        }
        $source = $object->config('controller.dir.data') . 'Server' . $object->config('ds');
        $destination = $options['public'];
        Dir::create($destination, Dir::CHMOD);
        Dir::copy($source, $destination);
        File::permission($object, [
            'destination' => $destination,
            '.htaccess' => $destination . '.htaccess',
            '.user.ini' => $destination . '.user.ini',
            'index.php' => $destination . 'index.php',
        ]);
d($object->config('controller.dir.data'));
//        d($object->config('project'));
        ddd($options);
        /*
        $package = $object->request('package');
        if($package){
            $node = new Node($object);
            $node->role_system_create($package);
        }
        */
    }
}