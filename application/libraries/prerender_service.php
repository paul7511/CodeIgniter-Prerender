<?php
/**
 * reference
 * https://github.com/codebar-ag/Laravel-Prerender/blob/master/src/PrerenderMiddleware.php
 */
class Prerender_service extends KK_service
{
	private $enable;
	private $prerender_url;
	private $crawler_user_agents;
	private $prerender_token;
	private $escaped_fragment_name;
	private $whitelist;
	private $blacklist;
	private $allow_parameter;

	public function __construct($config = array())
	{
		parent::__construct();

		$this->enable = $config['enable'];
		$this->prerender_url = $config['prerender_url'];
		$this->crawler_user_agents = $config['crawler_user_agents'];
		$this->prerender_token = $config['prerender_token'];
		$this->escaped_fragment_name = $config['escaped_fragment_name'];
		$this->whitelist = $config['whitelist'];
		$this->blacklist = $config['blacklist'];
		$this->allow_parameter = $config['allow_parameter'];
	}

	public function handle()
	{
		if(!$this->enable)
		{
			return FALSE;
		}

		if($this->should_show_prerendered_page())
		{
			$response = $this->get_prerendered_page_response();
			if(!$response)
			{
				return FALSE;
			}
			echo $response;
			exit();
		}
		return FALSE;
	}

	private function should_show_prerendered_page()
	{
		$user_agent = strtolower($this->input->user_agent());
		if(!$user_agent)
		{
			return FALSE;
		}

		if($this->input->server('REQUEST_METHOD') !== 'GET')
		{
			return FALSE;
		}

		if($this->whitelist)
		{
			$position = $this->router->fetch_class() . '/' . $this->router->fetch_method();
			if(!in_array($position, $this->whitelist) && !in_array($this->uri->uri_string(), $this->whitelist))
			{
				return FALSE;
			}
		}

		// only check blacklist if it is not empty
		if ($this->blacklist)
		{
			// TODO: Implement when needed
		}

		// skip user agent check
		if($this->is_escaped_fragment())
		{
			return TRUE;
		}

		$is_requesting_prerendered_page = FALSE;
		foreach($this->crawler_user_agents as $crawler_user_agents)
		{
			if(strpos($user_agent, $crawler_user_agents) !== FALSE)
			{
				$is_requesting_prerendered_page = TRUE;
				break;
			}
		}

		if(!$is_requesting_prerendered_page)
		{
			return FALSE;
		}

		return TRUE;
	}

	private function is_escaped_fragment()
	{
		parse_str($this->input->server('QUERY_STRING'), $query_string);
		return array_key_exists($this->escaped_fragment_name, $query_string);
	}

	private function get_current_url()
	{
		// prerender url Only the parameters needed for the screen display, Avoid page URL divergence and increase unnecessary cache page
		parse_str($this->input->server('QUERY_STRING'), $query_string);
		$query_string = array_filter($query_string, array($this, 'is_allow_parameter'), ARRAY_FILTER_USE_KEY);

		$current_uri= $this->config->site_url($this->uri->uri_string());
		return empty($query_string) ? $current_uri : $current_uri . '?' . http_build_query($query_string);
	}

	private function is_allow_parameter($parameter)
	{
		$position = $this->router->fetch_class() . '/' . $this->router->fetch_method();
		if(!isset($this->allow_parameter[$position]))
		{
			return FALSE;
		}

		return in_array($parameter, $this->allow_parameter[$position]);
	}

	private function get_prerendered_page_response()
	{
		try
		{
			$curl_options = array(
				CURLOPT_TIMEOUT => 30,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_FAILONERROR => true,
				CURLOPT_HTTPHEADER => [
					'User-Agent:' . $this->input->user_agent(),
					'X-Prerender-Token:' . $this->prerender_token,
					'X-Prerender:1',
				],
			);

			$url = $this->prerender_url . '/' . urlencode($this->get_current_url());

			$ch = curl_init($url);
			curl_setopt_array($ch, $curl_options);
			$result = curl_exec($ch);
			curl_close($ch);

			return $result;
		}
		catch(Exception $e)
		{
			return NULL;
		}
	}
}
