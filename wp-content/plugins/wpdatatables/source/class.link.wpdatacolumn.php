<?php

defined('ABSPATH') or die("Cannot access pages directly.");

class LinkWDTColumn extends WDTColumn
{

    protected $_jsDataType = 'string';
    protected $_dataType = 'string';

    /**
     * LinkWDTColumn constructor.
     * @param array $properties
     */
    public function __construct($properties = array())
    {
        parent::__construct($properties);
        $this->_dataType = 'link';
    }

    /**
     * @param $content
     * @return mixed|string
     */
    public function prepareCellOutput($content)
    {
        if (strpos($content, '||') !== false) {
            list($link, $content) = explode('||', $content);
            $formattedValue = "<a data-content='{$content}' href='{$link}' target='_blank'>{$content}</a>";
        } else {
            if ($this->_inputType == 'attachment') {
                if (!empty($content)) {
                    $formattedValue = "<a href='{$content}' target='_blank'>{$this->_title}</a>";
                } else {
                    $formattedValue = '';
                }
            } else {
                $formattedValue = "<a href='{$content}' target='_blank'>{$content}</a>";
            }
        }
        $formattedValue = apply_filters('wpdatatables_filter_link_cell', $formattedValue, $this->getParentTable()->getWpId());
        return $formattedValue;
    }

}