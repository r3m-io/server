<?php
namespace Package\R3m\Io\Server\Trait;

use R3m\Io\Config;
use R3m\Io\Module\Core;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Event;
use R3m\Io\Module\File;

use R3m\Io\Node\Model\Node;

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
        $destination = $options['public'];
        Dir::create($destination, Dir::CHMOD);
        $source = $object->config('controller.dir.data') . '.htaccess';
        $destination = $options['public'] . '.htaccess';
        File::copy($source, $destination);
        $source = $object->config('controller.dir.data') . '.user.ini';
        $destination = $options['public'] . '.user.ini';
        File::copy($source, $destination);
        $source = $object->config('controller.dir.data') . 'index.php';
        $destination = $options['public'] . 'index.php';
        File::copy($source, $destination);
        File::permission($object, [
            'public' => $options['public'],
            '.htaccess' => $options['public'] . '.htaccess',
            '.user.ini' => $options['public'] . '.user.ini', // <-- need parse
            'index.php' => $options['public'] . 'index.php',
        ]);
        $node = new Node($object);
        $class = 'System.Server';
        $response = $node->record($class, $node->role_system(), [
            'filter' => [
                '#class' => 'System.Server'
            ],
        ]);
        if(!$response){
            $record = (object) [
                'public' => $options['public'],
                '#class' => 'System.Server'
            ];
            $response = $node->create($class, $node->role_system(), $record);
            ddd($response);
            //create a system.server record
            //add system.server to system.config (relation)
        }
        elseif(
            is_array($response) &&
            array_key_exists('node', $response) &&
            is_object($response['node']) &&
            property_exists($response['node'], 'uuid')
        ){
            $config = $node->record('System.Config', $node->role_system(), []);
            d($config);
            ddd($response);
            //update system.server record
            //add system.server to system.config (relation)
        }


        ddd($response);

        //select server
        //update server.public && server.public_directory
    }
}