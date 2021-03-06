<?php

namespace Lighthouse\IntegrationBundle\Set10\Import\Products;

use Lighthouse\IntegrationBundle\Set10\XmlParser;
use DOMNode;

/**
 * @method GoodElement readNextElement()
 */
class Set10ProductImportXmlParser extends XmlParser
{
    /**
     * @param $name
     * @return bool
     */
    protected function supportsNodeName($name)
    {
        return 'good' == $name;
    }

    /**
     * @param DOMNode $node
     * @return GoodElement
     */
    protected function createElement(DOMNode $node)
    {
        return GoodElement::createByDom($node);
    }
}
