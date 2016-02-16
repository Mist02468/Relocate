<?php

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

class CompanyLocationTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getCompanyLocation($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        return $row;
    }

    public function saveCompanyLocation(CompanyLocation $companyLocation)
    {
        $data = array(
            'city_id'          => $companyLocation->city_id,
            'company_id'       => $companyLocation->company_id,
            'coordinatesNorth' => $companyLocation->coordinatesNorth,
            'coordinatesWest'  => $companyLocation->coordinatesWest
        );

        $id = (int) $companyLocation->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
            $companyLocation->id = $this->tableGateway->getLastInsertValue();
        } else {
            if ($this->getCompanyLocation($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('CompanyLocation id does not exist');
            }
        }
        return $companyLocation;
    }

    public function deleteCompanyLocation($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }
}
