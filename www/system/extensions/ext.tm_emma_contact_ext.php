<?php

/**
 * ----------------------------------------------------------------------------
 * ext.tm_emma_contact_ext.php
 *
 * This extension provides MyEmma integration with the Expression Engine
 * contact form.  Users who submit the contact form are added to the specified
 * MyEmma mailing list.  Unsubscribe features are not implemented, so list
 * removal should be handled directly in the MyEmma account and interfaces.
 *
 * Provided by:   Twomile Information Services - http://www.twomile.com
 *  Written by:   Kevin Major <kevin@twomile.com>
 *          On:   3/5/3008
 *
 * -------------------------------- NOTE --------------------------------------
 * All variables should be set using the settings option in the control panel.
 * This form searches for a segment name within the current URL so that it 
 * knows if you are sending mail from a specific page.  
 * So the "Contact form segment name" is the segment of the URL that you want 
 * to send from.  For example if your contact URL is 
 * "www.somesite.com/aboutus/contactpage/"
 * Then you want to set it to "/contactpage/" (no quotes, but include the slashes).
 * ----------------------------------------------------------------------------
 *
 *
 * ----------------------------------------------------------------------------
 * This extension is based on work by:
 *
 *   Michael R. Bagnall <mbagnall@elusivemind.net>
 *   http://elusivemind.net
 *
 * ----------------------------------------------------------------------------
 * THIS SOFTWARE IS PROVIDED "AS IS" AND PROVIDES NO WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, OR WARRANTIES OF ANY
 * KIND. IN NO EVENT SHALL TWOMILE BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, OR CONSEQUENTIAL DAMAGES ARISING IN ANY WAY FROM THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * ----------------------------------------------------------------------------
 */
 
/**
 * ----------------------------------------------------------------------------
 * Main contact class
 * ----------------------------------------------------------------------------
 */

class Tm_emma_contact_ext
{
	var $settings = array();
	var $name = "Twomile Emma Extension";
	var $version = "1.0.1";
	var $description = "Allows you to add email addresses to an Emma mailing list on a contact form";
	var $settings_exist = "y";
	var $docs_url = "http://www.twomile.com";

	
/**
 * ----------------------------------------------------------------------------
 * Extracts settings from EE settings form
 * ----------------------------------------------------------------------------
 */
	 
	function Tm_emma_contact_ext( $settings = "" )
	{
		$this->settings = $settings;
	}//End Tm_emma_contact_ext

	
/**
 * ----------------------------------------------------------------------------
 * Activates extension when user chooses enable in EE control panel
 * ----------------------------------------------------------------------------
 */

		function activate_extension()
	{
		global $DB;
		
		$DB->query(
			$DB->insert_string(
				'exp_extensions', array(
										'extension_id' => '',
										'class' => 'Tm_emma_contact_ext',
										'method' => 'send_to_emma_cp',
										'hook' => 'email_module_send_email_end',
										'settings' => '',
										'priority' => 1,
										'version' => $this->version,
										'enabled' => "y"
								)
			)
		);
	
	}//End activate_extension

/**
 * ----------------------------------------------------------------------------
 * Disables extension when user chooses disable in EE control panel 
 * ----------------------------------------------------------------------------
 */

	function disable_extension()
	{
		global $DB;	
		$DB->query("DELETE FROM exp_extensions WHERE class='Tm_emma_contact_ext'");
	}  	// end disable_extension()
	
	
/**
 * ----------------------------------------------------------------------------
 * Get contact info and send to Emma
 * ----------------------------------------------------------------------------
 */
	function send_to_emma_member( $data )
	{

		$opt_in_value = $this->settings['opt_in'];
		$url = $_POST['URI'];
        $searchstr = $this->settings['contact_form_segment'];
		
		//check if opt-in box was has a value if so only send if checked
		if ($opt_in_value != "")
		{
        	//check to see if checkbox has been checked	
			if (array_key_exists($opt_in_value,$_POST))
			{   
				//make sure this call is coming from the contact form
				if ((stripos($url, $searchstr) !== FALSE))
				{
					// build post
					$post  = "signup_post=".$this->settings['signup_post']."&";
					$post .= "emma_account_id=".$this->settings['account_id']."&";
					$post .= "username=".$this->settings['username']."&";
					$post .= "password=".$this->settings['password']."&";
					$post .= "group[".$this->settings['group']."]=1&";
					$post .= "emma_member_email=".$_POST['from']."&";
					$post .= "new_member_email=".$_POST['from']."&";
					$post .= "no_confirm=".$this->settings['no_confirm'];
		
					// set up the curl request
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL,$this->settings['emma_remote_signup_url']);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
					$response_code = curl_exec ($ch);
					curl_close ($ch); 
				}		
			}
		}
		// if opt-in has no value, then all contact form entries get sent
		else
		{
    	    if ((stripos($url, $searchstr) !== FALSE))
    	    {
			$post  = "signup_post=".$this->settings['signup_post']."&";
			$post .= "emma_account_id=".$this->settings['account_id']."&";
			$post .= "username=".$this->settings['username']."&";
			$post .= "password=".$this->settings['password']."&";
			$post .= "group[".$this->settings['group']."]=1&";
			$post .= "emma_member_email=".$_POST['from']."&";
			$post .= "new_member_email=".$_POST['from']."&";
			$post .= "no_confirm=".$this->settings['no_confirm'];
		
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$this->settings['emma_remote_signup_url']);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			$response_code = curl_exec ($ch);
			curl_close ($ch); 
			}		
		}    		
		
	} //end send_to_emma_member()
	
/**
 * ----------------------------------------------------------------------------
 * Calls Emma send function
 * ----------------------------------------------------------------------------
 */
	function send_to_emma_cp( $member_id, $data )
	{
		$this->send_to_emma_member( $data );
	} //End send_to_emma_cp

/**
 * ----------------------------------------------------------------------------
 * Default settings for EE control panel
 * ----------------------------------------------------------------------------
 */

	function settings()
	{
		$settings = array();
		$settings ['signup_post']	= "99999";
		$settings ['account_id']	= "99999";
		$settings ['username']	= "username";
		$settings ['password']	= "password";
		$settings ['group']	= "9999999";
		$settings ['no_confirm']	= "1";
		$settings ['emma_remote_signup_url'] = "https://app.e2ma.net/app/view:RemoteSignup";
		$settings ['contact_form_segment'] = "/contact/";
		$settings ['opt_in'] = "opt_in";	
		return $settings;
	}//End of settings
} //End of Class
?>