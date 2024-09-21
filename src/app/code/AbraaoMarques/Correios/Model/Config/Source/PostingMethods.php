<?php

namespace AbraaoMarques\Correios\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PostingMethods implements OptionSourceInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            array('value' => 40010, 'label' => __('Sedex sem Contrato (40010)')),
            array('value' => 4162, 'label' => __('Sedex com Contrato (4162)')),
            array('value' => 41106, 'label' => __('Pac sem Contrato (41106)')),
            array('value' => 4669, 'label' => __('Pac com Contrato (4669)')),
            array('value' => 40215, 'label' => __('Sedex 10 (40215)')),
            array('value' => 40290, 'label' => __('Sedex HOJE (40290)')),
            array('value' => 40045, 'label' => __('Sedex a Cobrar (40045)')),
        ];
    }
}
