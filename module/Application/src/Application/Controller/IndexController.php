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

      return new ViewModel(array('mapboxAccessToken' => $accessToken, 'mapboxProjectId' => $projectId));
    }

    public function tablesByCityAction()
    {
        return new ViewModel();
    }

    public function tablesByCompanyAction()
    {
        return new ViewModel();
    }

    public function mapsAjaxAction()
    {
        //select the city fields and use a join to get the city's state's name
        $citySql = <<<SQL
SELECT city.name AS cityName, state.name AS stateName, coordinatesNorth, coordinatesWest, walkScore, transitScore, avgTemp, population, landAreaSquareMiles
FROM city
INNER JOIN state ON state.id = city.state_id
SQL;
        //select the company location fields and use a join to get the company location's company's name
        $companyLocationSql = <<<SQL
SELECT company.name AS companyName, coordinatesNorth, coordinatesWest
FROM companyLocation
INNER JOIN company ON company.id = companyLocation.company_id
SQL;
        return $this->getAndPrepareData(array($citySql, $companyLocationSql));
    }

    public function tablesByCityAjaxAction()
    {
        //select the city fields, use a join to get the city's state's name, and use joins and a group by to get the list of companies for each city
        $sql = <<<SQL
SELECT city.name AS cityName, state.name AS stateName, walkScore, transitScore, avgTemp, population, landAreaSquareMiles,
       GROUP_CONCAT(DISTINCT company.name ORDER BY company.name ASC SEPARATOR ';') AS companies
FROM city
INNER JOIN state ON state.id = city.state_id
INNER JOIN companyLocation ON companyLocation.city_id = city.id
INNER JOIN company ON company.id = companyLocation.company_id
GROUP BY city.id
SQL;
        return $this->getAndPrepareData(array($sql));
    }

    public function tablesByCompanyAjaxAction()
    {
        //select the company field and use joins and a group by to get lists of cities and states
        $sql = <<<SQL
SELECT company.name AS companyName,
       GROUP_CONCAT(DISTINCT city.name ORDER BY city.name ASC SEPARATOR ', ') AS cities,
       GROUP_CONCAT(DISTINCT state.name ORDER BY state.name ASC SEPARATOR ', ') AS states
FROM company
INNER JOIN companyLocation ON companyLocation.company_id = company.id
INNER JOIN city ON city.id = companyLocation.city_id
INNER JOIN state ON state.id = city.state_id
GROUP BY company.id
SQL;
        return $this->getAndPrepareData(array($sql));
    }

    private function getAndPrepareData(array $sqls)
    {
        //get the db handle
        $db = $this->getServiceLocator()->get('db');

        //might have multiple results, if given multiple sql queries
        $results = array();
        foreach($sqls as $sql) {
            $stmt      = $db->query($sql);
            $resultSet = $stmt->execute();
            $results[] = $resultSet->getResource()->fetchAll(\PDO::FETCH_NAMED); //get results as an associative array
        }

        //if just one query result, create a data associative array for data tables
        if (count($results) === 1) {
            $results = array("data" => $results[0]);
        }

        //output JSON rather than HTML
        $this->response->setContent(\Zend\Json\Json::encode($results));
        return $this->response;
    }
}
