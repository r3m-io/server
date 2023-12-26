<?php
namespace Package\R3m\Io\Server\Trait;

use R3m\Io\Module\Core;

use Exception;

use R3m\Io\Exception\ObjectException;

trait Server {

    /**
     * @throws ObjectException
     */
    public function public_create($options): void
    {
        $object = $this->object();
        $options = Core::object($options, Core::OBJECT_ARRAY);

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