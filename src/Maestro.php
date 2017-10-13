<?php

namespace Vendi\Cache;

use Assert\Assertion;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Vendi\Cache\CacheMaster;
use Vendi\Cache\CacheSettings;
use Vendi\Cache\Secretary;
use Vendi\Cache\DefaultUpdater;
use Vendi\Cache\UpdaterInterface;
use Vendi\Cache\VendiMonoLoggger;
use Vendi\Cache\Admin\UI;

final class Maestro
{
    private static $_default_instance = null;

    private $_logger = null;

    private $_adapter = null;

    private $_secretary = null;

    private $_cache_master = null;

    private $_file_system = null;

    private $_request = null;

    private $_admin_ui = null;

    public function __construct( )
    {
        //NOOP
    }

    /**
     * Use the supplied Vendi Admin UI..
     *
     * @param  Admin\UI $admin_ui The Vendi Admin UI to route admin requests
     * @return Maestro
     */
    public function with_admin_ui( UI $admin_ui  )
    {
        $this->_admin_ui = $admin_ui;
        return $this;
    }

    /**
     * Use the supplied Request.
     *
     * @param  Request $logger The Symfony Request object to base decisions off of
     * @return Maestro
     */
    public function with_request( Request $request )
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
    public function with_logger( LoggerInterface $logger )
    {
        $this->_logger = $logger;
        return $this;
    }

    /**
     * Use the supplied File System Adapter.
     *
     * @param  AdapterInterface $adapter The FlySystem Adapter to use.
     * @return Maestro
     */
    public function with_file_system_adapter( AdapterInterface $adapter )
    {
        $this->_adapter = $adapter;
        return $this;
    }

    /**
     * Use the supplied cache settings.
     *
     * @param  Secretary $cache_settings The Cache Settings to use.
     * @return Maestro
     */
    public function with_secretary( Secretary $cache_settings )
    {
        $this->_secretary = $cache_settings;
        return $this;
    }

    public function get_admin_ui()
    {
        if( ! $this->_admin_ui instanceof UI )
        {
            $this->_admin_ui = self::get_default_admin_ui( $this );
        }

        return $this->_admin_ui;
    }

    /**
     * [get_request description]
     * @return Request
     */
    public function get_request()
    {
        if( ! $this->_request instanceof Request )
        {
            $this->_request = self::get_default_request();
        }

        return $this->_request;
    }

    /**
     * Get the current or default logger.
     *
     * @return Logger
     */
    public function get_logger()
    {
        if( ! $this->_logger instanceof LoggerInterface )
        {
            $this->_logger = self::get_default_logger( $this->get_secretary() );
        }

        return $this->_logger;
    }

    /**
     * Get the current or default FlySystem adapter
     *
     * @return AdapterInterface
     */
    public function get_adapter()
    {
        if( ! $this->_adapter instanceof AdapterInterface )
        {
            $this->_adapter = self::get_default_adapter( $this->get_secretary() );
        }

        return $this->_adapter;
    }

    /**
     * Get the current or default Cache Settings.
     *
     * @return Secretary
     */
    public function get_secretary()
    {
        if( ! $this->_secretary instanceof Secretary )
        {
            $this->_secretary = self::get_default_secretary();
        }

        return $this->_secretary;
    }

    /**
     * Get the FlySystem using the current or default adapter.
     *
     * @return Filesystem
     */
    public function get_file_system()
    {
        if( ! $this->_file_system instanceof Filesystem )
        {
            $this->_file_system = new Filesystem(
                                                    $this->get_adapter(),
                                                    [
                                                        'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
                                                    ]
                                                );
        }

        return $this->_file_system;
    }

    public static function get_default_admin_ui( Maestro $maestro )
    {
        return new UI( $maestro );
    }

    /**
     * [get_default_request description]
     * @return Request
     */
    public static function get_default_request()
    {
        return Request::createFromGlobals();
    }

    /**
     * Get an instance of Maestro with all defaults applied.
     *
     * @param  Secretary|null $cache_settings Optional. Custom CacheSettings.
     *
     * @return Maestro
     */
    public static function get_default_instance( Secretary $cache_settings = null )
    {
        if( null === $cache_settings )
        {
            $cache_settings = self::get_default_secretary();
        }

        return ( new self() )
                ->with_secretary( $cache_settings )
                ->with_file_system_adapter( self::get_default_adapter( $cache_settings ) )
                ->with_logger( self::get_default_logger( $cache_settings ) )
            ;
    }

    /**
     * Get the CacheMaster object bound to all of this object's properties.
     *
     * NOTE: You must call with_secretary(), with_file_system_adapter() and
     * with_logger() before calling this method if you wish to override the
     * defaults.
     *
     * @return CacheMaster
     */
    public function get_cache_master()
    {
        //Already setup, just return
        if( $this->_cache_master instanceof CacheMaster  )
        {
            return $this->_cache_master;
        }

        //Sanity check that we have things setup
        Assertion::notNull( $this->get_secretary() );
        Assertion::notNull( $this->get_adapter() );
        Assertion::notNull( $this->get_logger() );

        Assertion::isInstanceOf( $this->get_secretary(), 'Vendi\Cache\Secretary' );
        Assertion::isInstanceOf( $this->get_adapter(),   'League\Flysystem\AdapterInterface' );
        Assertion::isInstanceOf( $this->get_logger(),    'Psr\Log\LoggerInterface'  );

        //Create and return our object bound to this
        $this->_cache_master = new CacheMaster( $this );

        return $this->_cache_master;
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
     * Get the default FlySystem adapater using the supplied settings.
     *
     * @param  CacheSettings $cache_settings The settings to use for the adapter.
     * @return AdapterInterface
     */
    public static function get_default_adapter( Secretary $cache_settings )
    {
        return new Local(
                            //The folder to cache to
                            $cache_settings->get_cache_folder_abs(),

                            //Use locks during write (default)
                            LOCK_EX,

                            //Throw exception on symlinks (default)
                            Local::DISALLOW_LINKS,

                            //Special file system permissions
                            $cache_settings->get_fs_permissions_for_cache()
                        );
    }

    /**
     * Get the default LoggerInterface using the supplied settings.
     *
     * @param  Secretary $cache_settings The settings to use for the adapter.
     * @return LoggerInterface
     */
    public static function get_default_logger( Secretary $cache_settings )
    {
        return new VendiMonoLoggger( $cache_settings );
    }

    /**
     * Override magic method so that we don't use incorrect property names.
     */
    public function __set( $name, $value )
    {
        throw new \Exception( sprintf( __( 'Attempt at setting undeclared property %1$s.', 'vendi-cache' ), esc_html( $name ) ) );
    }

    /**
     * Override magic method so that we don't use incorrect property names.
     */
    public function __get( $name )
    {
        throw new \Exception( sprintf( __( 'Attempt at getting undeclared property %1$s.', 'vendi-cache' ), esc_html( $name ) ) );
    }
}
