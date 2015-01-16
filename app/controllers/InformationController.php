<?php

class InformationController extends BaseController {
	
	public function __construct() { }

	public function index() {
		phpinfo();
	}
}