<?php
namespace Mezon\CRUDService;

/**
 * Class CRUDServiceLogic
 *
 * @package CRUDService
 * @subpackage CRUDServiceLogic
 * @author Dodonov A.A.
 * @version v.1.0 (2019/08/13)
 * @copyright Copyright (c) 2019, aeon.org
 */
require_once (__DIR__ . '/../../../gui/vendor/fields-algorithms/vendor/filter/filter.php');
require_once (__DIR__ . '/../../../gui/vendor/form-builder/form-builder.php');
require_once (__DIR__ . '/../../../service/vendor/service-logic/service-logic.php');
require_once (__DIR__ . '/../../../utils/utils.php');

require_once (__DIR__ . '/../crud-service-model/crud-service-model.php');

define('NOW', 'NOW()');
define('CREATION_DATE_FIELD_NAME', 'creation_date');
define('DOMAIN_ID_FIELD_NAME', 'domain_id');
define('ORDER_FIELD_NAME', 'order');
define('FIELD_FIELD_NAME', 'field');
define('FIELD_TYPE_CUSTOM', 'custom');
define('ITEMS_FIELD_NAME', 'items');
define('ENTITY_FIELD_NAME', 'entity');
define('FIELDS_FIELD_NAME', 'fields');

/**
 * Class handles CRUD logic.
 *
 * @author Dodonov A.A.
 */
class CRUDServiceLogic extends \Mezon\Service\ServiceLogic
{

    /**
     * Form builder
     */
    var $FormBuilder = false;

    /**
     * Method deletes the specified record
     *
     * @return integer id of the deleted record
     */
    public function deleteRecord()
    {
        $DomainId = $this->getDomainId();
        $Where = \Mezon\GUI\FieldsAlgorithms\Filter::addFilterCondition([
            'id = ' . intval($this->ParamsFetcher->getParam('id'))
        ]);

        return ($this->Model->deleteFiltered($DomainId, $Where));
    }

    /**
     * Method deletes filtered records
     */
    public function deleteFiltered()
    {
        $DomainId = $this->getDomainId();
        $Where = \Mezon\GUI\FieldsAlgorithms\Filter::addFilterCondition([]);

        return ($this->Model->deleteFiltered($DomainId, $Where));
    }

    /**
     * Method returns records
     *
     * @param integer $DomainId
     *            Domain id
     * @param array $Order
     *            Sorting settings
     * @param integer $From
     *            Starting record
     * @param integer $Limit
     *            Fetch limit
     * @return array of records after all transformations
     */
    public function getRecords($DomainId, $Order, $From, $Limit): array
    {
        $Records = $this->Model->getSimpleRecords($DomainId, $From, $Limit, \Mezon\GUI\FieldsAlgorithms\Filter::addFilterCondition([]), $Order);

        return ($Records);
    }

    /**
     * Method returns domain id.
     *
     * @return integer Domain id.
     */
    public function getDomainId()
    {
        // records are not separated between domains
        if ($this->Model->hasField(DOMAIN_ID_FIELD_NAME) === false) {
            return (false);
        }

        if (isset($_GET['cross_domain']) && intval($_GET['cross_domain'])) {
            if ($this->hasPermit($this->Model->getEntityName() . '-manager')) {
                $DomainId = false;
            } else {
                throw (new \Exception('User "' . $this->getSelfLoginValue() . '" has no permit "' . $this->Model->getEntityName() . '-manager"'));
            }
        } else {
            $DomainId = $this->getSelfIdValue();
        }

        return ($DomainId);
    }

    /**
     * Method returns records
     *
     * @return array of records after all transformations.
     */
    public function listRecord(): array
    {
        $DomainId = $this->getDomainId();
        $Order = $this->ParamsFetcher->getParam(ORDER_FIELD_NAME, [
            FIELD_FIELD_NAME => 'id',
            ORDER_FIELD_NAME => 'ASC'
        ]);

        $From = $this->ParamsFetcher->getParam('from', 0);
        $Limit = $this->ParamsFetcher->getParam('limit', 1000000000);

        return ($this->getRecords($DomainId, $Order, $From, $Limit));
    }

    /**
     * Method returns all records
     *
     * @return array of records after all transformations
     */
    public function all(): array
    {
        $DomainId = $this->getDomainId();
        $Order = $this->ParamsFetcher->getParam(ORDER_FIELD_NAME, [
            FIELD_FIELD_NAME => 'id',
            ORDER_FIELD_NAME => 'ASC'
        ]);

        return ($this->getRecords($DomainId, $Order, 0, 1000000000));
    }

