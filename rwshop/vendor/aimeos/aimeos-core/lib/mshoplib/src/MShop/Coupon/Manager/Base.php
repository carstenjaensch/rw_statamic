<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2017
 * @package MShop
 * @subpackage Coupon
 */

namespace Aimeos\MShop\Coupon\Manager;


/**
 * Abstract class for coupon managers.
 *
 * @package MShop
 * @subpackage Coupon
 */
abstract class Base
	extends \Aimeos\MShop\Common\Manager\Base
{
	/**
	 * Returns the coupon model which belongs to the given code.
	 *
	 * @param \Aimeos\MShop\Coupon\Item\Iface $item Coupon item interface
	 * @param string $code Coupon code
	 * @return \Aimeos\MShop\Coupon\Provider\Iface Returns a coupon provider instance
	 * @throws \Aimeos\MShop\Coupon\Exception If coupon couldn't be found
	 */
	public function getProvider( \Aimeos\MShop\Coupon\Item\Iface $item, $code )
	{
		$names = explode( ',', $item->getProvider() );

		if( ( $providername = array_shift( $names ) ) === null ) {
			throw new \Aimeos\MShop\Coupon\Exception( sprintf( 'Provider in "%1$s" not available', $item->getProvider() ) );
		}

		if( ctype_alnum( $providername ) === false ) {
			throw new \Aimeos\MShop\Coupon\Exception( sprintf( 'Invalid characters in provider name "%1$s"', $providername ) );
		}

		$interface = '\\Aimeos\\MShop\\Coupon\\Provider\\Factory\\Iface';
		$classname = '\\Aimeos\\MShop\\Coupon\\Provider\\' . $providername;

		if( class_exists( $classname ) === false ) {
			throw new \Aimeos\MShop\Coupon\Exception( sprintf( 'Class "%1$s" not available', $classname ) );
		}

		$context = $this->getContext();
		$provider = new $classname( $context, $item, $code );

		if( ( $provider instanceof $interface ) === false )
		{
			$msg = sprintf( 'Class "%1$s" does not implement interface "%2$s"', $classname, $interface );
			throw new \Aimeos\MShop\Coupon\Exception( $msg );
		}

		/** mshop/coupon/provider/decorators
		 * Adds a list of decorators to all coupon provider objects automatcally
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap decorators
		 * ("\Aimeos\MShop\Coupon\Provider\Decorator\*") around the coupon provider.
		 *
		 *  mshop/coupon/provider/decorators = array( 'decorator1' )
		 *
		 * This would add the decorator named "decorator1" defined by
		 * "\Aimeos\MShop\Coupon\Provider\Decorator\Decorator1" to all coupon provider
		 * objects.
		 *
		 * @param array List of decorator names
		 * @since 2014.05
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/account/favorite/decorators/excludes
		 * @see client/html/account/favorite/decorators/local
		 */
		$decorators = $context->getConfig()->get( 'mshop/coupon/provider/decorators', [] );

		$object = $this->addCouponDecorators( $item, $code, $provider, $names );
		$object = $this->addCouponDecorators( $item, $code, $object, $decorators );

		return $object->setObject( $object );
	}


	/**
	 * Wraps the named coupon decorators around the coupon provider.
	 *
	 * @param \Aimeos\MShop\Coupon\Item\Iface $item Coupon item object
	 * @param string $code Coupon code
	 * @param \Aimeos\MShop\Coupon\Provider\Iface $provider Coupon provider object
	 * @param array $names List of decorator names
	 * @return \Aimeos\MShop\Coupon\Provider\Iface Coupon provider wrapped by one or more coupon decorators
	 * @throws \Aimeos\MShop\Coupon\Exception If a coupon decorator couldn't be instantiated
	 */
	protected function addCouponDecorators( \Aimeos\MShop\Coupon\Item\Iface $item, $code,
		\Aimeos\MShop\Coupon\Provider\Iface $provider, array $names )
	{
		$iface = '\\Aimeos\\MShop\\Coupon\\Provider\\Decorator\\Iface';
		$classprefix = '\\Aimeos\\MShop\\Coupon\\Provider\\Decorator\\';

		foreach( $names as $name )
		{
			if( ctype_alnum( $name ) === false ) {
				throw new \Aimeos\MShop\Coupon\Exception( sprintf( 'Invalid characters in class name "%1$s"', $name ) );
			}

			$classname = $classprefix . $name;

			if( class_exists( $classname ) === false ) {
				throw new \Aimeos\MShop\Coupon\Exception( sprintf( 'Class "%1$s" not available', $classname ) );
			}

			$provider = new $classname( $provider, $this->getContext(), $item, $code );

			if( ( $provider instanceof $iface ) === false ) {
				throw new \Aimeos\MShop\Coupon\Exception( sprintf( 'Class "%1$s" does not implement interface "%2$s"', $classname, $iface ) );
			}
		}

		return $provider;
	}
}