<?php
namespace ChefForms\Wrappers;

abstract class Wrapper {


    /**
     * The resolved object instances.
     *
     * @var array
     */
    protected static $resolvedInstances;

    /**
     * Each facade must define their igniter service
     * class key name.
     *
     * @throws \RuntimeException
     * @return string
     */
    protected static function getFacadeAccessor() {
        
        throw new \RuntimeException('Facade does not implement getFacadeAccessor method.');
    }

    /**
     * Retrieve the instance called by the igniter service.
     *
     * @return mixed
     */
    public static function getFacadeRoot() {
        /**
         * Grab the igniter service class and get the instance
         * called by the service.
         */
        return static::resolveFacadeInstance(static::getFacadeAccessor());
    }

    /**
     * Return a facade instance if one already exists. If not, keep a copy
     * of all instances and return the current called one.
     *
     * @param string $name
     * @return mixed
     */
    private static function resolveFacadeInstance($name) {

        if (is_object($name)) return $name;

        return static::getFacadeName( $name );
    }


    private static function getFacadeName( $name ) {
        
        $aliases = array(
            'form-manager'                  => 'ChefForms\Admin\FormManager',
            'form-builder'                  => 'ChefForms\Admin\FormBuilder',
            'entries-manager'               => 'ChefForms\Creators\EntriesManager',
            'form-creator'                  => 'ChefForms\Creators\FormCreator',
            'notification-creator'          => 'ChefForms\Creators\NotificationCreator',
            'settings-manager'              => 'ChefForms\Creators\SettingsManager',
            'field-block'                   => 'ChefForms\Creators\FieldCreator',
            'field'                         => 'ChefForms\Creators\FieldCreator',
            'settings-panel'                => 'ChefForms\Creators\SettingsPanel',
            'controls'                      => 'ChefForms\Creators\FieldControls',
            'form'                          => 'ChefForms\Front\Form',
            'notification'                  => 'ChefForms\Front\Notification',
        );

        return $aliases[ $name ];
    }


    /**
     * Clear a resolved facade instance.
     *
     * @param string $name
     * @return void
     */
    public static function clearResolvedInstance($name) {

        unset(static::$resolvedInstances[$name]);
    }

    /**
     * Clear all of the resolved instances.
     *
     * @return void
     */
    public static function clearResolvedInstances() {

        static::$resolvedInstances = array();
    }

    /**
     * Magic method. Use to dynamically call the registered
     * instance method.
     *
     * @param string $method The class method used.
     * @param array $args The method arguments.
     * @return mixed
     */
    public static function __callStatic($method, $args) {

        $instance = static::getFacadeRoot();
        $instance = new $instance();


        /**
         * Call the instance and its method.
         */
        return call_user_func_array(array($instance, $method), $args);
    }

} 