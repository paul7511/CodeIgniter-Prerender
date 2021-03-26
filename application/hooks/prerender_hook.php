<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('prerender'))
{
	function prerender()
	{
		$CI =& get_instance();
		$CI->load->library('prerender_service');
		$response = $CI->prerender_service->handle();
		if(!$response)
		{
			return;
		}
	}
}
