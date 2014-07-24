<?php
/**
 * Send to kindle email
 * @author jwest <jwest@jwest.pl>
 */
class Send {

    /**
     * Your kindle email
     * @var string
     */
    private $_kindle_email;

    /**
     * Your email (must be added on amazon)
     * @var string
     */
    private $_email;

    /**
     * Prepare mail
     * @param string $kindle_email your kindle email
     * @param string $email email for send to kindle
     */
    public function __construct($kindle_email, $email)
    {
        $this->_kindle_email = $kindle_email;
        $this->_email = $email;   
    }

    /**
     * Send file
     * @param string $file path to file
     * @return bool
     */
    public function send($file)
    {
        //prepare file
        $file_size = filesize($file);
        $filename = basename($file);
        $handle = fopen($file, "r");
        $content = fread($handle, $file_size);
        fclose($handle);
        $content = chunk_split(base64_encode($content));

        $uid = md5(uniqid(time())); 

        //generate header for mail
        $header  = "From: News2Kindle <".$this->_email.">\r\n";        
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
        $header .= "This is a multi-part message in MIME format.\r\n";
        $header .= "--".$uid."\r\n";
        $header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
        $header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $header .= "send via News2Kindle script\r\n\r\n";
        $header .= "--".$uid."\r\n";
        $header .= "Content-Type: application/x-mobipocket-ebook; name=\"".$filename."\"\r\n";
        $header .= "Content-Transfer-Encoding: base64\r\n";
        $header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
        $header .= $content."\r\n\r\n";
        $header .= "--".$uid."--";

        //send mail
        return mail( $this->_kindle_email, '[newsToKindle] ' . $filename, "", $header );
    }
}

?>