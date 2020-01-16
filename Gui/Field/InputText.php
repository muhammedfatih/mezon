<?php
namespace Mezon\Gui\Field;

/**
 * Class InputText
 *
 * @package Field
 * @subpackage InputText
 * @author Dodonov A.A.
 * @version v.1.0 (2019/09/04)
 * @copyright Copyright (c) 2019, aeon.org
 */

/**
 * Input field control
 */
class InputText extends \Mezon\Gui\Field
{

    /**
     * Generating input feld
     *
     * @return string HTML representation of the input field
     */
    public function html(): string
    {
        $Content = '<input class="form-control"';
        $Content .= $this->Required ? ' required="required"' : '';
        $Content .= ' type="text" name="' . $this->NamePrefix . '-' . $this->Name .
            ($this->Batch ? '[{_creation_form_items_counter}]' : '') . '"';
        $Content .= $this->Disabled ? ' disabled ' : '';
        $Content .= $this->Toggler === '' ? '' : 'toggler="' . $this->Toggler . '" ';
        $Content .= $this->Toggler === '' ? '' : 'toggle-value="' . $this->ToggleValue . '"';
        $Content .= '>';

        return $Content;
    }
}
