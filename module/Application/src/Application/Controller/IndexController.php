<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    protected $cityTable;

    public function indexAction()
    {
        //get the Mapbox API key from my local file
        $secrets     = parse_ini_file('config/autoload/localSecrets.ini');
        $accessToken = $secrets['MapboxAccessToken'];
        $projectId   = $secrets['MapboxProjectId'];

        return new ViewModel(array('mapboxAccessToken' => $accessToken, 'mapboxProjectId' => $projectId, 'cities' => $this->getCityTable()->fetchAll()));
    }

    public function getCityTable()
    {
        if (!$this->cityTable) {
            $sm = $this->getServiceLocator();
            $this->cityTable = $sm->get('Application\Model\cityTable');
        }
        return $this->cityTable;
    }
}
