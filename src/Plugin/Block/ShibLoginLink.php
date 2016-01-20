<?php

/**
 * @file
 * Contains \Drupal\uw_auth\Plugin\Block\ShibLogin.
 */

namespace Drupal\uw_auth\Plugin\Block;

use Drupal\Core\Url;
use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'ShibLoginLink' block.
 *
 * @Block(
 *  id = "shib_login",
 *  admin_label = @Translation("Shib login link"),
 * )
 */
class ShibLoginLink extends BlockBase {


  /**
   * {@inheritdoc}
   */
  public function build() {
	global $base_url;

	// Preserve Query string
	$query_string = substr(\Drupal::request()->getRequestUri(), strpos(\Drupal::request()->getRequestUri(), '?'));
	if(strpos($query_string, '?') !== false && strlen($query_string) > 1){
		$qs = explode('&', substr($query_string, 1));
	}else{
		$qs = array();
	}
	
	// Get the actual path
	$path = $base_url.substr(\Drupal::request()->getRequestUri(), 0, strpos(\Drupal::request()->getRequestUri(), '?'));
	
	// Add shiblogin to query string
	$qs[] = 'shiblogin=1';	
	
	$path = str_replace('CURRENT_PATH', $path, \Drupal::config('uw_auth.settings')->get('login_link'));
	
	// build the target url
	$target = $base_url.$path.'/?'.implode('&',$qs);

    $build = [];
	$url = 
    $build['shib_login']['#markup'] = '<a href="/Shibboleth.sso/DS?target='.$target.'">Login</a>';
	
	return $build;
  }

}