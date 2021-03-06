<?php
namespace models\traits;

trait evaluation {

  // these are dependencies
  protected $criterion = null,
            $student   = null;
  
  public function getTitle(\DOMElement $context)
  {
    return $this->criterion->context['@title'];
  }

  public function getIndex(\DOMelement $context)
  {
    return $this->criterion->context['@index'];
  }

  public function beforeSave()
  {
    $this->context->setAttribute('updated', (new \DateTime())->format('Y-m-d H:i:s'));
  }

  public function getStatus(\DOMElement $context)
  {
    return $context['@updated'] ? 'marked' : 'open';
  }

  public function getPercentage(\DOMElement $context)
  {
    return $this->status == 'open' ? 'NA' : round($this->score * 100);
  }

  public function getLetter(\DOMElement $context)
  {
    return \models\Assessment::LETTER(round($this->score));
  }

  public function getFlag(\DOMElement $context)
  {
    if ($this->status == 'open') {
      return 'NA';
    }
    return $this->score == 0 ? '♻' : ($this->score < 0 ? '⚐' : '✗');
  }

  public function getWeighted(\DOMElement $context)
  {
    return $this->percentage;
  }
}
