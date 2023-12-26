<?php
namespace Package\R3m\Io\Server\Trait;

use R3m\Io\Config;
use R3m\Io\Module\Core;
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
            $options['public'] = 'Public';
        }
        d($object->config('project'));
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