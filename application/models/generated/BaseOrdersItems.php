<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('OrdersItems', 'doctrine');

/**
 * BaseOrdersItems
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $detail_id
 * @property integer $order_id
 * @property integer $billing_cycle_id
 * @property timestamp $date_start
 * @property timestamp $date_end
 * @property boolean $autorenew
 * @property integer $quantity
 * @property float $cost
 * @property float $price
 * @property float $setupfee
 * @property integer $status_id
 * @property string $parameters
 * @property string $setup
 * @property string $note
 * @property integer $product_id
 * @property integer $tld_id
 * @property integer $review_id
 * @property integer $parent_detail_id
 * @property string $description
 * @property string $callback_url
 * @property string $uuid
 * @property Orders $Orders
 * @property Products $Products
 * @property DomainsTlds $DomainsTlds
 * @property BillingCycle $BillingCycle
 * @property Statuses $Statuses
 * @property Reviews $Reviews
 * @property Doctrine_Collection $CancelRequests
 * @property Doctrine_Collection $Domains
 * @property Doctrine_Collection $Messages
 * @property Doctrine_Collection $OrdersItemsDomains
 * @property Doctrine_Collection $OrdersItemsServers
 * @property Doctrine_Collection $PanelsActions
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class BaseOrdersItems extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('orders_items');
        $this->hasColumn('detail_id', 'integer', 4, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => true,
             'length' => '4',
             ));
        $this->hasColumn('order_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => '4',
             ));
        $this->hasColumn('billing_cycle_id', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => false,
             'length' => '4',
             ));
        $this->hasColumn('date_start', 'timestamp', 25, array(
             'type' => 'timestamp',
             'notnull' => true,
             'length' => '25',
             ));
        $this->hasColumn('date_end', 'timestamp', 25, array(
             'type' => 'timestamp',
             'notnull' => false,
             'length' => '25',
             ));
        $this->hasColumn('autorenew', 'boolean', 25, array(
             'type' => 'boolean',
             'default' => 1,
             'length' => '25',
             ));
        $this->hasColumn('quantity', 'integer', 4, array(
             'type' => 'integer',
             'notnull' => true,
             'length' => '4',
             ));
        $this->hasColumn('cost', 'float', 10, array(
             'type' => 'float',
             'notnull' => true,
             'length' => '10',
             ));
        $this->hasColumn('price', 'float', 10, array(
             'type' => 'float',
             'notnull' => true,
             'length' => '10',
             ));
        $this->hasColumn('setupfee', 'float', 10, array(
             'type' => 'float',
             'default' => 0,
             'length' => '10',
             ));
        $this->hasColumn('status_id', 'integer', 4, array(
             'type' => 'integer',
             'default' => '1',
             'notnull' => true,
             'length' => '4',
             ));
        $this->hasColumn('parameters', 'string', null, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '',
             ));
        $this->hasColumn('setup', 'string', null, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '',
             ));
        $this->hasColumn('note', 'string', null, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '',
             ));
        $this->hasColumn('product_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('tld_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('review_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('parent_detail_id', 'integer', 4, array(
             'type' => 'integer',
             'length' => '4',
             ));
        $this->hasColumn('description', 'string', null, array(
             'type' => 'string',
             'length' => '',
             ));
        $this->hasColumn('callback_url', 'string', 200, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '200',
             ));
        $this->hasColumn('uuid', 'string', 50, array(
             'type' => 'string',
             'notnull' => false,
             'length' => '50',
             ));


        $this->index('uuid', array(
             'fields' => 
             array(
              0 => 'uuid',
             ),
             'type' => 'unique',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Orders', array(
             'local' => 'order_id',
             'foreign' => 'order_id',
             'onDelete' => 'CASCADE'));

        $this->hasOne('Products', array(
             'local' => 'product_id',
             'foreign' => 'product_id',
             'onDelete' => 'CASCADE'));

        $this->hasOne('DomainsTlds', array(
             'local' => 'tld_id',
             'foreign' => 'tld_id'));

        $this->hasOne('BillingCycle', array(
             'local' => 'billing_cycle_id',
             'foreign' => 'billing_cycle_id'));

        $this->hasOne('Statuses', array(
             'local' => 'status_id',
             'foreign' => 'status_id'));

        $this->hasOne('Reviews', array(
             'local' => 'review_id',
             'foreign' => 'review_id'));

        $this->hasMany('CancelRequests', array(
             'local' => 'detail_id',
             'foreign' => 'orderitem_id'));

        $this->hasMany('Domains', array(
             'local' => 'detail_id',
             'foreign' => 'orderitem_id'));

        $this->hasMany('Messages', array(
             'local' => 'detail_id',
             'foreign' => 'detail_id'));

        $this->hasMany('OrdersItemsDomains', array(
             'local' => 'detail_id',
             'foreign' => 'orderitem_id'));

        $this->hasMany('OrdersItemsServers', array(
             'local' => 'detail_id',
             'foreign' => 'orderitem_id'));

        $this->hasMany('PanelsActions', array(
             'local' => 'detail_id',
             'foreign' => 'orderitem_id'));
    }
}