<?php
/**
 * Netresearch_Logmon_Block_Adminhtml_Config_Archive
 *
 * @category Netresearch
 * @package  Netresearch_Logmon
 * @author   Thomas Kappel <thomas.kappel@netresearch.de>
 */
class Netresearch_Logmon_Block_Adminhtml_Config_Archive
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected $addRowButtonHtml = array();
    protected $removeRowButtonHtml = array();
    protected $element;

    /**
     * Generates HTML Output
     * 
     * @param Varien_Data_Form_Element_Abstract $element
     * 
     * @return
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setTemplate('logmon/config/archive.phtml');
        $this->element = $element;
        return $this->_toHtml();
    }
    
    /**
     * Returns data of all configured rules
     * 
     * @return array
     */
    public function getRules()
    {
        $rules = $this->getValue();
        if (!is_array($rules)) {
            return array();
        }
        foreach ($rules as $id => $rule) {
            if(!is_array($rule) || $id <= 0) {
                unset($rules[$id]);
            }
        }
        return $rules;
    }

    /**
     * Generates html-output for one solvency group form and fills it with data
     * of grou with given id; leaves it empty, when 0 given 
     * 
     * @param int $id
     * 
     * @return string
     */
    public function getRowTemplateHtml($id = 0)
    {
        return $this->getLayout()
            ->createBlock('core/template', 'archive_form',
                array('template' => 'logmon/config/archive/form.phtml')
            )
            ->setElementName($this->element->getName())
            ->setId($id)
            ->addData(
                is_array($this->getValue($id))
                ? $this->getValue($id) 
                : array()
            )
            ->setRemoveRowButtonHtml($this->getRemoveRowButtonHtml())
            ->_toHtml();
    }

    /**
     * Return currently configured value with name key of rule with given id.
     * If no id given, it returns configuration of all groups, if no key is given
     * complete configuration of rule is returned.
     *  
     * @param int    $id
     * @param string $key
     * 
     * @return mixed
     */
    protected function getValue($id=null, $key=null)
    {
        if(is_null($id)) {
            return $this->element->getData('value');            
        }
        if(is_null($key)) {
            return $this->element->getData(sprintf('value/%d',
                $id
            ));
        }
        return $this->element->getData(sprintf('value/%d/%s',
            $id,
            $key
        ));
    }

    /**
     * Get add button html
     *
     * @param string $container
     * @param string $template
     * @param string $title
     *
     * @return string
     */
    public function getAddRowButtonHtml(
        $container, $template, $title = 'Add')
    {
        if (!isset($this->addRowButtonHtml[$container])) {
            $this->addRowButtonHtml[$container] =
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('add ' . $this->getDisabled())
                    ->setLabel($this->__($title))
                    ->setOnClick("
                        template = $('" . $template . "').innerHTML.replace(/\[0\]/g, '[' + getConfigId() + ']');
                        Element.insert($('" . $container . "'), {bottom: template})
                    ")
                    ->setDisabled($this->getDisabled())
                    ->toHtml();
        }

        return $this->addRowButtonHtml[$container];
    }

    /**
     * Get remove button html
     *
     * @param string $selector
     * @param string $title
     *
     * @return string
     */
    protected function getRemoveRowButtonHtml(
        $selector = 'li', $title = 'Remove')
    {
        if (!$this->removeRowButtonHtml) {
            $this->removeRowButtonHtml =
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('delete v-middle ' . $this->getDisabled())
                    ->setLabel($this->__($title))
                    ->setOnClick("Element.remove($(this).up('" . $selector . "'))")
                    ->setDisabled($this->getDisabled())
                    ->toHtml();
        }

        return $this->removeRowButtonHtml;
    }
}