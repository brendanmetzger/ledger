<?php
namespace controllers\traits;

trait config {
  public function __construct($request)
  {
    \bloc\view::addRenderer('after', \bloc\view\renderer::HTML());
		$this->year        = date('Y');
    $this->title       = "Gradebook";
    $this->_controller = $request->controller;
    $this->_action     = $request->action;
  }
}
