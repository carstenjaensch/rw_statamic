<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2017
 * @package Admin
 * @subpackage JQAdm
 */


namespace Aimeos\Admin\JQAdm\Locale\Site;

sprintf( 'locale/site' ); // for translation


/**
 * Default implementation of locale site JQAdm client.
 *
 * @package Admin
 * @subpackage JQAdm
 */
class Standard
	extends \Aimeos\Admin\JQAdm\Common\Admin\Factory\Base
	implements \Aimeos\Admin\JQAdm\Common\Admin\Factory\Iface
{
	/** admin/jqadm/locale/site/standard/subparts
	 * List of JQAdm sub-clients rendered within the locale section
	 *
	 * The output of the frontend is composed of the code generated by the JQAdm
	 * clients. Each JQAdm client can consist of serveral (or none) sub-clients
	 * that are responsible for rendering certain sub-parts of the output. The
	 * sub-clients can contain JQAdm clients themselves and therefore a
	 * hierarchical tree of JQAdm clients is composed. Each JQAdm client creates
	 * the output that is placed inside the container of its parent.
	 *
	 * At first, always the JQAdm code generated by the parent is printed, then
	 * the JQAdm code of its sub-clients. The order of the JQAdm sub-clients
	 * determines the order of the output of these sub-clients inside the parent
	 * container. If the configured list of clients is
	 *
	 *  array( "subclient1", "subclient2" )
	 *
	 * you can easily change the order of the output by reordering the subparts:
	 *
	 *  admin/jqadm/<clients>/subparts = array( "subclient1", "subclient2" )
	 *
	 * You can also remove one or more parts if they shouldn't be rendered:
	 *
	 *  admin/jqadm/<clients>/subparts = array( "subclient1" )
	 *
	 * As the clients only generates structural JQAdm, the layout defined via CSS
	 * should support adding, removing or reordering content by a fluid like
	 * design.
	 *
	 * @param array List of sub-client names
	 * @since 2017.10
	 * @category Developer
	 */
	private $subPartPath = 'admin/jqadm/locale/site/standard/subparts';
	private $subPartNames = [];


	/**
	 * Copies a resource
	 *
	 * @return string HTML output
	 */
	public function copy()
	{
		$view = $this->getView();
		$context = $this->getContext();

		try
		{
			if( ( $id = $view->param( 'id' ) ) === null ) {
				throw new \Aimeos\Admin\JQAdm\Exception( sprintf( 'Required parameter "%1$s" is missing', 'id' ) );
			}

			$this->checkSite( $view->access( 'super' ), $id );

			$manager = \Aimeos\MShop\Factory::createManager( $context, 'locale/site' );
			$view->item = $manager->getItem( $id );

			$view->itemData = $this->toArray( $view->item, true );
			$view->itemSubparts = $this->getSubClientNames();
			$view->itemBody = '';

			foreach( $this->getSubClients() as $idx => $client )
			{
				$view->tabindex = ++$idx + 1;
				$view->itemBody .= $client->copy();
			}
		}
		catch( \Aimeos\Admin\JQAdm\Exception $e )
		{
			$error = array( 'locale-site-item' => $context->getI18n()->dt( 'admin', $e->getMessage() ) );
			$view->errors = $view->get( 'errors', [] ) + $error;
			$this->logException( $e );
		}
		catch( \Aimeos\MShop\Exception $e )
		{
			$error = array( 'locale-site-item' => $context->getI18n()->dt( 'mshop', $e->getMessage() ) );
			$view->errors = $view->get( 'errors', [] ) + $error;
			$this->logException( $e );
		}
		catch( \Exception $e )
		{
			$error = array( 'locale-site-item' => $e->getMessage() . ', ' . $e->getFile() . ':' . $e->getLine() );
			$view->errors = $view->get( 'errors', [] ) + $error;
			$this->logException( $e );
		}

		return $this->render( $view );
	}


	/**
	 * Creates a new resource
	 *
	 * @return string HTML output
	 */
	public function create()
	{
		$view = $this->getView();
		$context = $this->getContext();

		try
		{
			$this->checkSite( $view->access( 'super' ) );

			$data = $view->param( 'item', [] );

			if( !isset( $view->item ) ) {
				$view->item = \Aimeos\MShop\Factory::createManager( $context, 'locale/site' )->createItem();
			} else {
				$data = $this->toArray( $view->item );
			}

			$view->itemSubparts = $this->getSubClientNames();
			$view->itemData = $data;
			$view->itemBody = '';

			foreach( $this->getSubClients() as $idx => $client )
			{
				$view->tabindex = ++$idx + 1;
				$view->itemBody .= $client->create();
			}
		}
		catch( \Aimeos\Admin\JQAdm\Exception $e )
		{
			$error = array( 'locale-site-item' => $context->getI18n()->dt( 'admin', $e->getMessage() ) );
			$view->errors = $view->get( 'errors', [] ) + $error;
			$this->logException( $e );
		}
		catch( \Aimeos\MShop\Exception $e )
		{
			$error = array( 'locale-site-item' => $context->getI18n()->dt( 'mshop', $e->getMessage() ) );
			$view->errors = $view->get( 'errors', [] ) + $error;
			$this->logException( $e );
		}
		catch( \Exception $e )
		{
			$error = array( 'locale-site-item' => $e->getMessage() . ', ' . $e->getFile() . ':' . $e->getLine() );
			$view->errors = $view->get( 'errors', [] ) + $error;
			$this->logException( $e );
		}

		return $this->render( $view );
	}


	/**
	 * Deletes a resource
	 *
	 * @return string|null HTML output
	 */
	public function delete()
	{
		$view = $this->getView();
		$context = $this->getContext();

		$manager = \Aimeos\MShop\Factory::createManager( $context, 'locale/site' );
		$manager->begin();

		try
		{
			if( ( $id = $view->param( 'id' ) ) === null ) {
				throw new \Aimeos\Admin\JQAdm\Exception( sprintf( 'Required parameter "%1$s" is missing', 'id' ) );
			}

			$this->checkSite( $view->access( 'super' ), $id );

			$view->item = $manager->getItem( $id );

			foreach( $this->getSubClients() as $client ) {
				$client->delete();
			}

			$manager->deleteItem( $id );
			$manager->commit();

			$this->nextAction( $view, 'search', 'locale/site', null, 'delete' );
			return;
		}
		catch( \Aimeos\Admin\JQAdm\Exception $e )
		{
			$error = array( 'locale-site-item' => $context->getI18n()->dt( 'admin', $e->getMessage() ) );
			$view->errors = $view->get( 'errors', [] ) + $error;
			$this->logException( $e );
		}
		catch( \Aimeos\MShop\Exception $e )
		{
			$error = array( 'locale-site-item' => $context->getI18n()->dt( 'mshop', $e->getMessage() ) );
			$view->errors = $view->get( 'errors', [] ) + $error;
			$this->logException( $e );
		}
		catch( \Exception $e )
		{
			$error = array( 'locale-site-item' => $e->getMessage() . ', ' . $e->getFile() . ':' . $e->getLine() );
			$view->errors = $view->get( 'errors', [] ) + $error;
			$this->logException( $e );
		}

		$manager->rollback();

		return $this->search();
	}


	/**
	 * Returns a single resource
	 *
	 * @return string HTML output
	 */
	public function get()
	{
		$view = $this->getView();
		$context = $this->getContext();

		try
		{
			if( ( $id = $view->param( 'id' ) ) === null ) {
				throw new \Aimeos\Admin\JQAdm\Exception( sprintf( 'Required parameter "%1$s" is missing', 'id' ) );
			}

			$this->checkSite( $view->access( 'super' ), $id );

			$manager = \Aimeos\MShop\Factory::createManager( $context, 'locale/site' );

			$view->item = $manager->getItem( $id );
			$view->itemSubparts = $this->getSubClientNames();
			$view->itemData = $this->toArray( $view->item );
			$view->itemBody = '';

			foreach( $this->getSubClients() as $idx => $client )
			{
				$view->tabindex = ++$idx + 1;
				$view->itemBody .= $client->get();
			}
		}
		catch( \Aimeos\Admin\JQAdm\Exception $e )
		{
			$error = array( 'locale-site-item' => $context->getI18n()->dt( 'admin', $e->getMessage() ) );
			$view->errors = $view->get( 'errors', [] ) + $error;
			$this->logException( $e );
		}
		catch( \Aimeos\MShop\Exception $e )
		{
			$error = array( 'locale-site-item' => $context->getI18n()->dt( 'mshop', $e->getMessage() ) );
			$view->errors = $view->get( 'errors', [] ) + $error;
			$this->logException( $e );
		}
		catch( \Exception $e )
		{
			$error = array( 'locale-site-item' => $e->getMessage() . ', ' . $e->getFile() . ':' . $e->getLine() );
			$view->errors = $view->get( 'errors', [] ) + $error;
			$this->logException( $e );
		}

		return $this->render( $view );
	}


	/**
	 * Saves the data
	 *
	 * @return string HTML output
	 */
	public function save()
	{
		$view = $this->getView();
		$context = $this->getContext();

		$manager = \Aimeos\MShop\Factory::createManager( $context, 'locale/site' );
		$manager->begin();

		try
		{
			$view->item = $this->fromArray( $view->param( 'item', [] ), $view->access( 'super' ) );
			$view->itemBody = '';

			foreach( $this->getSubClients() as $client ) {
				$view->itemBody .= $client->save();
			}

			$manager->commit();

			$this->nextAction( $view, $view->param( 'next' ), 'locale/site', $view->item->getId(), 'save' );
			return;
		}
		catch( \Aimeos\Admin\JQAdm\Exception $e )
		{
			$error = array( 'locale-site-item' => $context->getI18n()->dt( 'admin', $e->getMessage() ) );
			$view->errors = $view->get( 'errors', [] ) + $error;
			$this->logException( $e );
		}
		catch( \Aimeos\MShop\Exception $e )
		{
			$error = array( 'locale-site-item' => $context->getI18n()->dt( 'mshop', $e->getMessage() ) );
			$view->errors = $view->get( 'errors', [] ) + $error;
			$this->logException( $e );
		}
		catch( \Exception $e )
		{
			$error = array( 'locale-site-item' => $e->getMessage() . ', ' . $e->getFile() . ':' . $e->getLine() );
			$view->errors = $view->get( 'errors', [] ) + $error;
			$this->logException( $e );
		}

		$manager->rollback();

		return $this->create();
	}


	/**
	 * Returns a list of resource according to the conditions
	 *
	 * @return string HTML output
	 */
	public function search()
	{
		$view = $this->getView();
		$context = $this->getContext();

		try
		{
			$total = 0;
			$params = $this->storeSearchParams( $view->param(), 'locale/site' );
			$manager = \Aimeos\MShop\Factory::createManager( $context, 'locale/site' );
			$search = $this->initCriteria( $manager->createSearch(), $params );

			if( $view->access( 'super' ) === false )
			{
				$search->setConditions( $search->combine( '&&', [
					$search->compare( '==', 'locale.site.id', $this->getUserSiteId() ),
					$search->getConditions(),
				] ) );
			}

			$view->items = $manager->searchItems( $search, [], $total );
			$view->filterAttributes = $manager->getSearchAttributes( true );
			$view->filterOperators = $search->getOperators();
			$view->total = $total;
			$view->itemBody = '';

			foreach( $this->getSubClients() as $client ) {
				$view->itemBody .= $client->search();
			}
		}
		catch( \Aimeos\MShop\Exception $e )
		{
			$error = array( 'locale-item' => $context->getI18n()->dt( 'mshop', $e->getMessage() ) );
			$view->errors = $view->get( 'errors', [] ) + $error;
			$this->logException( $e );
		}
		catch( \Exception $e )
		{
			$error = array( 'locale-item' => $e->getMessage() . ', ' . $e->getFile() . ':' . $e->getLine() );
			$view->errors = $view->get( 'errors', [] ) + $error;
			$this->logException( $e );
		}

		/** admin/jqadm/locale/site/template-list
		 * Relative path to the HTML body template for the locale list.
		 *
		 * The template file contains the HTML code and processing instructions
		 * to generate the result shown in the body of the frontend. The
		 * configuration string is the path to the template file relative
		 * to the templates directory (usually in admin/jqadm/templates).
		 *
		 * You can overwrite the template file configuration in extensions and
		 * provide alternative templates. These alternative templates should be
		 * named like the default one but with the string "default" replaced by
		 * an unique name. You may use the name of your project for this. If
		 * you've implemented an alternative client class as well, "default"
		 * should be replaced by the name of the new class.
		 *
		 * @param string Relative path to the template creating the HTML code
		 * @since 2016.04
		 * @category Developer
		 */
		$tplconf = 'admin/jqadm/locale/site/template-list';
		$default = 'locale/site/list-standard.php';

		return $view->render( $view->config( $tplconf, $default ) );
	}


	/**
	 * Returns the sub-client given by its name.
	 *
	 * @param string $type Name of the client type
	 * @param string|null $name Name of the sub-client (Default if null)
	 * @return \Aimeos\Admin\JQAdm\Iface Sub-client object
	 */
	public function getSubClient( $type, $name = null )
	{
		/** admin/jqadm/locale/site/decorators/excludes
		 * Excludes decorators added by the "common" option from the locale JQAdm client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to remove a decorator added via
		 * "client/jqadm/common/decorators/default" before they are wrapped
		 * around the JQAdm client.
		 *
		 *  admin/jqadm/locale/site/decorators/excludes = array( 'decorator1' )
		 *
		 * This would remove the decorator named "decorator1" from the list of
		 * common decorators ("\Aimeos\Admin\JQAdm\Common\Decorator\*") added via
		 * "client/jqadm/common/decorators/default" to the JQAdm client.
		 *
		 * @param array List of decorator names
		 * @since 2017.10
		 * @category Developer
		 * @see admin/jqadm/common/decorators/default
		 * @see admin/jqadm/locale/site/decorators/global
		 * @see admin/jqadm/locale/site/decorators/local
		 */

		/** admin/jqadm/locale/site/decorators/global
		 * Adds a list of globally available decorators only to the locale JQAdm client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap global decorators
		 * ("\Aimeos\Admin\JQAdm\Common\Decorator\*") around the JQAdm client.
		 *
		 *  admin/jqadm/locale/site/decorators/global = array( 'decorator1' )
		 *
		 * This would add the decorator named "decorator1" defined by
		 * "\Aimeos\Admin\JQAdm\Common\Decorator\Decorator1" only to the JQAdm client.
		 *
		 * @param array List of decorator names
		 * @since 2017.10
		 * @category Developer
		 * @see admin/jqadm/common/decorators/default
		 * @see admin/jqadm/locale/site/decorators/excludes
		 * @see admin/jqadm/locale/site/decorators/local
		 */

		/** admin/jqadm/locale/site/decorators/local
		 * Adds a list of local decorators only to the locale JQAdm client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap local decorators
		 * ("\Aimeos\Admin\JQAdm\Locale\Site\Decorator\*") around the JQAdm client.
		 *
		 *  admin/jqadm/locale/site/decorators/local = array( 'decorator2' )
		 *
		 * This would add the decorator named "decorator2" defined by
		 * "\Aimeos\Admin\JQAdm\Locale\Site\Decorator\Decorator2" only to the JQAdm client.
		 *
		 * @param array List of decorator names
		 * @since 2017.10
		 * @category Developer
		 * @see admin/jqadm/common/decorators/default
		 * @see admin/jqadm/locale/site/decorators/excludes
		 * @see admin/jqadm/locale/site/decorators/global
		 */
		return $this->createSubClient( 'locale/site' . $type, $name );
	}


	/**
	 * Checks if the user is allowed to access the site item
	 *
	 * @param boolean $super True if user is a super user
	 * @param string $id ID of the site to access
	 * @throws \Aimeos\Admin\JQAdm\Exception If user isn't allowed to access the site
	 */
	protected function checkSite( $super, $id = null )
	{
		if( $super === true || (string) $this->getUserSiteId() === (string) $id ) {
			return;
		}

		throw new \Aimeos\Admin\JQAdm\Exception( sprintf( 'Permission denied' ) );
	}


	/**
	 * Returns the site ID of the current user
	 *
	 * @return string|null Site ID of the current user
	 */
	protected function getUserSiteId()
	{
		$context = $this->getContext();
		$manager = \Aimeos\MShop\Factory::createManager( $context, 'customer' );

		return $manager->getItem( $context->getUserId() )->getSiteId();
	}


	/**
	 * Returns the list of sub-client names configured for the client.
	 *
	 * @return array List of JQAdm client names
	 */
	protected function getSubClientNames()
	{
		return $this->getContext()->getConfig()->get( $this->subPartPath, $this->subPartNames );
	}


	/**
	 * Creates new and updates existing items using the data array
	 *
	 * @param string[] $data Data array
	 * @param boolean $super If current user is a super user
	 * @return \Aimeos\MShop\Locale\Item\Iface New locale item object
	 */
	protected function fromArray( array $data, $super )
	{
		$conf = [];

		if( isset( $data['config']['key'] ) )
		{
			foreach( (array) $data['config']['key'] as $idx => $key )
			{
				if( trim( $key ) !== '' && isset( $data['config']['val'][$idx] ) ) {
					$conf[$key] = $data['config']['val'][$idx];
				}
			}
		}

		$manager = \Aimeos\MShop\Factory::createManager( $this->getContext(), 'locale/site' );

		if( isset( $data['locale.site.id'] ) && $data['locale.site.id'] != '' )
		{
			$this->checkSite( $super, $data['locale.site.id'] );

			$item = $manager->getItem( $data['locale.site.id'] );
			$item->fromArray( $data );
			$item->setConfig( $conf );
			$item = $manager->saveItem( $item );
		}
		else
		{
			$this->checkSite( $super );

			$item = $manager->createItem();
			$item->fromArray( $data );
			$item->setConfig( $conf );
			$item = $manager->insertItem( $item );
		}

		return $item;
	}


	/**
	 * Constructs the data array for the view from the given item
	 *
	 * @param \Aimeos\MShop\Locale\Item\Iface $item Locale item object
	 * @return string[] Multi-dimensional associative list of item data
	 */
	protected function toArray( \Aimeos\MShop\Locale\Item\Site\Iface $item, $copy = false )
	{
		$data = $item->toArray( true );
		$data['config'] = [];

		if( $copy === true )
		{
			$data['locale.site.code'] = $data['locale.site.code'] . '_copy';
			$data['locale.site.id'] = '';
		}

		foreach( $item->getConfig() as $key => $value )
		{
			$data['config']['key'][] = $key;
			$data['config']['val'][] = $value;
		}

		return $data;
	}


	/**
	 * Returns the rendered template including the view data
	 *
	 * @return string HTML output
	 */
	protected function render( \Aimeos\MW\View\Iface $view )
	{
		/** admin/jqadm/locale/site/template-item
		 * Relative path to the HTML body template for the locale item.
		 *
		 * The template file contains the HTML code and processing instructions
		 * to generate the result shown in the body of the frontend. The
		 * configuration string is the path to the template file relative
		 * to the templates directory (usually in admin/jqadm/templates).
		 *
		 * You can overwrite the template file configuration in extensions and
		 * provide alternative templates. These alternative templates should be
		 * named like the default one but with the string "default" replaced by
		 * an unique name. You may use the name of your project for this. If
		 * you've implemented an alternative client class as well, "default"
		 * should be replaced by the name of the new class.
		 *
		 * @param string Relative path to the template creating the HTML code
		 * @since 2017.10
		 * @category Developer
		 */
		$tplconf = 'admin/jqadm/locale/site/template-item';
		$default = 'locale/site/item-standard.php';

		return $view->render( $view->config( $tplconf, $default ) );
	}
}
