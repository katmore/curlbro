<?php
/*
 * For Documentation and Latest Version:
 *    https://github.com/katmore/curlbro
 * 
 * Purpose: curl convenience class
 *    loads everything into DOM and SimpleXML
 *    has xpath query method
 *    facilitates use of cookies
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
   private $method;
   private $postdata;
   private $postcount;
   private $postmtype;

   public function setPOST($options="") {
      $this->method = "POST";
      $this->postcount=0;
      $this->postdata='';
      $this->postmtype='';
      if (isset($options['Content-Type'])) {
         if (isset($options['data'])) {
            $this->postdata = $options['data'];
            $this->postmtype=$options['Content-Type'];
         }
      } else
      if (isset($options['fields'])) {
         if (is_array($options['fields'])) {
            
            foreach($options['fields'] as $key=>$value) {
               $this->postdata .= $key.'='.urlencode($value).'&';
               $this->postcount++;
            }
            rtrim($fields_string, '&');
            
         }
      }
   }

   public function __construct($config) {
      /*
       * default values
       */
      $this->useragent = 'curlbro';
      $this->usedom = true;
      $this->method = "GET";
      
      /*
       * apply configuration
       */
      if (!empty($config['useragent'])) {
         $this->useragent = $config['useragent'];
      }
      if (isset($config['usedom'])) {
         if ($config['usedom']===false) {
            $this->usedom = false;
         }
      }
      if (!empty($config['cookiefile'])) {
         $this->cookiefile = $config['cookiefile'];
      } else {
         $this->cookiefile = tempnam ( sys_get_temp_dir(),"curlbro");
      }
      
      $this->config = $config;
   }

   public function simplexpath($xquery,$demand_result=true) {
      if (!$this->usedom) throw new Exception("xpath query impossible: dom not loaded");
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
      @unlink($this->cookiefile);
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
      if ($this->method=="POST") {
         curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postdata);
         if ($this->postcount>0) {
            curl_setopt($ch, CURLOPT_POST, $this->postcount);
         }
         if (!empty($this->postmtype)) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
                'Content-Type: '.$this->postmtype,                                                                                
                'Content-Length: ' . strlen($this->postdata))                                                                       
            );
         } 
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
         throw new Exception('did not get 200/ok response for '.$url);
      } 
      if ($this->document=='') {
         throw new Exception('empty document');
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
