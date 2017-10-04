<?php

namespace Vendi\Cache;

use Assert\Assertion;
use League\Flysystem\{AdapterInterface, Filesystem};
use League\Flysystem\Adapter\Local;
use Monolog\Logger;
use Vendi\Cache\{CacheSettings, CacheMaster, VendiMonoLoggger};

final class Master
{
    private $_logger = null;

    private $_adapter = null;

    private $_cache_settings = null;

    private $_cache_master = null;

    private $_file_system = null;

    public function __construct( )
    {
        //NOOP
    }

    /**
     * Use the supplied logger.
     *
     * @param  Logger $logger The Monolog logger to log to
     * @return Master
     */
    public function with_logger( Logger $logger ) : self
    {
        $this->_logger = $logger;
        return $this;
    }

    /**
     * Use the supplied File System Adapter.
     *
     * @param  AdapterInterface $adapter The FlySystem Adapter to use.
     * @return Master
     */
    public function with_file_system_adapter( AdapterInterface $adapter ) : self
    {
        $this->_adapter = $adapter;
        return $this;
    }

    /**
     * Use the supplied cache settings.
     *
     * @param  CacheSettings $cache_settings The Cache Settings to use.
     * @return Master
     */
    public function with_cache_settings( CacheSettings $cache_settings ) : self
    {
        $this->_cache_settings = $cache_settings;
        return $this;
    }

    /**
     * Get the current or default logger.
     *
     * @return Logger
     */
    public function get_logger() : Logger
    {
        if( ! $this->_logger instanceof Logger )
        {
            $this->_logger = $this->get_default_logger();
        }

        return $this->_logger;
    }

    /**
     * Get the current or default FlySystem adapter
     *
     * @return AdapterInterface
     */
    public function get_adapter() : AdapterInterface
    {
        if( ! $this->_adapter instanceof AdapterInterface )
        {
            $this->_adapter = $this->get_default_adapter();
        }

        return $this->_adapter;
    }

    /**
     * Get the current or default Cache Settings.
     *
     * @return CacheSettings
     */
    public function get_cache_settings() : CacheSettings
    {
        if( ! $this->_cache_settings instanceof CacheSettings )
        {
            $this->_cache_settings = $this->get_default_cache_settings();
        }

        return $this->_cache_settings;
    }

    /**
     * Get the FlySystem using the current or default adapter.
     *
     * @return Filesystem
     */
    public function get_file_system() : Filesystem
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

    /**
     * Get an instance of Master with all defaults applied.
     *
     * @param  CacheSettings|null $cache_settings Optional. Custom CacheSettings.
     *
     * @return Master
     */
    public static function get_default_instance( CacheSettings $cache_settings = null ) : self
    {
        if( null === $cache_settings )
        {
            $cache_settings = self::get_default_cache_settings();
        }

        return ( new self() )
                ->with_cache_settings( $cache_settings )
                ->with_file_system_adapter( self::get_default_adapter( $cache_settings ) )
                ->with_logger( self::get_default_logger( $cache_settings ) )
            ;
    }

    /**
     * Get the CacheMaster object bound to all of this object's properties.
     *
     * NOTE: You must call with_cache_settings(), with_file_system_adapter() and
     * with_logger() before calling this method if you wish to override the
     * defaults.
     *
     * @return CacheMaster
     */
    public function get_cache_master() : CacheMaster
    {
        //Already setup, just return
        if( $this->_cache_master instanceof CacheMaster  )
        {
            return $this->_cache_master;
        }

        //Sanity check that we have things setup
        Assertion::noNull( $this->get_cache_settings() );
        Assertion::noNull( $this->get_adapter() );
        Assertion::noNull( $this->get_logger() );

        Assertion::isInstanceOf( $this->get_cache_settings(), CacheSettings::class );
        Assertion::isInstanceOf( $this->get_adapter(), '\League\Flysystem\AdapterInterface' );
        Assertion::isInstanceOf( $this->get_logger(), Logger::class );

        //Create and return our object bound to this
        $this->_cache_master = new CacheMaster( $this );

        return $this->_cache_master;
    }

    /**
     * Get the default cache settings.
     *
     * @return CacheSettings
     */
    public static function get_default_cache_settings() : CacheSettings
    {
        return CacheSettings::get_instance();
    }

    /**
     * Get the default FlySystem adapater using the supplied settings.
     *
     * @param  CacheSettings $cache_settings The settings to use for the adapter.
     * @return AdapterInterface
     */
    public static function get_default_adapter( CacheSettings $cache_settings ) : AdapterInterface
    {
        return new Local(
                            //The folder to cache to
                            $cache_setting->get_cache_folder_abs(),

                            //Use locks during write (default)
                            LOCK_EX,

                            //Throw exception on symlinks (default)
                            Local::DISALLOW_LINKS,

                            //Special file system permissions
                            $cache_setting->get_fs_permissions_for_cache()
                        );
    }

    /**
     * Get the default Monolog Logger using the supplied settings.
     *
     * @param  CacheSettings $cache_settings The settings to use for the adapter.
     * @return Logger
     */
    public static function get_default_logger( CacheSettings $cache_settings ) : Logger
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
