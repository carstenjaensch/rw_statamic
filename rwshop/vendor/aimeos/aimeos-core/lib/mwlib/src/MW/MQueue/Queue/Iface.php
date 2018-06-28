<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2016-2017
 * @package MW
 * @subpackage MQueue
 */


namespace Aimeos\MW\MQueue\Queue;


/**
 * Common interface for all queue implementations
 *
 * @package MW
 * @subpackage MQueue
 */
interface Iface
{
	/**
	 * Adds a new message to the message queue
	 *
	 * @param string $msg Message, e.g. JSON encoded data
	 * @return null
	 */
	public function add( $msg );

	/**
	 * Returns the next message from the queue
	 *
	 * Don't forget to remove the message after processing it using the del()
	 * method. Otherwise, it will be handed over in the next call to get() again!
	 *
	 * @return \Aimeos\MW\MQueue\Message\Iface|null Message object or null if none is available
	 */
	public function get();

	/**
	 * Removes the message from the queue
	 *
	 * After processing the message, it must be removed from the queue so it won't
	 * be handed over in the next call to get():
	 *
	 *  $msg = $queue->get();
	 *  // process message
	 *  $queue->del( $msg );
	 *
	 * @param \Aimeos\MW\MQueue\Message\Iface $msg Message object
	 * @return null
	 */
	public function del( \Aimeos\MW\MQueue\Message\Iface $msg );
}