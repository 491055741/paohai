<?php
namespace Postcard;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

use Postcard\Model\Order;
use Postcard\Model\OrderTable;
use Postcard\Model\WxPara;
use Postcard\Model\WxParaTable;
use Postcard\Model\Contact;
use Postcard\Model\ContactTable;
use Postcard\Model\UserPosition;
use Postcard\Model\UserPositionTable;

use Postcard\Model\Activity;
use Postcard\Model\ActivityTable;
use Postcard\Model\ActivityTemplateConfig;
use Postcard\Model\ActivityTemplateConfigTable;
use Postcard\Model\ActivityPriceRule;
use Postcard\Model\ActivityPriceRuleTable;
use Postcard\Model\ActivityJoinRecord;
use Postcard\Model\ActivityJoinRecordTable;
use Postcard\Model\Image;
use Postcard\Model\ImageTable;
use Postcard\Model\Youchuo;
use Postcard\Model\YouchuoTable;
use Postcard\Model\Coupon;
use Postcard\Model\CouponTable;


class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Postcard\Model\OrderTable' =>  function($sm) {
                    $tableGateway = $sm->get('PostcardTableGateway');
                    $table = new OrderTable($tableGateway);
                    return $table;
                },

                'Postcard\Model\WxParaTable' =>  function($sm) {
                    $tableGateway = $sm->get('WxParaTableGateway');
                    $table = new WxParaTable($tableGateway);
                    return $table;
                },

                'Postcard\Model\YouchuoTable' =>  function($sm) {
                    $tableGateway = $sm->get('YouchuoTableGateway');
                    $table = new YouchuoTable($tableGateway);
                    return $table;
                },

                'Postcard\Model\CouponTable' =>  function($sm) {
                    $tableGateway = $sm->get('CouponTableGateway');
                    $table = new CouponTable($tableGateway);
                    return $table;
                },

                'Postcard\Model\ContactTable' =>  function($sm) {
                    $tableGateway = $sm->get('ContactTableGateway');
                    $table = new ContactTable($tableGateway);
                    return $table;
                },

                'Postcard\Model\UserPositionTable' => function($sm) {
                    $tableGateway = $sm->get('UserPositionTableGateway');
                    $table = new UserPositionTable($tableGateway);
                    return $table;
                },
                'Postcard\Model\ActivityTable' => function($sm) {
                    $tableGateway = $sm->get('ActivityTableGateway');
                    $table = new ActivityTable($tableGateway);
                    return $table;
                },
                'Postcard\Model\ActivityTemplateConfigTable' => function($sm) {
                    $tableGateway = $sm->get('ActivityTemplateConfigTableGateway');
                    $table = new ActivityTemplateConfigTable($tableGateway);
                    return $table;
                },
                'Postcard\Model\ActivityPriceRuleTable' => function($sm) {
                    $tableGateway = $sm->get('ActivityPriceRuleTableGateway');
                    $table = new ActivityPriceRuleTable($tableGateway);
                    return $table;
                },
                'Postcard\Model\ActivityJoinRecordTable' => function($sm) {
                    $tableGateway = $sm->get('ActivityJoinRecordTableGateway');
                    $table = new ActivityJoinRecordTable($tableGateway);
                    return $table;
                },
                'Postcard\Model\ImageTable' => function($sm) {
                    $tableGateway = $sm->get('ImageTableGateway');
                    $table = new ImageTable($tableGateway);
                    return $table;
                },
                'PostcardTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Order());
                    return new TableGateway('order_table', $dbAdapter, null, $resultSetPrototype);
                },

                'WxParaTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new WxPara());
                    return new TableGateway('wxpara_table', $dbAdapter, null, $resultSetPrototype);
                },

                'YouchuoTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Youchuo());
                    return new TableGateway('youchuo', $dbAdapter, null, $resultSetPrototype);
                },

                'CouponTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Coupon());
                    return new TableGateway('coupon', $dbAdapter, null, $resultSetPrototype);
                },

                'ContactTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Contact());
                    return new TableGateway('contact_table', $dbAdapter, null, $resultSetPrototype);
                },

                'UserPositionTableGateway' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new UserPosition());
                    return new TableGateway('user_position', $dbAdapter, null, $resultSetPrototype);
                },
                'ActivityTableGateway' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Activity());
                    return new TableGateway("activity", $dbAdapter, null, $resultSetPrototype);
                },
                'ActivityTemplateConfigTableGateway' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new ActivityTemplateConfig());
                    return new TableGateway("activity_template_config", $dbAdapter, null, $resultSetPrototype);
                },
                'ActivityPriceRuleTableGateway' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new ActivityPriceRule());
                    return new TableGateway("activity_price_rule", $dbAdapter, null, $resultSetPrototype);
                },
                'ActivityJoinRecordTableGateway' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new ActivityJoinRecord());
                    return new TableGateway("activity_join_record", $dbAdapter, null, $resultSetPrototype);
                },
                'ImageTableGateway' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Image());
                    return new TableGateway("image", $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
