<?php


class messengerController {

	public const SUBMIT_NAME = 'form_submit';

	public const BLOCK_NEW = 'new';
	public const BLOCK_INBOX = 'inbox';
	public const BLOCK_SPAM = 'spam';

	public const NAME_FORM_ACTION = 'form_action';

	public const ACTION_SEND_MESSAGE = 'send_message';
	public const ACTION_MAKE_SPAM = 'make_spam';
	public const ACTION_MAKE_INBOX = 'make_inbox';
	public const ACTION_DELETE_MESSAGE = 'delete_message';

	public const INPUT_ID_INBOX = 'id_inbox';
	public const INPUT_USER_ID = 'user_id';
	public const INPUT_SEARCH_KEYWORD = 'search_keyword';
	public const INPUT_MESSAGE = 'message';

	public const TIME_BREAK_LINE = 5; //(phút)


	public function __construct()
	{
		if(Auth::$isLogin != true)
		{
			return Router::redirect('*', RouteMap::get('home'));
		}
	}

	public function index($block, $id = null)
	{

		if($block == "")
		{
			return $this->inbox();	
		}

		switch($block)
		{
			case self::BLOCK_NEW:
				return $this->new($id);

			case self::BLOCK_INBOX:
				return $this->inbox($id);
			
			case self::BLOCK_SPAM:
				return $this->inbox($id, true);

			default:
				return ServerErrorHandler::error_404();
		}
	}

	public function new($user_id = null)
	{
		$data_view = [
			'title' => 'Messenger - Tin nhắn mới', #edit_lang
			'error' => null,
			'success' => null
		];

		if(isset($_POST[self::SUBMIT_NAME]))
		{
			$id = intval(Request::post(self::INPUT_USER_ID, null));

			if(Security::validate() == true)
			{
				$where = [
					'id[!]' => Auth::$id,
					'id' => $id
				];

				$find_user = User::get($where);				
			}
		}
		else
		{
			if($user_id != "")
			{
				$find_user = User::get([
					'id' => $user_id,
					'id[!]' => Auth::$id
				]);
			}			
		}


		if(isset($find_user))
		{
			if(!$find_user)
			{
				$data_view['error'] = 'Không tìm thấy thành viên.'; #edit_lang
			}
			else
			{
				$chat = Messenger::get_chat_from_user_id($find_user);
				if($chat)
				{
					return Router::redirect('*', RouteMap::get('messenger', ['block' => self::BLOCK_INBOX, 'id' => $chat['id']]));
				}

				$insert_id = Messenger::create_chat($find_user);
				if($insert_id > 0)
				{
					return Router::redirect('*', RouteMap::get('messenger', ['block' => self::BLOCK_INBOX, 'id' => $insert_id]));
				}
				else
				{
					$data_view['error'] = lang('system', 'default_error');
				}
			}
		}

		return View::render_theme('messenger.new', $data_view);
	}

	public function inbox($id = null, $is_spam = false)
	{
		if($id != "" && !in_array($id, [Messenger::SEEN, Messenger::UNSEEN]))
		{
			return $this->chat($id);
		}

		$title = 'Messenger - inbox'; #edit_lang
		
		$error = null;
		$success = null;

		$type = $id;
		$keyword = Request::get(self::INPUT_SEARCH_KEYWORD, null);

		if(Security::validate() == true)
        {
			$id_inbox = Request::post(self::INPUT_ID_INBOX, null);
			$form_action = Request::post(self::NAME_FORM_ACTION, null);
            switch($form_action)
            {
				case self::ACTION_MAKE_SPAM:
					if(Messenger::make_spam($id_inbox) == true)
					{
						$success = 'Tin nhắn đã được chuyển đến spam.'; #edit_lang
					}
					else
					{
						$error = lang('system', 'default_error');
					}
					break;

				case self::ACTION_MAKE_INBOX:

					if(Messenger::make_inbox($id_inbox) == true)
					{
						$success = 'Tin nhắn đã được chuyển đến hộp tin.'; #edit_lang
					}
					else
					{
						$error = lang('system', 'default_error');
					}
					break;

                case self::ACTION_DELETE_MESSAGE:
					if(Messenger::delete_message($id_inbox) == true)
					{
						$success = lang('system', 'success_delete');
					}
					else
					{
						$error = lang('system', 'default_error');
					}
                    break;
            }            
        }

		$get = Messenger::list_inbox($is_spam, $type, $keyword);
		$inbox_data = $get['items'];
		$pagination = $get['pagination'];

		return View::render_theme('messenger.inbox', compact('title', 'error', 'success', 'is_spam', 'type', 'keyword', 'inbox_data', 'pagination'));
	}
	
