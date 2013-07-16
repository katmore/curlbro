<?php
/*
 * Purpose: curl convenience class
 *    loads everything into DOM and SimpleXML
 *    has xpath query
 */

class curlbro {
   private $info;
   private $useragent;
   private $simplexml;
   private $document;
   private $dom;
   private $url;
   private $cookiefile;
   private $usedom;

   public function simplexpath($xquery,$demand_result=true) {
      if (!$this->usedom) throw Exception("xpath query impossible: dom not loaded");
      if (false === ($result =   $this->simplexml->xpath($xquery))) {
         throw new Exception(
         'xpath query issue'.
         "\nurl\n".$this->url."\n".
         "\nquery\n".$xquery."\n"
         );
      }
      if ($demand_result && count($result)<1) {
         throw new Exception(
         "xpath query returned no results:".
         "\nurl\n".$this->url."\n".
         "\nquery\n".$xquery."\n"
         );
      }
      return $result;
   }
   public function __destruct() {
      unlink($this->cookiefile);
   }
   public function __construct($useragent='',$usedom=true) {
      if ($useragent=='') {
         $this->useragent = 'curlbro http://github.com/katmore/curlbro';
      } else {
         $this->useragent = $useragent;
      }
      $this->usedom = $usedom;
      $this->cookiefile = tempnam ( sys_get_temp_dir(),"curl");
   }
   public function __get($what) {
      if ($what=='info') return $this->info;
      if ($what=='document') return $this->document;
      if ($what=='dom') return $this->dom;
      if ($what=='url') return $this->url;
      throw new Exception("call to unavailable property: '$what'");
   }
   public function curlexec($url,$referrer='',$savecookies=false,$usecookies=false) {
      $this->url = $url;
      $this->info = array();
      $this->simplexml = null;
      $this->document = '';
      $this->dom = null;
      $ch = curl_init($url);
      if ($referrer == '') $referrer = $url;
      if (!empty($this->useragent)) {
         curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
      }
      if ($savecookies) {
         curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiefile);
      }
      if ($usecookies) {
         curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiefile);
      }
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      if (false === ($this->document = curl_exec($ch))) {
         throw new Exception('issue with curl_exec');
      }
      if (curl_errno($ch)!=0) {
         throw new Exception('got curl error #'.curl_errno($ch));
      }
      if (false === ($this->info = curl_getinfo($ch))) {
         throw new Exception('could not get curlinfo');
      }
      if ($this->info['http_code']!='200') {
         throw new Exception('did not get 200/ok response');
      } 
      curl_close($ch);
      
      
      /*
       * if usedom indicated
       */
      if ($this->usedom) {
         /*
          * tell libxml not be a whiney bitch 
          */
         libxml_use_internal_errors(true);
         
         /*
          * create DOM object from the imdb result page
          */
         $this->dom = new DOMDocument();
         $this->dom->strictErrorChecking = false;
         $this->dom->loadHTML($this->document);
         
         /*
          * create SimpleXML object out of the DOM object
          *    i use SimpleXML here because it's a bit
          *    'simplier' to traverse than DOM (which can be cumbersome)
          */
         $this->simplexml = simplexml_import_dom($this->dom);
      }
   }
}
