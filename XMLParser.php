<?php
/**
 * Created by Anton Repin.
 * Date: 1/29/16
 * Time: 10:52 AM
 */

namespace Xdio;

class XMLParser
{

    private $success = false;

    private $error = 0;

    private $errorMessage = "";

    private $resultArray = [];

    private $maxDepth = 16;

    private $currentDepth = 0;

    private $index = [];

    private $maxIndexDepth = 0;

    private $requireToIndex = [];

    private $requireToIndexAmount = 0;

    function __construct()
    {

    }

    /**
     * @param string $nodeName
     */
    public function requireToIndexNodeName($nodeName){
        $this->requireToIndex[$nodeName] = 1;
        $this->requireToIndexAmount++;
    }

    /**
     * @param $nodeName
     * @return null
     */
    public function &getIndexedNodesByNodeName($nodeName){
        if(isset($this->index[$nodeName])){
            return $this->index[$nodeName];
        }
        return null;
    }

    /**
     * @return array
     */
    public function &getResultArray()
    {
        return $this->resultArray;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return boolean
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @return int
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param int $maxDepth
     */
    public function setMaxDepth($maxDepth)
    {
        $this->maxDepth = $maxDepth;
    }

    /**
     * @param int $maxIndexDepth
     */
    public function setMaxIndexDepth($maxIndexDepth)
    {
        $this->maxIndexDepth = $maxIndexDepth;
    }

    /**
     * @param string $string
     */
    public function parseXMLString($string) {

        $xmlDoc = new \DOMDocument();
        $s = str_replace("&","&amp;", $string);
        if($xmlDoc->loadXML($s)) {
            $this->parseDOMDocument($xmlDoc);
        } else {
            $this->error = 400;
            $this->errorMessage = "Not valid XML string";
        }

    }

    /**
     * @param \DOMDocument $xml
     */
    public function parseDOMDocument(\DOMDocument $xml) {

        $this->success = false;
        $this->currentDepth = 0;
        $this->resultArray = [];
        $struct = $xml->documentElement;

        $this->resultArray = $this->parseDocumentElement($struct);
        if($this->error < 400){
            $this->success = true;
        }

    }

    /**
     * @param \DOMElement $e
     * @return array | null
     */
    private function parseDocumentElement(\DOMElement $e){

        $this->currentDepth++;
        $a = [];
        $k = 0;

        if($this->currentDepth <= $this->maxDepth) {

            if($e->nodeType != 3) {

                if ($e->hasChildNodes()) {

                    $childs = $e->childNodes;

                    if($childs->length > 0) {

                        /** ---------------------------------------------------------------------------------------  \
                         *                                  EXTRACT RESULTS
                         *  --------------------------------------------------------------------------------------- */

                        /** @var \DOMElement $child */
                        foreach ($childs as $child) {

                            if ($child->nodeType != 3) {
                                $res = $this->parseDocumentElement($child);
                                if(empty($res)){
                                    $a[$e->localName][] = [$child->localName => $child->nodeValue];
                                } else {
                                    $a[$e->localName][] = $res;
                                }
                                $k++;
                            }

                        }

                        /** ---------------------------------------------------------------------------------------  \
                         *                                  PRETTIFY RESULTS
                         *  --------------------------------------------------------------------------------------- */
                        // Make dictionary fields instead of
                        //                        [nodeName][][childNodeName][childNodeValue]
                        //                  result will be
                        //                        [nodeName][childNodeName][childNodeValue]
                        //                  if node has only one childNodeName

                        $sims = false;
                        $imdt = [];
                        $fixd = [];
                        // check for similarities
                        foreach($a as $mk=>&$s) {

                            // [nodeName][index]
                            if(!empty($s)) {

                                // [index][dataOfNodeValue]
                                foreach($s as $k => &$si) {

                                    if(!empty($si)) {

                                        // [childNodeName][childNodeValue]
                                        foreach($si as $sk => &$sv) {
                                            $sims = isset($imdt[$sk])?true:false;
                                            $imdt[$sk] = 1;
                                            $fixd[$mk][$sk] = &$sv;
                                        }

                                    }

                                }

                            }

                        }
                        // substitute if no similarities found
                        if(!$sims) {
                            $a = $fixd;
                        }
                        /** --------------------------------------------------------------------------------------- */

                        if($this->maxIndexDepth > 1 || $this->requireToIndexAmount > 0) {
                            if (($this->currentDepth < $this->maxIndexDepth && $this->currentDepth > 1) || isset($this->requireToIndex[$e->localName])) {
                                $this->index[$e->localName][] = &$a[$e->localName];
                            }
                        }

                        // apply and return
                        $this->currentDepth--;
                        return $a;

                    }

                }

            }

        } else {
            $this->error = 300;
            $this->errorMessage = "Max depth of ".$this->currentDepth." for thread ".$e->localName." reached, "
            ."results for this thread will be clipped";
        }

        $this->currentDepth--;
        if($k==0) $a = null;
        return $a;

    }

}