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
    public function indexAction()
    {
        return new ViewModel();
    }

    public function mapsAction()
    {
      //get the Mapbox API key from my local file
      $secrets     = parse_ini_file('config/autoload/localSecrets.ini');
      $accessToken = $secrets['MapboxAccessToken'];
      $projectId   = $secrets['MapboxProjectId'];

      $serviceManager   = $this->getServiceLocator();
      $cities           = $serviceManager->get('Application\Model\CityTable')->fetchAll();
      $companyLocations = $serviceManager->get('Application\Model\CompanyLocationTable')->fetchAll();

      return new ViewModel(array('mapboxAccessToken' => $accessToken, 'mapboxProjectId' => $projectId,
                                 'cities' => $cities, 'companyLocations' => $companyLocations));
    }

    public function tablesAction()
    {
        $serviceManager   = $this->getServiceLocator();
        $cities           = $serviceManager->get('Application\Model\CityTable')->fetchAll();
        $companyLocations = $serviceManager->get('Application\Model\CompanyLocationTable')->fetchAll();

        return new ViewModel(array('cities' => $cities, 'stateTable' => $serviceManager->get('Application\Model\StateTable'),
                                   'companyLocations' => $companyLocations));
    }
}
