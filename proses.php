<?php
	/**
	 * filename: proces_2.php
	 * Copyright (C) 2014 Agung Andika
	 *
	 * Process input jpeg which taken by camera mobile phones (currently Android JB 4.1.x and up) 
	 * 
	 * version 0.1 - 28 December 2014
	 * - 1st release
	 * 
	 * includes example from:
	 * - IPTC.php | Copyright (C) 2004, 2005  Martin Geisler. | Code licensed under GPL v2
	 */

    include 'IPTC.php';
    include 'Log/Log.php';
    include 'qqwry-php/qqwry.php';

    function getIP() /*获取客户端IP*/  
    {  
		if (@$_SERVER["HTTP_X_FORWARDED_FOR"])  
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];  
        else if (@$_SERVER["HTTP_CLIENT_IP"])  
    	   	$ip = $_SERVER["HTTP_CLIENT_IP"];  
        else if (@$_SERVER["REMOTE_ADDR"])  
    		$ip = $_SERVER["REMOTE_ADDR"];  
        else if (@getenv("HTTP_X_FORWARDED_FOR"))  
    		$ip = getenv("HTTP_X_FORWARDED_FOR");  
        else if (@getenv("HTTP_CLIENT_IP"))  
    		$ip = getenv("HTTP_CLIENT_IP");  
        else if (@getenv("REMOTE_ADDR"))  
    		$ip = getenv("REMOTE_ADDR");  
        else  
    		$ip = "Unknown";  
    	
    	return $ip;  
	}

	function getLocation($ip)
	{
		$lo = new qqwry("/home/u/hello/qqwry.dat");
		return $lo->query($ip);
	}

	$target_dir = "uploads/";
	$file_name_before = basename($_FILES["takePictureFieldBefore"]["name"]);

	$target_file_before = $target_dir . date('Y-m-d_H:i:s_') . $file_name_before ;

	$uploadOk = 1;
	$imageFileType_before = pathinfo($target_file_before, PATHINFO_EXTENSION);

	//
	function sexRecognition($file)
	{
	    $nsfw = "/home/u/nsfw/nsfw";//nsfw path
	    $tag_prob = "probability:";
		$tag_time = "time:";

		error_reporting(E_ALL);
		$param = $nsfw . " ".$file;
		$handle = popen($param, 'r');

		while(!feof($handle)) 
		{
			$buffer = fgets($handle);
			echo $buffer;
		}
		pclose($handle);
	}

	//
	// Check if image file is a actual image or fake image
	if(isset($_POST)) 
	{
		$conf = array('mode' => 0600, 'timeFormat' => '%X %x');
		$logger = Log::factory('file', '/var/www/html/cam/out.log', 'SEX CHECK');
		$ip = getIP();
		$lo = getLocation($ip);
		$loutf8 = iconv("UTF-8", "GB2312//IGNORE", $lo[0].$lo[1]);
		$logger->log( $ip.'|'.$loutf8);

		// check existance upload target directory
		if( !is_dir($target_dir) ) 
			@mkdir($target_dir);

	    $check_before = getimagesize($_FILES["takePictureFieldBefore"]["tmp_name"]);
	    if($check_before !== false) 
	    { 
	        $uploadOk = 1;
			if (file_exists($target_file_before)) 
			{
			    unlink($target_file_before);
			}
		} 
		else 
		{
	        echo "File Before is not an image.";
	        $uploadOk = 0;
	    }

	    // Check if $uploadOk is set to 0 by an error
		if ($uploadOk === 0) 
		{
		    echo "Sorry, your file was not uploaded.";
		} 
		else 
		{
			$do_upload_before = move_uploaded_file($_FILES["takePictureFieldBefore"]["tmp_name"], $target_file_before);

		    if ($do_upload_before )
		    {
		    	// write final EXIF TAG
		    	$objIPTC_before = new IPTC($target_file_before);
		    	$objIPTC_before->setValue(IPTC_COPYRIGHT_STRING, "A copyright notice");
		    	$objIPTC_before->setValue(IPTC_CAPTION, "A caption descriptions for this picture [BEFORE]."); 
		    	$objIPTC_before->setValue(IPTC_FIXTURE_IDENTIFIER, "fixture identifier");
		    	$objIPTC_before->setValue(IPTC_CREDIT, "IPTC_CREDIT");
		    	$objIPTC_before->setValue(IPTC_ORIGINATING_PROGRAM, "originating apps");
		    	$objIPTC_before->setValue(IPTC_SOURCE, "IPTC_SOURCE");

		        echo "The file ". basename( $file_name_before ) . " has been uploaded. <br/>";
		        echo "<img src='" . $target_file_before . "' /><br/>";

				echo '<pre>';  
				sexRecognition($target_file_before);
				echo '</pre>';  
			} 
			else 
			{
		        echo "Sorry, there was an error uploading your file.";
		    }
		}
	}
