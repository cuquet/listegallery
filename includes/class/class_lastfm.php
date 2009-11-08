<?php
/*********************************************************
*	@desc:		Listen music web database 2.0
*	@authors:	Angel Calleja	(code injection 2008-2009)
*				Cyril Russo 	(code injection 2009)
*				http://www.thehandcoders.com	(code injection 2008)
*	@url:		http://www.raro.dsland.org
*	@license:	licensed under GPL licenses
* 				http://www.gnu.org/licenses/gpl.html
*	@comments:	
**********************************************************/
//if(!defined("VALID_ACL_")) exit("direct access is not allowed.");

class lastFM
{
	function __construct()
	{
		$this->user="willblackmore";
		$this->api_key="96e0589327a3f120074f74dbc8ec6443";
		$this->url = "http://ws.audioscrobbler.com/2.0/?method=album.getinfo&user=[user]&api_key=[api_key]&artist=[artist]&album=[album]";
		$this->data=null;
    }

	function get_lastfm($artist,$album,$type="image")
	{
		$this->url=str_replace("[user]"  , $this->user  , $this->url);
		$this->url=str_replace("[api_key]"  , $this->api_key  , $this->url);
		$this->url=str_replace("[artist]" , urlencode($artist) , $this->url);
		$this->url=str_replace("[album]"  , urlencode($album)  , $this->url);
		if ($stream = fopen($this->url, "r")) 
		{
			$this->data=stream_get_contents($stream);	
			fclose($stream);
		}
		if ($this->data!=null) 
		{
			$imageurl = $this->helper_get_albumurl($this->data);
			if (strlen($imageurl)!=0) 
			{
			   return $this->helper_get_image($imageurl);
			} 
			else 
			{
				return false;
           }
		} 
	}

	function helper_valuein($element_name, $elem_parameters = "", $xml, $content_only = true) {
		if ($xml == false) {
			return false;
		}
		$found = preg_match_all("/<".$element_name.".*". $elem_parameters ."(?:\s+[^>]+)?>(.*?)<\/".$element_name.">/", $xml, $matches);
		
		if ($found != false) 
		{
			if ($content_only) 
			{
				return $matches[1];  //ignore the enclosing tags
			}
			else
			{
				return $matches[0];  //return the full pattern match
			}
		}
		// No match found: return false.
		return false;
	}

	function helper_get_albumurl($p_data) 
	{
		$images = $this->helper_valuein("image","size=\"large\"",$p_data,true);
		return $images[sizeof($images)-1];
	}
	
	function helper_get_image($_url) 
	{
		$fp = fopen($_url, "rb");
		$meta_data = stream_get_meta_data($fp);
		foreach ($meta_data["wrapper_data"] as $v) 
		{
			$pos = strpos ( $v, ":" );
			if ($pos>0) 
			{
				$k = strtolower(str_replace ( " ", "", substr ( $v, 0, $pos ) ));
				$val = trim ( substr ( $v, ( $pos + 1 ) ) );
				$headers[$k] = $val;
				
			}
		}
		fclose($fp);
		$value = array("file" => $_url, "mime" => $headers["content-type"]);
		return $value;
	}
}
?>
