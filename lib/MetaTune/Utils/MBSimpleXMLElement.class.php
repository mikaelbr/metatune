<?php
namespace MetaTune\Utils;

/**
 * Extention of the SimpleXMLElement class to make SimpleXMLElement ´
 * able to add another XML Element as a child to another XML Element.
 */
class MBSimpleXMLElement extends SimpleXMLElement {

    public function addXMLElement(SimpleXMLElement $source) {
        $new_dest = $this->addCData($source->getName(), $source[0]);

        foreach ($source->attributes() as $name => $value) {
            $new_dest->addAttribute($name, $value);
        }

        foreach ($source->children() as $child) {
            $new_dest->addXMLElement($child);
        }
    }

    public function addCData($nodename, $cdata_text) {
        $node2 = $this->addChild($nodename); //Added a nodename to create inside the function
        $node = dom_import_simplexml($node2);
        $no = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($cdata_text));
        return $node2;
    }

}
