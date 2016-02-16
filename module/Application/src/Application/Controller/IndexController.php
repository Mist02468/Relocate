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
use Zend\View\Model\JsonModel;

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
        return new ViewModel();
    }

    public function tablesAjaxAction()
    {
        $db = $this->getServiceLocator()->get('db');

        $sql = <<<SQL
SELECT city.name AS cityName, state.name AS stateName, walkScore, transitScore, avgTemp, population, landAreaSquareMiles,
       GROUP_CONCAT(DISTINCT company.name ORDER BY company.name ASC SEPARATOR ';') AS companies
FROM city
INNER JOIN state ON state.id = city.state_id
INNER JOIN companyLocation ON companyLocation.city_id = city.id
INNER JOIN company ON company.id = companyLocation.company_id
GROUP BY city.id
SQL;
        $stmt           = $db->query($sql);
        $resultSet      = $stmt->execute();
        $result         = $resultSet->getResource()->fetchAll(\PDO::FETCH_NAMED);
        $resultPrepared = array("data" => $result);

        $this->response->setContent(\Zend\Json\Json::encode($resultPrepared));
        return $this->response;
    }
}