    /**
     * Method returns all records created since $Date
     *
     * @return array List of records created since $Date
     */
    public function newRecordsSince(): array
    {
        $DomainId = $this->getDomainId();
        $Date = $this->ParamsFetcher->getParam('date');

        if ($this->Model->hasField(CREATION_DATE_FIELD_NAME) === false) {
            throw (new \Exception('Field "creation_date" was not found'));
        }

        return ($this->Model->newRecordsSince($DomainId, $Date));
    }

    /**
     * Method returns records count
     *
     * @return integer Records count
     */
    public function recordsCount(): int
    {
        $DomainId = $this->getDomainId();

        return ($this->Model->recordsCount($DomainId));
    }

    /**
     * Method returns last $Count records
     *
     * @return array List of the last $Count records
     */
    public function lastRecords()
    {
        $DomainId = $this->getDomainId();
        $Count = $this->ParamsFetcher->getParam('count');
        $Filter = \Mezon\GUI\FieldsAlgorithms\Filter::addFilterCondition([
            '1 = 1'
        ]);

        return ($this->Model->lastRecords($DomainId, $Count, $Filter));
    }

    /**
     * Method compiles basic update record
     *
     * @param integer $id
     *            Id of the updating record
     * @return array with updated fields
     */
    protected function updateBasicFields($id)
    {
        $DomainId = $this->getDomainId();
        $Record = $this->Model->fetchFields();

        if ($this->Model->hasField('domain_id')) {
            $Record['domain_id'] = $this->getSelfIdValue();
        }

        $Where = [
            "id = " . $this->getParam('id')
        ];

        return ($this->Model->updateBasicFields($DomainId, $Record, $Where));
    }

    /**
     * Method updates custom fields
     *
     * @param integer $id
     *            Id of the updating record
     * @param array|object $Record
     *            Updating data
     * @param array $CustomFields
     *            Custom fields to be updated
     * @return array|object - Updated data
     */
    protected function updateCustomFields($id, $Record, $CustomFields)
    {
        if (isset($CustomFields)) {
            foreach ($CustomFields as $Name => $Value) {
                $this->Model->setFieldForObject($id, $Name, $Value);
            }

            $Record['custom_fields'] = $CustomFields;
        }

        return ($Record);
    }

    /**
     * Method updates record and it's custom fields
     *
     * @return array Updated fields and their new values
     */
    public function updateRecord()
    {
        $id = $this->ParamsFetcher->getParam('id');

        $Record = $this->updateBasicFields($id);

        $Record = $this->updateCustomFields($id, $Record, $this->ParamsFetcher->getParam('custom_fields', null));

        $Record['id'] = $id;

        return ($Record);
    }

    /**
     * Method creates user
     *
     * @return array Created record
     */
    public function createRecord()
    {
        $Record = $this->Model->fetchFields();

        if ($this->Model->hasField('domain_id')) {
            $DomainId = $this->getSelfIdValue();
        } else {
            $DomainId = false;
        }

        $Record = $this->Model->insertBasicFields($Record, $DomainId);

        foreach ($this->Model->getFields() as $Name => $Field) {
            $FieldName = $this->Model->getEntityName() . '-' . $Name;
            if ($Field['type'] == 'external' && $this->ParamsFetcher->getParam($FieldName, false) !== false) {
                $Ids = $this->ParamsFetcher->getParam($FieldName);
                $Record = $this->Model->insertExternalFields($Record, $this->ParamsFetcher->getParam('session_id'), $Name, $Field, $Ids);
            }
        }

        return ($Record);
    }

    /**
     * Method returns exact record from the table.
     *
     * @return array Exact record.
     */
    public function exact()
    {
        $id = $this->ParamsFetcher->getParam('id');
        $DomainId = $this->getDomainId();

        $Records = $this->Model->fetchRecordsByIds($DomainId, $id);

        return ($Records[0]);
    }

    /**
     * Method returns exact records from the table.
     *
     * @return array Exact list of records.
     */
    public function exactList()
    {
        $ids = $this->ParamsFetcher->getParam('ids');
        $DomainId = $this->getDomainId();

        return ($this->Model->fetchRecordsByIds($DomainId, $ids));
    }

    /**
     * Method returns records count, grouped by the specified field.
     *
     * @return integer Records count.
     */
    public function recordsCountByField()
    {
        $DomainId = $this->getDomainId();

        $this->Model->validateFieldExistance($this->ParamsFetcher->getParam(FIELD_FIELD_NAME));

        $Field = \Mezon\Security::getStringValue($this->ParamsFetcher->getParam(FIELD_FIELD_NAME));

        $Where = \Mezon\GUI\FieldsAlgorithms\Filter::addFilterCondition([]);

        return ($this->Model->recordsCountByField($DomainId, $Field, $Where));
    }

    /**
     * Fields descriptions.
     *
     * @return array Fields descriptions and layout
     */
    public function fields()
    {
        return ([
            'fields' => $this->Model->getFields(),
            'layout' => $this->Layout
        ]);
    }
}

?>