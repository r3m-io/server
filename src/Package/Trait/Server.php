<?php
namespace Package\R3m\Io\Server\Trait;

use R3m\Io\Config;
use R3m\Io\Module\Core;
use R3m\Io\Module\Dir;
use R3m\Io\Module\Event;
use R3m\Io\Module\File;

use R3m\Io\Module\Parse;
use R3m\Io\Node\Model\Node;

use Exception;

use R3m\Io\Exception\ObjectException;
use R3m\Io\Node\Service\Security;

trait Server {

    /**
     * @throws ObjectException
     * @throws Exception
     */
    public function public_create($options): ?string
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
            Event::trigger($object, 'r3m.io.server.public.create', [
                'options' => $options,
                'exception' => $exception
            ]);
            throw $exception;
        }
        $node = new Node($object);
        $class = 'System.Server';
        if (!array_key_exists('function', $options)) {
            $options['function'] = __FUNCTION__;
        }
        $options['relation'] = false;
        if (!Security::is_granted(
            $class,
            $node->role_system(),
            $options
        )) {
            return false;
        }
        if(
            !array_key_exists('public', $options) ||
            empty($options['public'])
        ){
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
        $parse = new Parse($object);
        $read = File::read($destination);
        $read = $parse->compile($read, $object->data());
        File::write($destination, $read);
        $source = $object->config('controller.dir.data') . 'index.php';
        $destination = $options['public'] . 'index.php';
        File::copy($source, $destination);
        File::permission($object, [
            'public' => $options['public'],
            '.htaccess' => $options['public'] . '.htaccess',
            '.user.ini' => $options['public'] . '.user.ini',
            'index.php' => $options['public'] . 'index.php',
        ]);
        $response = $node->record($class, $node->role_system());
        if(!$response){
            $record = (object) [
                'public' => $options['public'],
                '#class' => $class
            ];
            $response = $node->create($class, $node->role_system(), $record);
            $config = $this->system_config($node);
            if(
                $config &&
                is_array($config) &&
                array_key_exists('node', $config) &&
                is_object($config['node']) &&
                property_exists($config['node'], 'server') &&
                !empty($config['node']->server) &&
                $response &&
                is_array($response) &&
                array_key_exists('node', $response) &&
                is_object($response['node']) &&
                property_exists($response['node'], 'public') &&
                !empty($response['node']->public) &&
                Dir::is($response['node']->public)
            ){
                echo 'Server public directory (' . $response['node']->public .') configured (create)' . PHP_EOL;
                Event::trigger($object, 'r3m.io.server.public.create', [
                    'options' => $options,
                    'response' => $response
                ]);
                return null;
            }
        }
        elseif(
            is_array($response) &&
            array_key_exists('node', $response) &&
            is_object($response['node']) &&
            property_exists($response['node'], 'uuid')
        ){
            $config = $this->system_config($node);
            $record = (object) [
                'uuid' => $response['node']->uuid,
                'public' => $options['public'],
                '#class' => $class
            ];
            if(
                property_exists($response['node'], 'public') &&
                !empty($response['node']->public) &&
                Dir::is($response['node']->public) &&
                $record->public !== $response['node']->public
            ){
                Dir::remove($response['node']->public);
            }
            $response = $node->patch($class, $node->role_system(), $record);
            if(
                $config &&
                is_array($config) &&
                array_key_exists('node', $config) &&
                is_object($config['node']) &&
                property_exists($config['node'], 'server') &&
                !empty($config['node']->server) &&
                $response &&
                is_array($response) &&
                array_key_exists('node', $response) &&
                is_object($response['node']) &&
                property_exists($response['node'], 'public') &&
                !empty($response['node']->public) &&
                Dir::is($response['node']->public)
            ){
                echo 'Server public directory (' . $response['node']->public .') configured (patch)' . PHP_EOL;
                Event::trigger($object, 'r3m.io.server.public.create', [
                    'options' => $options,
                    'response' => $response
                ]);
                return null;
            }
            if(
                $response &&
                is_array($response) &&
                array_key_exists('error', $response)
            ){
                $result = Core::object($response, Core::OBJECT_JSON) . PHP_EOL;
                Event::trigger($object, 'r3m.io.server.public.create', [
                    'options' => $options,
                    'response' => $response
                ]);
                return $result;
            }
        }
        $exception = new Exception('Server public directory (' . $options['public'] .') not configured...');
        Event::trigger($object, 'r3m.io.server.public.create', [
            'options' => $options,
            'exception' => $exception
        ]);
        throw $exception;
    }

    public function system_config($node): ?array
    {
        $config = $node->record('System.Config', $node->role_system());
        if(
            $config &&
            is_array($config) &&
            array_key_exists('node', $config) &&
            property_exists($config['node'], 'uuid') &&
            !property_exists($config['node'], 'server')
        ){
            $patch = (object) [
                'uuid' => $config['node']->uuid,
                'server' => '*' //we have $response and can use the uuid too.
            ];
            $config = $node->patch('System.Config', $node->role_system(), $patch);
        }
        return $config;
    }
}