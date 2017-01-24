<?php
	namespace DavidMagalhaes\CrawlersRepo;

	class Uzo {
		const URL_MAIN = "http://www.uzo.pt";
		const URL_LOGIN = "www.uzo.pt/pt/pagina.uzo";
		const URL_BALANCE = "https://meu.uzo.pt/resumo.xml";

		private $_crawlerHelper;

		private $_phoneNumber;
		private $_password;
		private $_cookiePath = "uzo.cookies";

		public function __construct($phoneNumber, $password) {
			$this->_crawlerHelper = new \CrawlerHelper();
			$this->_crawlerHelper->setCookiePath($this->_cookiePath);

			$this->_phoneNumber = $phoneNumber;
			$this->_password = $password;
		}

		/**
		 * Login into the website
		 * 
		 * @return [type] [description]
		 */
		public function login() {
			$uzoPage = $this->_crawlerHelper->httpRequest($this::URL_MAIN, false, $this::URL_MAIN);

			$uzoHtml = $uzoPage->getHtml();
			$vars = explode('<input type="hidden"', $uzoHtml);
			array_shift($vars);

			$uzoPost = array();
			foreach($vars as $hidden) {
				$varName = explode('name="', $hidden);
				$varName = explode('"', $varName[1]);
				$varName = $varName[0];

				$varValue = explode('value="', $hidden);
				$varValue = explode('"', $varValue[1]);
				$varValue = $varValue[0];

				$uzoPost[$varName] = urlencode($varValue);
			}

			$eventTarget 		= "ctl00%24ucLogin1%24lnkbtnOK";
			$eventArgument 		= $uzoPost["__EVENTARGUMENT"];
			$viewState 			= $uzoPost["__VIEWSTATE"];
			$viewStateGenerator = $uzoPost["__VIEWSTATEGENERATOR"];
			$eventValidation 	= $uzoPost["__EVENTVALIDATION"];


			$post = "__EVENTTARGET=" . $eventTarget 
			. "&__EVENTARGUMENT=" . $eventArgument 
			. "&__VIEWSTATE=" . $viewState 
			. "&__VIEWSTATEGENERATOR=" . $viewStateGenerator 
			. "&__EVENTVALIDATION=" . $eventValidation 
			. "&ctl00%24ucLogin1%24txbUsername=" . $this->_phoneNumber 
			. "&ctl00%24ucLogin1%24txbPassword=" . $this->_password 
			. "&ctl00%24ucLogin1%24txbPasswordClear=password";

			$httpResponse = $this->_crawlerHelper->httpRequest($this::URL_LOGIN, $post, $this::URL_LOGIN);

			// Check if login was successful or not
			if (strpos('Telemóvel ou password incorrecto', $httpResponse->getHtml()) !== false) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Get current balance
		 * 
		 * @return float|bool Current balance if available, or false if not
		 */
		public function getBalance() {
			// Get information
			$httpResponse = $this->_crawlerHelper->httpRequest($this::URL_BALANCE, false, $this::URL_BALANCE);

			$balance = explode('class="priceMoviment">', $httpResponse->getHtml());
			if (is_array($balance) && count($balance) >= 2) {
				$balance = explode('<', $balance[1]);
				$balance = $balance[0];

				return (float) trim(str_replace(array('&euro;',','), array('','.'), $balance));
			} else {
				return false;
			}
		}

		/**
		 * Remove cookie file created
		 */
		public function __destruct() {
			if (file_exists($this->_cookiePath)) {
				unlink($this->_cookiePath);
			}
		}
	}
