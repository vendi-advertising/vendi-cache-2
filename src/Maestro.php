<?php declare(strict_types=1);
namespace Vendi\Cache;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Vendi\Cache\Admin\UI;

final class Maestro
{
    private static $_default_instance = null;

    private $_logger = null;

    private $_secretary = null;

    private $_cache_master = null;

    private $_file_system = null;

    private $_request = null;

    private $_admin_ui = null;

    public function __construct()
    {
        //NOOP
    }

    /**
     * Use the supplied Vendi Admin UI..
     *
     * @param  Admin\UI $admin_ui The Vendi Admin UI to route admin requests
     * @return Maestro
     */
    public function with_admin_ui(UI $admin_ui)
    {
        $this->_admin_ui = $admin_ui;
        return $this;
    }

    /**
     * Use the supplied ServerRequestInterface.
     *
     * @param  ServerRequestInterface $request The PSR-7 Request object to base decisions off of
     * @return Maestro
     */
    public function with_request(ServerRequestInterface $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * Use the supplied logger.
     *
     * @param  LoggerInterface $logger PSR-4 logger to log to
     * @return Maestro
     */
    public function with_logger(LoggerInterface $logger)
    {
        $this->_logger = $logger;
        return $this;
    }

    /**
     * Use the supplied cache settings.
     *
     * @param  Secretary $cache_settings the Cache Settings to use
     * @return Maestro
     */
    public function with_secretary(Secretary $cache_settings)
    {
        $this->_secretary = $cache_settings;
        return $this;
    }

    public function with_file_system(AbstractFileSystem $file_system)
    {
        $this->_file_system = $file_system;
        return $this;
    }

    public function get_admin_ui($do_not_create_new = false)
    {
        if (! $this->_admin_ui instanceof UI) {
            if ($do_not_create_new) {
                throw new \Exception(sprintf(__('The property %1$s is null and the getter %2$s was requested to not generate a new one.', 'vendi-cache'), '_admin_ui', 'get_admin_ui'));
            }
            $this->_admin_ui = self::get_default_admin_ui($this);
        }

        return $this->_admin_ui;
    }

    /**
     * [get_request description].
     * @param  mixed                  $do_not_create_new
     * @return ServerRequestInterface
     */
    public function get_request($do_not_create_new = false)
    {
        if (! $this->_request instanceof ServerRequestInterface) {
            if ($do_not_create_new) {
                throw new \Exception(sprintf(__('The property %1$s is null and the getter %2$s was requested to not generate a new one.', 'vendi-cache'), '_request', 'get_request'));
            }
            $this->_request = self::get_default_request();
        }

        return $this->_request;
    }

    /**
     * Get the current or default logger.
     *
     * @param  mixed  $do_not_create_new
     * @return Logger
     */
    public function get_logger($do_not_create_new = false)
    {
        if (! $this->_logger instanceof LoggerInterface) {
            if ($do_not_create_new) {
                throw new \Exception(sprintf(__('The property %1$s is null and the getter %2$s was requested to not generate a new one.', 'vendi-cache'), '_logger', 'get_logger'));
            }
            $this->_logger = self::get_default_logger($this->get_secretary());
        }

        return $this->_logger;
    }

    /**
     * Get the current or default Cache Settings.
     *
     * @param  mixed     $do_not_create_new
     * @return Secretary
     */
    public function get_secretary($do_not_create_new = false)
    {
        if (! $this->_secretary instanceof Secretary) {
            if ($do_not_create_new) {
                throw new \Exception(sprintf(__('The property %1$s is null and the getter %2$s was requested to not generate a new one.', 'vendi-cache'), '_secretary', 'get_secretary'));
            }
            $this->_secretary = self::get_default_secretary();
        }

        return $this->_secretary;
    }

    /**
     * Get the CacheMaster object bound to all of this object's properties.
     *
     * @param  mixed       $do_not_create_new
     * @return CacheMaster
     */
    public function get_cache_master($do_not_create_new = false)
    {
        //Already setup, just return
        if (! $this->_cache_master instanceof CacheMaster) {
            if ($do_not_create_new) {
                throw new \Exception(sprintf(__('The property %1$s is null and the getter %2$s was requested to not generate a new one.', 'vendi-cache'), '_cache_master', 'get_cache_master'));
            }
            $this->_cache_master = new CacheMaster($this);
        }

        return $this->_cache_master;
    }

    public function get_file_system($do_not_create_new = false)
    {
        //Already setup, just return
        if (! $this->_file_system instanceof AbstractFileSystem) {
            if ($do_not_create_new) {
                throw new \Exception(sprintf(__('The property %1$s is null and the getter %2$s was requested to not generate a new one.', 'vendi-cache'), '_file_system', 'get_file_system'));
            }
            $this->_file_system = self::get_default_file_system($this);
        }

        return $this->_file_system;
    }

    public static function get_default_admin_ui(Maestro $maestro)
    {
        return new UI($maestro);
    }

    /**
     * [get_default_request description].
     * @return ServerRequestInterface
     */
    public static function get_default_request()
    {
        return VendiPsr7RequestMaker::create_default_request();
    }

    public static function get_default_file_system(Maestro $maestro)
    {
        return new FileSystem($maestro);
    }

    /**
     * Get an instance of Maestro with all defaults applied.
     *
     * @param Secretary|null $cache_settings Optional. Custom CacheSettings.
     *
     * @return Maestro
     */
    public static function get_default_instance(Secretary $cache_settings = null)
    {
        if (null === $cache_settings) {
            $cache_settings = self::get_default_secretary();
        }

        $ret = new self();
        return ( new self() )
                ->with_secretary($cache_settings)
                ->with_logger(self::get_default_logger($cache_settings))
                ->with_file_system(self::get_default_file_system($ret))
            ;
    }

    /**
     * Get the default cache settings.
     *
     * @return Secretary
     */
    public static function get_default_secretary()
    {
        return new Secretary();
    }

    /**
     * Get the default LoggerInterface using the supplied settings.
     *
     * @param  Secretary       $cache_settings the settings to use for the adapter
     * @return LoggerInterface
     */
    public static function get_default_logger(Secretary $cache_settings)
    {
        return new VendiPsr3Logger($cache_settings);
    }

    /**
     * Override magic method so that we don't use incorrect property names.
     * @param mixed $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        throw new \Exception(sprintf(__('Attempt at setting undeclared property %1$s.', 'vendi-cache'), esc_html($name)));
    }

    /**
     * Override magic method so that we don't use incorrect property names.
     * @param mixed $name
     */
    public function __get($name)
    {
        throw new \Exception(sprintf(__('Attempt at getting undeclared property %1$s.', 'vendi-cache'), esc_html($name)));
    }
}
