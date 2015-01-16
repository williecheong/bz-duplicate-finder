<?php

class BaseController extends Controller {

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout() {
		if ( ! is_null($this->layout)) {
			$this->layout = View::make($this->layout);
		}
	}

	public function makeError( $content = "" ) {
		if ( is_string($content) ) {
			$content = array(
				"message" => $content
			);
		} 

		$response = Response::make($content, "400");
		$response->header('Content-Type', 'application/json');
		return $response;
	}

	public function makeSuccess( $content = "" ) {
		if ( is_string($content) ) {
			$content = array(
				"message" => $content
			);
		}

		$response = Response::make($content, "200");
		$response->header('Content-Type', 'application/json');
		return $response;
	}
}
