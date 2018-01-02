<?php

require_once('mimeDecode.php');


class FEP_Email_Parser {
    private $saved_files = array();
    private $debug = false;
    private $raw = '';
    private $decoded;
    private $from;
	private $from_email;
    private $subject;
    private $body;

    public function __construct( $src = 'php://stdin' ){
		$this->readEmail( $src );

    }

    /**
     * @brief Read an email message
     *
     * @param $src (optional) Which file to read the email from. Default is php://stdin for use as a pipe email handler
     *
     * @return An associative array of files saved. The key is the file name, the value is an associative array with size and mime type as keys.
     */
    public function readEmail( $src ){
        // Process the e-mail from stdin
        $fd = fopen($src,'r');
        while(!feof($fd)){ $this->raw .= fread($fd,1024); }

        // Now decode it!
        // http://pear.php.net/manual/en/package.mail.mail-mimedecode.decode.php
        $decoder = new Mail_mimeDecode($this->raw);
        $this->decoded = $decoder->decode(
            Array(
                'decode_headers' => TRUE,
                'include_bodies' => TRUE,
                'decode_bodies' => TRUE,
            )
        );

        // Set $this->from_email and check if it's allowed
        $this->from = $this->decoded->headers['from'];
        $this->from_email = preg_replace('/.*<(.*)>.*/',"$1",$this->from);

        // Set the $this->subject
        $this->subject = $this->decoded->headers['subject'];

        // Find the email body, and any attachments
        // $body_part->ctype_primary and $body_part->ctype_secondary make up the mime type eg. text/plain or text/html
        if(isset($this->decoded->parts) && is_array($this->decoded->parts)){
            foreach($this->decoded->parts as $idx => $body_part){
                $this->decodePart($body_part);
            }
        }

        if(isset($this->decoded->disposition) && $this->decoded->disposition == 'inline'){
            $mimeType = "{$this->decoded->ctype_primary}/{$this->decoded->ctype_secondary}"; 

            if(isset($this->decoded->d_parameters) &&  array_key_exists('filename',$this->decoded->d_parameters)){
                $filename = $this->decoded->d_parameters['filename'];
            }else{
                $filename = 'file';
            }

            $this->saveFile($filename,$this->decoded->body,$mimeType);
            $this->body = "Body was a binary";
        }

        // We might also have uuencoded files. Check for those.
        if(!isset($this->body)){
            if(isset($this->decoded->body)){
                $this->body = $this->decoded->body;
            }else{
                $this->body = "No plain text body found";
            }
        }

        if(preg_match("/begin ([0-7]{3}) (.+)\r?\n(.+)\r?\nend/Us", $this->body) > 0){
            foreach($decoder->uudecode($this->body) as $file){
                // file = Array('filename' => $filename, 'fileperm' => $fileperm, 'filedata' => $filedata)
                $this->saveFile($file['filename'],$file['filedata']);
            }
            // Strip out all the uuencoded attachments from the body
            while(preg_match("/begin ([0-7]{3}) (.+)\r?\n(.+)\r?\nend/Us", $this->body) > 0){
                $this->body = preg_replace("/begin ([0-7]{3}) (.+)\r?\n(.+)\r?\nend/Us", "\n",$this->body);
            }
        }

        // Print messages
        if($this->debug){
            $this->debugMsg();
        }
    }

    /**
     * @brief Decode a single body part of an email message
     *
     * @note Recursive if nested body parts are found
     *
     * @note This is the meat of the script.
     *
     * @param $body_part (required) The body part of the email message, as parsed by Mail_mimeDecode
     */
    private function decodePart($body_part){
        if(array_key_exists('name',$body_part->ctype_parameters)){ // everyone else I've tried
            $filename = $body_part->ctype_parameters['name'];
        }else if($body_part->ctype_parameters && array_key_exists('filename',$body_part->ctype_parameters)){ // hotmail
            $filename = $body_part->ctype_parameters['filename'];
        }else{
            $filename = "file";
        }

        $mimeType = "{$body_part->ctype_primary}/{$body_part->ctype_secondary}"; 

        if($this->debug){
            print "Found body part type $mimeType\n";
        }

        if($body_part->ctype_primary == 'multipart') {
            if(is_array($body_part->parts)){
                foreach($body_part->parts as $ix => $sub_part){
                    $this->decodePart($sub_part);
                }
            }
        } elseif($mimeType == 'text/plain'){
            if(!isset($body_part->disposition)){
                $this->body .= $body_part->body . "\n"; // Gather all plain/text which doesn't have an inline or attachment disposition
            }
        } else {
            $this->saveFile($filename,$body_part->body,$mimeType);
        }
    }
	
