# XMLParser
XML String DOM to Array parser

# Usage

put XML parser file somwhere in your program libraries (or set up by calling `require("XMLParser.php");` )

1) define parser
```
$xmlParser = new \Xdio\XMLParser();
```

2) define options
```
// for set depth of Nodes Indexing (tags in range of defined structure depth will go to easy search index)

$xmlParser->setMaxIndexDepth(0);

// if you need to Index specific tag names (index them during parsing for easy search)

$xmlParser->requireToIndexNodeName('order');
$xmlParser->requireToIndexNodeName('meta');
```

3) execute
```
$xmlParser->parseXMLString($string);

if($xmlParser->isSuccess()){
    // .... do something succesfull
}
```

4) retrieve something after
```
// if you want to get Indexed tags
$orderArray = $xmlParser->getIndexedNodesByNodeName("order");

// If you want to get all result array
$allArray = $xmlParser->getResultArray();
```
