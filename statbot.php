<?php

class StatBot
{
	private $token;
	private $allowed_users;
	private $hosts;

	//There are some standard messages which can be sent to the user back
	private $GATHERING_MSG = "Gathering data, wait a moment...";
	private $NO_ACCESS_MSG = "Access denied!";

	function __construct($token, $allowed_users, $hosts)
	{
		$this->token = $token;
		$this->allowed_users = $allowed_users;
		$this->hosts = $hosts;
	}

	/*
	 * This function is used to execute Telegram bot API method
	 */
	public function execute_method($method, $reply_json)
	{
		$url = "https://api.telegram.org/bot" . $this->token . "/" . $method;

		$options = array(
			'http' => array(
			     'header'  => "Content-type: application/json\r\n",
			     'method'  => 'POST',
			     'content' => json_encode($reply_json),
		     	)
		    );

		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);

	}

	/*
	 * Function to send message by bot to chat. In our example, we are using it to send replies to the same chat where are we got a message
	 */
	public function send_message($chat_id, $text)
	{
		$reply_text = array(
			'chat_id' => $chat_id,
		      	'text' => $text,
		);

		$this->execute_method("sendMessage", $reply_text);
	}

	/*
	 * This function is needed to replace unicode sentencies like \uXXXX to their appropriate characters. This is used mainly for logging
	 * to the syslog
	 */
	private function remove_unicode_sequences($struct)
	{
		return preg_replace("/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", $struct);
	}

	/*
	 * Drop request to the syslog
	 */
	public function log_last_query()
	{
		$data_log = file_get_contents('php://input');
		syslog(LOG_INFO, "New request: " . $this->remove_unicode_sequences($data_log));
	}

	/*
	 * Ping function to test host availability
	 */
	private function ping($host)
	{
		$exit_status = 0;
		system("ping -c 2 -w 2 " . $host, $exit_status);
		return $exit_status == 0 ? true : false;
	}

	/*
	 * Getting a string line for host, which will be sent to the chat
	 */
	public function get_text_ping($host)
	{
		//\xF0\x9F\x86\x99 and \xF0\x9F\x86\x98 are emoticons UP and SOS
		return $this->ping($host) ? "\xF0\x9F\x86\x99 " . $host . " is up\n" : "\xF0\x9F\x86\x98 " . $host . " is down\n";
	}

	/*
	 * Getting avaliability for hosts and filling it with one message
	 */
	public function get_status()
	{
		$status = "";

		foreach ($this->hosts as $host)
			$status .= $this->get_text_ping($host);

		return $status;
	}

	/*
	 * Main message handler
	 */
	public function handle_message()
	{
		$data = json_decode(file_get_contents('php://input'), true);

		if ($data['message']['text'] == "/status")
		{
			$chat_id = $data['message']['chat']['id'];
			$user = $data['message']['from']['username'];
			if (in_array($user, $this->allowed_users, true))
			{
				$this->send_message($chat_id, $this->GATHERING_MSG);
				$this->send_message($chat_id, $this->get_status());
			}
			else
				$this->send_message($chat_id, $this->NO_ACCESS_MSG);
		}
	}
}

?>
