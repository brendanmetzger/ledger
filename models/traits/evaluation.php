<?php
namespace models\traits;

trait evaluation {
  protected $criterion = null,
            $student   = null;

  public function loadCriterion(\DOMElement $criterion)
  {
    $this->criterion = new \models\Criterion($criterion);
    return $this;
  }

  public function loadStudent(\models\Student $student)
  {
    $this->student = $student;
    return $this;
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
    return $this->status == 'open' ? 'NA' : ($this->score * 100);
  }

  public function getLetter(\DOMElement $context)
  {
    return \models\Assessment::LETTER($this->score);
  }

  public function getFlag(\DOMElement $context)
  {
    if ($this->status == 'open') {
      return 'NA';
    }
    return ($this->status == 'marked' && $this->score <= 0) ? '⚐' : '✗';
  }

  public function getWeighted(\DOMElement $context)
  {
    return $this->percentage;
  }
}
