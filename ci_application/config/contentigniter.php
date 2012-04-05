<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Root Domain
|--------------------------------------------------------------------------
|
| Domain name with no sub-domain, nothing preceeding, nothing trainling:
|
|	example.com
|
*/
$config['rootdomain']	= 'domain.com';


/*
|--------------------------------------------------------------------------
| Does the site use ANY subdomains
|--------------------------------------------------------------------------
|
| If the site uses any subdomains, even www this needs to be true.
|
*/
$config['no_subdomain']	= FALSE;

/*
|--------------------------------------------------------------------------
| Template Types
|--------------------------------------------------------------------------
|
| This is the templates required by each page and the order in which they
| are parsed.
|
|	_header,_nav,_article,_aside,_footer
|
*/
$config['page_template_types']	= '_header,_nav,_article,_aside,_footer';

/*
|--------------------------------------------------------------------------
| Static URL
|--------------------------------------------------------------------------
|
| The url to the static content folder (no trailing slash).
|
|	/static
|	//static.example.com
*/
$config['static_url']	= '//static.domain.com';

/*
|--------------------------------------------------------------------------
| Secure Static URL
|--------------------------------------------------------------------------
|
| This is if you use a different static URL for secure vs. non-secure.
|
*/
$config['secure_static_url']	= '//static.domain.com';

/*
|--------------------------------------------------------------------------
| SSL Installed
|--------------------------------------------------------------------------
|
| Is SSL installed?
|
*/
$config['ssl_installed']	= '0';

/*
|--------------------------------------------------------------------------
| Salt
|--------------------------------------------------------------------------
|
| This should be a 60-80 character mixed alpha numeric hash for creating
| extremely secure passwords.
|
|	NosPt6miIinC51FCEPqtZoCS7wZPytqqH3EcTD3NosPt6miIinC51yh4VzFCEPqS7wv5RqWz9WqN1tiv
|
*/
$config['salt'] = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';


/* End of file contentigniter.php */
/* Location: ./system/application/config/contentigniter.php */