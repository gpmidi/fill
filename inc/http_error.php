<?php

class HttpException extends Exception {
	protected $httpError = 500;
	function __construct($httpError = 500) {
		// BAZINGA ¬_¬
		$this->httpError = $httpError;
	}
	
	function getErrorCode() {
		return $this->httpError;
	}
	
	function getErrorFriendly() {
		switch ($this->httpError) {
			case 404:
				return 'Page Not Found';
				break;
			case 403:
				return 'Access Denied';
				break;
			case 500:
			default:
				return 'Internal Server Error';
				break;
		}
	}
	
	function getErrorTemplate() {
		switch ($this->httpError) {
			default:
				return 'httperror.html';
				break;
		}
	}
}