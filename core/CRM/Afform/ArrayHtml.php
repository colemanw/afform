<?php

/**
 * Class CRM_Afform_ArrayHtml
 *
 * FIXME This a quick-and-dirty array<=>html mapping.
 * FIXME: Comment mapping.
 */
class CRM_Afform_ArrayHtml {

  const DEFAULT_TAG = 'div';

  /**
   * This is a minimalist/temporary placeholder for a schema definition.
   * FIXME: It shouldn't be here or look like this.
   *
   * @var array
   *   Ex: $protoSchema['my-tag']['my-attr'] = 'text';
   */
  private $protoSchema = [
    '*' => [
      '*' => 'text',
      'af-fieldset' => 'text',
    ],
    'af-entity' => [
      '#selfClose' => TRUE,
      'name' => 'text',
      'type' => 'text',
      'data' => 'js',
    ],
    'af-field' => [
      '#selfClose' => TRUE,
      'name' => 'text',
      'defn' => 'js',
    ],
    'area' => ['#selfClose' => TRUE],
    'base' => ['#selfClose' => TRUE],
    'br' => ['#selfClose' => TRUE],
    'col' => ['#selfClose' => TRUE],
    'command' => ['#selfClose' => TRUE],
    'embed' => ['#selfClose' => TRUE],
    'hr' => ['#selfClose' => TRUE],
    'iframe' => ['#selfClose' => TRUE],
    'img' => ['#selfClose' => TRUE],
    'input' => ['#selfClose' => TRUE],
    'keygen' => ['#selfClose' => TRUE],
    'link' => ['#selfClose' => TRUE],
    'meta' => ['#selfClose' => TRUE],
    'param' => ['#selfClose' => TRUE],
    'source' => ['#selfClose' => TRUE],
    'track' => ['#selfClose' => TRUE],
    'wbr' => ['#selfClose' => TRUE],
  ];

  /**
   * @var bool
   */
  protected $deepCoding;

  /**
   * CRM_Afform_ArrayHtml constructor.
   * @param bool $deepCoding
   */
  public function __construct($deepCoding = TRUE) {
    $this->deepCoding = $deepCoding;
  }

  /**
   * @param array $array
   *   Ex: ['#tag' => 'div', 'class' => 'greeting', '#children' => ['Hello world']]
   * @return string
   *   Ex: '<div class="greeting">Hello world</div>'
   */
  public function convertArrayToHtml(array $array) {
    if ($array === []) {
      return '';
    }

    if (isset($array['#comment'])) {
      if (strpos($array['#comment'], '-->')) {
        Civi::log()->warning('Afform: Cannot store comment with text "-->". Munging.');
        $array['#comment'] = str_replace('-->', '-- >', $array['#comment']);
      }
      return sprintf('<!--%s-->', $array['#comment']);
    }

    if (isset($array['#text'])) {
      return $array['#text'];
    }

    $tag = empty($array['#tag']) ? self::DEFAULT_TAG : $array['#tag'];
    unset($array['#tag']);
    $children = empty($array['#children']) ? [] : $array['#children'];
    unset($array['#children']);

    $buf = '<' . $tag;
    foreach ($array as $attrName => $attrValue) {
      if ($attrName{0} === '#') {
        continue;
      }
      if (!preg_match('/^[a-zA-Z0-9\-]+$/', $attrName)) {
        throw new \RuntimeException("Malformed HTML attribute");
      }

      $type = $this->pickAttrType($tag, $attrName);
      $encodedValue = $this->encodeAttrValue($type, $attrValue);
      if ($encodedValue !== NULL) {
        // ENT_COMPAT: Will convert double-quotes and leave single-quotes alone.
        $buf .= sprintf(" %s=\"%s\"", $attrName, htmlentities($encodedValue, ENT_COMPAT | ENT_XHTML));
      }
      else {
        Civi::log()->warning('Afform: Cannot serialize attribute {attrName}', [
          'attrName' => $attrName,
        ]);
      }
    }

    if (isset($array['#markup'])) {
      return $buf . '>' . $array['#markup'] . '</' . $tag . '>';
    }

    if (empty($children) && $this->isSelfClosing($tag)) {
      $buf .= ' />';
    }
    else {
      $buf .= '>';
      $buf .= $this->convertArraysToHtml($children);
      $buf .= '</' . $tag . '>';
    }
    return $buf;
  }

  public function convertArraysToHtml($children) {
    $buf = '';

    foreach ($children as $child) {
      if (is_string($child)) {
        $buf .= htmlentities($child);
      }
      elseif (is_array($child)) {
        $buf .= $this->convertArrayToHtml($child);
      }
    }

    return $buf;
  }

