<?php
namespace Package\R3m\Io\Server\Trait;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use R3m\Io\Node\Model\Node;

use Exception;
trait Server {

    public function public_create($options): void
    {
        $object = $this->object();
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