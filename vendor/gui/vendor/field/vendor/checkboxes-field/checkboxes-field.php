<?php
namespace Mezon\GUI\Field;

/**
 * Class CheckboxesField
 *
 * @package Field
 * @subpackage CheckboxesField
 * @author Dodonov A.A.
 * @version v.1.0 (2019/09/13)
 * @copyright Copyright (c) 2019, aeon.org
 */
require_once (__DIR__ . '/../../../../../crud-service/vendor/crud-service-client/crud-service-client.php');
require_once (__DIR__ . '/../../../fields-algorithms/fields-algorithms.php');
require_once (__DIR__ . '/../../../form-builder/form-builder.php');
require_once (__DIR__ . '/../../../../../functional/functional.php');
require_once (__DIR__ . '/../../../../../template-engine/template-engine.php');

require_once (__DIR__ . '/../remote-field/remote-field.php');

/**
 * Checkboxes field control
 */
class CheckboxesField extends RemoteField
{

    /**
     * Getting list of records
     *
     * @return array List of records
     */
    protected function getExternalRecords(): array
    {
        // @codeCoverageIgnoreStart
        return ($this->getClient()->getAll());
        // @codeCoverageIgnoreEnd
    }

    /**
     * Method returns record's title
     *
     * @param array $Record
     *            Data source
     * @return string Compiled title
     */
    protected function getExternalTitle(array $Record): string
    {
        if (\Mezon\Functional::getField($Record, 'title') !== null) {
            return (\Mezon\Functional::getField($Record, 'title'));
        } else {
            return ('id : ' . \Mezon\Functional::getField($Record, 'id'));
        }
    }

    /**
     * Generating records feld
     *
     * @return string HTML representation of the records field
     */
    public function html(): string
    {
        $Content = '';

        $Records = $this->getExternalRecords();

        foreach ($Records as $Item) {
            $id = \Mezon\Functional::getField($Item, 'id');

            $Content .= '<label>
                <input type="checkbox" class="js-switch" name="' . $this->NamePrefix . '-' . $this->Name . '[]" value="' . $id . '" /> ' . $this->getExternalTitle($Item) . '
            </label><br>';
        }

        return ($Content);
    }
}

?>