	public function chat($id = null)
	{
		$chat = Messenger::get_chat($id);
		if(!$chat)
		{
			return Router::redirect('*', RouteMap::get('messenger', ['block' => self::BLOCK_INBOX]));
		}

		$user_to = User::get(Auth::$id == $chat['to_user_id'] ? $chat['from_user_id'] : $chat['to_user_id']);
		$count = Messenger::count_message_chat($chat);

		$title = 'Messenger - '.$user_to['username']; #edit_lang
		$is_spam = in_array(Auth::$id, [$chat['is_spam_to'], $chat['is_spam_from']]);
		
		$error = null;
		$success = null;

        if(Security::validate() == true)
        {
			$form_action = Request::post(self::NAME_FORM_ACTION, null);
            switch($form_action)
            {
				case self::ACTION_MAKE_SPAM:
					if(Messenger::make_spam($chat) == true)
					{
						$success = 'Tin nhắn đã được chuyển đến spam.'; #edit_lang
						$is_spam = true;
					}
					else
					{
						$error = lang('system', 'default_error');
					}
					break;

				case self::ACTION_MAKE_INBOX:

					if(Messenger::make_inbox($chat) == true)
					{
						$success = 'Tin nhắn đã được chuyển đến hộp tin.'; #edit_lang
						$is_spam = false;
					}
					else
					{
						$error = lang('system', 'default_error');
					}
					break;

                case self::ACTION_DELETE_MESSAGE:
					if(Messenger::delete_message($chat) == true)
					{
						$success = lang('system', 'success_delete');
					}
					else
					{
						$error = lang('system', 'default_error');
					}
                    break;

                case self::ACTION_SEND_MESSAGE:
					$message = trim(Request::post(self::INPUT_MESSAGE, ''));
					
					if($message != "")
					{
						if(Messenger::create_message($chat, $message) == true)
						{
							return Router::reload();
						}
						else
						{
							$error = 'Không thể gửi tin nhắn';
						}
					}

                    break;
            }            
        }

		if($count)
		{
			Messenger::seen($chat);
			View::addData('_count_message', Messenger::count_new_inbox());			
		}

		return View::render_theme('messenger.chat', compact('title', 'error', 'success', 'chat', 'user_to', 'is_spam', 'count'));
	}

	public static function loadMessage() {
		$id = intval(Request::post(InterFaceRequest::ID, 0));
		$chat = Messenger::get_chat($id);
		if(!$chat)
		{
			echo json_api(404, 'not found item'); #edit_lang
			return;
		}

		$count = Messenger::count_message_chat($chat);

		$page = Request::post('page', null);

		$page = abs(intval($page));
		$data_messages = [];
		if($count > 0)
		{
			$messages = Messenger::list_messages_chat($chat, $count, $page);
			if(!$messages)
			{
				echo json_api(404, 'not found item', $data_messages); #edit_lang
				return;
			}

			Messenger::seen($chat);
			$last_seen = Messenger::last_seen($chat);

			$i = 0;
			$old_time = Request::post('old_time', 0);
			$old_user_id = Request::post('old_user_id', null);
			$is_last_seen = false;
			foreach ($messages as $msg) {

				$data = [
					'is_reply' => false,
					'is_last_seen' => false,
					'message' => _echo($msg['text'], true, true),
					'seen' => _time($msg['seen']),
					'time' => _time($msg['time']),
					'break_time' =>  (($msg['time'] - $old_time) > 60 * self::TIME_BREAK_LINE) ? true : false,
					'old_time' => $msg['time'],
					'old_user_id' => $msg['from_user_id']
				];

				$data['break_line'] = $data['break_time'] == false && $old_user_id != null && $msg['from_user_id'] != $old_user_id ? true : false;

				if($msg['to_user_id'] != Auth::$id)
				{
					$data['is_reply'] = true;
					if($msg['seen'] && $msg['seen'] > 0 && $last_seen['id'] === $msg['id'])
					{
						$data['is_last_seen'] = true;	
						$is_last_seen = true;			
					}
				} else {
					if($is_last_seen == true)
					{
						$data_messages[$i - 1]['is_last_seen'] = false;
					}
				}
				$data_messages[$i] = $data;
				$old_time = $msg['time'];
				$old_user_id = $msg['from_user_id'];
				$i++;
			}			
		}
		echo json_api(200, count($data_messages).' items', $data_messages); #edit_lang
	}

	public static function insertHiddenAction($action_name)
	{
		return '<input type="hidden" name="'.self::NAME_FORM_ACTION.'" value="'.$action_name.'">';
	}

	public static function insertHiddenID($id)
	{
		return '<input type="hidden" name="'.self::INPUT_ID_INBOX.'" value="'.$id.'">';
	}
}





?>