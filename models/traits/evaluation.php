<?php
namespace models\traits;

trait evaluation {
  protected $criterion = null;

  public function loadCriterion(\DOMElement $criterion)
  {
    $this->criterion = new \models\Criterion($criterion);
    return $this;
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
    return $this->status == 'open' ? 'NA' : ($this->score * 100);
  }

  public function getLetter(\DOMElement $context)
  {
    return \models\Assessment::LETTER($this->score);
  }
}