  /**
   * @param string $html
   *   Ex: '<div class="greeting">Hello world</div>'
   * @return array
   *   Ex: ['#tag' => 'div', 'class' => 'greeting', '#children' => ['Hello world']]
   */
  public function convertHtmlToArray($html) {
    if ($html === '') {
      return [];
    }

    $doc = new DOMDocument();
    @$doc->loadHTML("<html><body>$html</body></html>");

    // FIXME: Validate expected number of child nodes

    foreach ($doc->childNodes as $htmlNode) {
      if ($htmlNode instanceof DOMElement && $htmlNode->tagName === 'html') {
        return $this->convertNodesToArray($htmlNode->firstChild->childNodes);
      }
    }

    return NULL;
  }

  /**
   * @param \DOMNode $node
   * @return array|string
   */
  public function convertNodeToArray($node) {
    if ($node instanceof DOMElement) {
      $arr = ['#tag' => $node->tagName];
      foreach ($node->attributes as $attribute) {
        $txt = $attribute->textContent;
        $type = $this->pickAttrType($node->tagName, $attribute->name);
        $arr[$attribute->name] = $this->decodeAttrValue($type, $txt);
      }
      if ($node->childNodes->length > 0) {
        // In shallow mode, return "af-markup" blocks as-is
        if (!$this->deepCoding && !empty($arr['class']) && strpos($arr['class'], 'af-markup') !== FALSE) {
          $arr['#markup'] = '';
          foreach ($node->childNodes as $child) {
            $arr['#markup'] .= $child->ownerDocument->saveXML($child);
          }
        }
        else {
          $arr['#children'] = $this->convertNodesToArray($node->childNodes);
        }
      }
      return $arr;
    }
    elseif ($node instanceof DOMText) {
      return ['#text' => $node->textContent];
    }
    elseif ($node instanceof DOMComment) {
      return ['#comment' => $node->nodeValue];
    }
    else {
      throw new \RuntimeException("Unrecognized DOM node");
    }
  }

  /**
   * @param array|DOMNodeList $nodes
   *   List of DOMNodes
   * @return array
   */
  protected function convertNodesToArray($nodes) {
    $children = [];
    foreach ($nodes as $childNode) {
      $children[] = $this->convertNodeToArray($childNode);
    }
    return $children;
  }

  /**
   * @param string $tag
   *   Ex: 'img', 'div'
   * @return bool
   *   TRUE if the tag should look like '<img/>'.
   *   FALSE if the tag should look like '<div></div>'.
   */
  protected function isSelfClosing($tag) {
    return $this->protoSchema[$tag]['#selfClose'] ?? FALSE;
  }

  /**
   * Determine the type of data that is stored in an attribute.
   *
   * @param string $tag
   *   Ex: 'af-entity'
   * @param string $attrName
   *   Ex: 'label'
   * @return string
   *   Ex: 'text' or 'js'
   */
  protected function pickAttrType($tag, $attrName) {
    if (!$this->deepCoding) {
      return 'text';
    }

    return $this->protoSchema[$tag][$attrName] ?? $this->protoSchema['*'][$attrName] ?? $this->protoSchema['*']['*'];
  }

  /**
   * Given an array (deep) representation of an attribute, determine the
   * attribute's content.
   *
   * @param string $type
   *   Ex A: 'text'
   *   Ex B: 'js'
   * @param mixed $mixedAttrValue
   *   The mixed (string/array/int) representation of the value in deep format
   *   Ex A: 'hello'
   *   Ex B: ['hello' => 123]
   * @return string
   *   An equivalent HTML attribute content (text).
   *   Ex A: 'hello'
   *   Ex B: '{hello: 123}'
   */
  protected function encodeAttrValue($type, $mixedAttrValue) {
    switch ($type) {
      case 'text':
        return $mixedAttrValue;

      case 'js':
        $v = CRM_Utils_JS::writeObject($mixedAttrValue, TRUE);
        return $v;

      default:
        return NULL;
    }
  }

  /**
   * Given a string representation of an attribute value, determine the
   * equivalent array (deep) representation.
   *
   * @param string $type
   *   Ex A: 'text'
   *   Ex B: 'js'
   * @param string $txtAttrValue
   *   The textual representation of the value from HTML notation.
   *   Ex A: 'hello'
   *   Ex B: '{hello: 123}'
   * @return mixed
   *   The mixed (string/array/int) rerepresentation of the value in deep format
   *   Ex A: 'hello'
   *   Ex B: ['hello' => 123]
   */
  protected function decodeAttrValue($type, $txtAttrValue) {
    if ($type == 'js') {
      $attrValue = CRM_Utils_JS::decode($txtAttrValue);
      return $attrValue;
    }
    else {
      $attrValue = $txtAttrValue;
      return $attrValue;
    }
  }

}