	private function get_string_between($str,$from,$to) {
		
		if( strpos($str, $from) === false )
			return '';
			
		$string = substr($str, strpos($str, $from) + strlen($from));
		if (strstr ($string,$to,true) != false) {
			$string = strstr ($string,$to, true);
		}
		return $string;
	}

    /**
     * @brief Save off a single file
     *
     * @param $filename (required) The filename to use for this file
     * @param $contents (required) The contents of the file we will save
     * @param $mimeType (required) The mime-type of the file
     */
    private function saveFile($filename,$content,$mimeType = 'unknown'){
        //$filename = preg_replace('/[^a-zA-Z0-9_.-]/','_',$filename);
		//$filename = preg_replace('/[^\w.-]/','_',$filename);

        // This is for readability for the return e-mail and in the DB
        $this->saved_files[] = array( 'name' => $filename, 'mime' => $mimeType, 'content' => $content );
    }
	
	public function sender_email(){
		return $this->from_email;
	}
	
	public function subject(){
	
		return function_exists( 'mb_convert_encoding' ) ? mb_convert_encoding($this->subject,'UTF-8','UTF-8') : iconv("UTF-8", "UTF-8//TRANSLIT", $this->subject);
	}
	
	public function message_key(){
		$string = $this->get_string_between( $this->subject(), '[MESSAGE KEY-', ']');
		
		if( strpos( $string, '-' ) === false )
			return $string;
		
		$array = explode( '-', $string );
		
		//More than two parts
		if( isset($array[2] ) )
			return '';
			
		return isset( $array[0] ) ? $array[0] : '';
	}
	
	public function blog_id(){
		$string = $this->get_string_between( $this->subject(), '[MESSAGE KEY-', ']');
		
		if( strpos( $string, '-' ) === false )
			return '';
		
		$array = explode( '-', $string );
		
		//More than two parts
		if( isset($array[2] ) )
			return '';
			
		return isset( $array[1] ) ? $array[1] : '';
	}
	
	public function body(){
	
		return function_exists( 'mb_convert_encoding' ) ? mb_convert_encoding($this->body,'UTF-8','UTF-8') : iconv("UTF-8", "UTF-8//TRANSLIT", $this->body);
	}
	
	public function attachments(){
		return $this->saved_files;
	}
	
	/**
   * Strips quotes (older messages) from a message body.
   *
   * This function removes any lines that begin with a quote character (>).
   * Note that quotes in reply bodies will also be removed by this function,
   * so only use this function if you're okay with this behavior.
   *
   * @param $message (string)
   *   The message to be cleaned.
   * @param $plain_text_output (bool)
   *   Set to TRUE to also run the text through strip_tags() (helpful for
   *   cleaning up HTML emails).
   *
   * @return (string)
   *   Same as message passed in, but with all quoted text removed.
   *
   * @see http://stackoverflow.com/a/12611562/100134
   */
   public function clean_body( $plain_text_output = false ) {
   	
	$message = $this->body();
	
     // Strip markup if $plain_text_output is set.
     if ( $plain_text_output ) {
       $message = strip_tags($message);
     }
     // Remove quoted lines (lines that begin with '>').
     $message = preg_replace("/(^\w.+:\n)?(^>.*(\n|$))+/mi", '', $message);
     // Remove lines beginning with 'On' and ending with 'wrote:' (matches
     // Mac OS X Mail, Gmail).
     $message = preg_replace("/^(On).*(wrote:).*$/sm", '', $message);
     // Remove lines like '----- Original Message -----' (some other clients).
     // Also remove lines like '--- On ... wrote:' (some other clients).
     $message = preg_replace("/^---.*$/mi", '', $message);
     // Remove lines like '____________' (some other clients).
     $message = preg_replace("/^____________.*$/mi", '', $message);
     // Remove blocks of text with formats like:
     //   - 'From: Sent: To: Subject:'
     //   - 'From: To: Sent: Subject:'
     //   - 'From: Date: To: Reply-to: Subject:'
     $message = preg_replace("/From:.*^(To:).*^(Subject:).*/sm", '', $message);
     // Remove any remaining whitespace.
     $message = trim($message);
	 
     return $message;
   }

    /**
     * @brief Print a summary of the most recent email read
     */
    private function debugMsg(){
        print "From : $this->from_email\n";
        print "Subject : $this->subject\n";
        print "Body : $this->body\n";
        print "Saved Files : \n";
        print_r($this->saved_files);
    }
}
