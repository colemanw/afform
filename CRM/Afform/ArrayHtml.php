<?php

/**
 * Class CRM_Afform_ArrayHtml
 *
 * FIXME This a quick-and-dirty array<=>html mapping.
 */
class CRM_Afform_ArrayHtml {

  const DEFAULT_TAG = 'div';

  /**
   * @param array $array
   *   Ex: ['#tag' => 'div', 'class' => 'greeting', '#children' => ['Hello world']]
   * @return string
   *   Ex: '<div class="greeting">Hello world</div>'
   */
  public function convertArrayToHtml($array) {
    $tag = empty($array['#tag']) ? self::DEFAULT_TAG : $array['#tag'];
    unset($array['#tag']);
    $children = empty($array['#children']) ? self::DEFAULT_TAG : $array['#children'];
    unset($array['#children']);

    $buf = '<' . $tag;
    foreach ($array as $attrName => $attrValue) {
      if ($attrName{0} === '#') {
        continue;
      }
      if (!preg_match('/^[a-zA-Z0-9\-]+$/', $attrName)) {
        throw new \RuntimeException("Malformed HTML attribute");
      }
      if (is_string($attrValue)) {
        $buf .= sprintf(' %s="%s"', $attrName, htmlentities($attrValue)); // FIXME attribute encoding
      }
      elseif (is_array($attrValue) && $this->allowStructuredAttribute($tag, $attrName)) {
        $buf .= sprintf(' %s="%s"', $attrName, htmlentities(json_encode($attrValue))); // FIXME attribute encoding
      }
    }
    $buf .= '>';

    foreach ($children as $child) {
      if (is_string($child)) {
        $buf .= htmlentities($child);
      }
      elseif (is_array($child)) {
        $buf .= $this->convertArrayToHtml($child);
      }
    }

    $buf .= '</' . $tag . '>';
    return $buf;
  }

  /**
   * @param string $html
   *   Ex: '<div class="greeting">Hello world</div>'
   * @return array
   *   Ex: ['#tag' => 'div', 'class' => 'greeting', '#children' => ['Hello world']]
   */
  public function convertHtmlToArray($html) {
    $doc = new DOMDocument();
    $doc->loadHTML("<html><body>$html</body></html>");

    // FIXME: Valid expected number of child nodes

    foreach ($doc->childNodes as $htmlNode) {
      if ($htmlNode instanceof DOMElement && $htmlNode->tagName === 'html') {
        return $this->convertNodeToArray($htmlNode->firstChild->firstChild);
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
        if ($txt{0} === '{' && $txt{1} !== '{' && $this->allowStructuredAttribute($node->tagName, $attribute->name)) {
          $arr[$attribute->name] = sprintf('PARSE-ME(%s)', $txt);
        }
        else {
          $arr[$attribute->name] = $txt;
        }
      }
      foreach ($node->childNodes as $childNode) {
        $arr['#children'][] = $this->convertNodeToArray($childNode);
      }
      return $arr;
    }
    elseif ($node instanceof DOMText) {
      return $node->textContent;
    }
    else {
      throw new \RuntimeException("Unrecognized DOM node");
    }
  }

  public function allowStructuredAttribute($tag, $attr) {
    // FIXME: use whitelist of allowed angular directives
    return FALSE;
  }

}
