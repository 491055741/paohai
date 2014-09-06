<?php
namespace Postcard;
use Postcard\Model\Order;
use Postcard\Model\OrderTable;
use Postcard\Model\WxPara;
use Postcard\Model\WxParaTable;
use Postcard\Model\Contact;
use Postcard\Model\ContactTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

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

                'Postcard\Model\ContactTable' =>  function($sm) {
                    $tableGateway = $sm->get('ContactTableGateway');
                    $table = new ContactTable($tableGateway);
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

                'ContactTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Contact());
                    return new TableGateway('contact_table', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
