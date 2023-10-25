<?php

class addon_turnstile extends flux_addon
{
	function register($manager)
	{
		//register hook
		$manager->bind('register_after_validation', array($this, 'hook_after_validation'));
		$manager->bind('register_before_submit', array($this, 'hook_before_submit'));
		
    //login hook
		$manager->bind('login_after_validation', array($this, 'hook_after_validation'));
		$manager->bind('login_before_submit', array($this, 'hook_before_submit'));
		
		
		//guestpost
		/*
		if ($pun_user['is_guest'])
		{
			$manager->bind('post_after_validation', array($this, 'hook_after_validation'));
			$manager->bind('post_before_submit', array($this, 'hook_before_submit'));
			$manager->bind('quickpost_before_submit', array($this, 'hook_before_submit'));
		}
		*/
	}
	
	function hook_after_validation()
	{
	    global $errors;
	    
		if (empty($errors) && !$this->verify_user_response())
		{
			$errors[] = "Please verify that you are a human being.";
		}
	}
	
	function hook_before_submit()
	{
		?>
        <div class="inform">
            <fieldset>
                <legend>Are you a human?</legend>
                <div class="infldset">
                    <p>Please prove that you're a human being.</p>
                    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
                    <div class="cf-turnstile" data-sitekey="0x4AAAAAAAKxSfnyNCLxW7Ws" data-theme="dark"></div>
                </div>
            </fieldset>
        </div>
		<?php
	}
	
	function verify_user_response()
	{

		if (empty($_POST['cf-turnstile-response'])) return false;
		
		$secret = "0x4AAAAAAAKxSe4EzuUsAs6dQ-cHSt8GMU4";
		$captcha = $_POST['cf-turnstile-response'];
	
    $ip = $_SERVER['REMOTE_ADDR'];

    $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    $data = ['secret' => $secret, 'response' => $captcha, 'remoteip' => $ip];
    $options = [
        'http' => [
            'method' => 'POST',
            'content' => http_build_query($data),
        ],
    ];
        
    $context = stream_context_create($options);
    $response = json_decode(file_get_contents($url, false, $context), true);
        
    return intval($response["success"]) === 1 ? true : false;
	}
}
