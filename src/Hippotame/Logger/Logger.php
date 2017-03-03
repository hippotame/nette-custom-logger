<?php

 /*
  * Custom Nette log handler
  * Overloading nette \Tracy\Logger
  */

 namespace Hippotame\Logger;

 use Nette;

 /**
  * Description of Logger
  *
  * @author tomas.filip@pharocom.com
  * 
  * @TODO Another mail methods
  */
 class Logger extends \Tracy\Logger {

     /**
      * carriage return type (RFC)
      * OR maybe PHP_EOL
      */
     const EOL = "\r\n";

     /**
      *
      * @var \Nette\Mail\IMailer
      */
     public $mailer;

     /**
      * 
      * @var string name of the directory where errors should be logged 
      */
     public $directory;

     /**
      *
      * @var string
      */
     private $recipients;

     /**
      *
      * From form Email header
      * 
      * @var string
      */
     public $mailfrom;

     /**
      *
      * @var mixed
      */
     public $replayTo = null;

     /**
      *
      * @var string
      */
     public $exceptionFile;

     /**
      *
      * @var string
      */
     public $subject = 'PHP: An error occurred on the server : ';

     /**
      *
      * @var string
      */
     public $from_name = 'appError';

     /**
      *
      * Unique ID (RFC)
      * 
      * @var string
      */
     protected $uid;

     /**
      *
      * Whatever to delete log or not 
      * 
      * @var boolean
      */
     protected $del_log = true;

     /**
      *
      * wheter to use comic or simple php mail function 
      * 
      * @var string
      */
     protected $use_messenger = 'mail';

     /**
      *
      * @var bool internal debug 
      */
     protected $debug;

     /**
      * 
      * @param type $directory
      * @param type $params
      * @param \Nette\Mail\IMailer $mailer
      * @return void
      */
     public function __construct($directory, $params, \Nette\Mail\IMailer $mailer) {


         $this->uid = md5(uniqid(time()));
         $host = $this->getHost();

         $this->directory = $directory;
         $this->recipients = implode(', ', $params['recipients']);
         $this->mailfrom = $params['mailfrom'];
         $this->del_log = $params['del_log'];
         $this->debug = $params['debug'];

         $this->subject = ( is_null($params['subject']) === true ? $this->subject . $host : $params['subject'] );
         $this->replayTo = ( isset($params['replayto']) === true ? $params['replayto'] : '' );
         $this->use_messenger = ( isset($params['use_messanger']) === true ? $params['use_messanger'] : $this->use_messenger );
     }

     /**
      * Logs message or exception to file and sends email notification.
      * @param  string|\Exception|\Throwable
      * @param  int   one of constant ILogger::INFO, WARNING, ERROR (sends email), EXCEPTION (sends email), CRITICAL (sends email)
      * @return string logged error filename
      */
     public function log($exception, $priority = self::INFO) {
         $exceptionFile = parent::log($exception, $priority);
         $this->exceptionFile = realpath($exceptionFile);

         $this->LoggerSendMessage($exception);


         /**
          * try to delete log file if you want to
          */
         if ($this->del_log === true) {
             @unlink($this->exceptionFile);
         }

         return $this->exceptionFile;
     }

     /**
      * 
      * We will not use original sendEmail function, just return true
      * 
      * @param  string|\Exception|\Throwable
      * @return true
      */
     public function sendEmail($message) {
         return true;
     }

     /**
      * 
      * @param string $exception
      * return void
      */
     protected function LoggerSendMessage($exception) {
         $content = chunk_split(base64_encode($this->readAtt()));
         $name = basename($this->exceptionFile);
         $header = "From: " . $this->from_name . " <" . $this->mailfrom . ">" . self::EOL;
         $header .= "Reply-To: " . $this->mailfrom . self::EOL;
         $header .= "MIME-Version: 1.0\r\n";
         $header .= "Content-Type: multipart/mixed; boundary=\"" . $this->uid . "\"";

         $body = "--" . $this->uid . self::EOL;
         $body .= "Content-Type: text/html; charset=UTF-8" . self::EOL;
         $body .= "Content-Transfer-Encoding: 7bit" . self::EOL . self::EOL;
         /**
          * this will be covered, but for text only messengers
          */
         $body .= $this->myFormat($exception) . self::EOL;
         $body .= "--" . $this->uid . self::EOL;
         $body .= "Content-Type: application/octet-stream; name=\"" . $name . "\"" . self::EOL;
         $body .= "Content-Transfer-Encoding: base64" . self::EOL;
         $body .= "Content-Disposition: attachment; filename=\"" . $name . "\"" . self::EOL . self::EOL;
         $body .= $content;
         $body .= "--" . $this->uid . "--";
         mail($this->recipients, $this->subject, $body, $header);
     }

     /**
      * 
      * @return string
      */
     protected function readAtt() {
         $handle = fopen($this->exceptionFile, "r");
         $get = fread($handle, filesize($this->exceptionFile));
         fclose($handle);
         return $get;
     }

     /**
      * 
      * @param string $str
      * @return string
      */
     public function myFormat($str) {
         return preg_replace('#\s*\r?\n\s*#', '<br>', $str);
     }

     /**
      * Get server name and use it in subject;
      * 
      * @return string
      */
     public function getHost() {
         return preg_replace('#[^\w.-]+#', '', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : php_uname('n'));
     }

 }
 