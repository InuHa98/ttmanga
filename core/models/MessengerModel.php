<?php


class Messenger extends Model {

	public static $table = 'core_messenger';
	protected static $primary_key = 'id';
	protected static $timestamps = false;
	protected static $default_join = [];
	protected static $default_selects = [
		'*'
	];
	protected static $order_by = [
		'id' => 'DESC'
	];

	public const MAX_ITEM_INBOX = 20; // số lượng inbox lấy ra
	public const MAX_ITEM_MESSAGE = 10; // số lượng tin nhắn lấy ra


	public static function create_chat($user_id_to = null)
	{
		$user_to = is_array($user_id_to) ? $user_id_to : User::get($user_id_to);
		if(!$user_to)
		{
			return false;
		}

		if(parent::insert([
			'refid' => '',
			'from_user_id' => Auth::$id,
			'to_user_id' => $user_to['id'],
			'is_delete' => '',
			'is_spam_from' => '',
			'is_spam_to' => '',
			'time' => time(),
			'text' => '',
			'seen' => ''
		]) > 0)
		{
			return self::$insert_id;
		}
		return false;
	}

	public static function create_message($chat_id, $message)
	{
		$chat = is_array($chat_id) ? $chat_id : self::get_chat($chat_id);
		if(!$chat || $message == "")
		{
			return false;
		}

		if(parent::insert([
			'refid' => $chat['id'],
			'from_user_id' => Auth::$id,
			'to_user_id' => $chat['from_user_id'] != Auth::$id ? $chat['from_user_id'] : $chat['to_user_id'],
			'is_delete' => '',
			'is_spam_from' => '',
			'is_spam_to' => '',
			'time' => time(),
			'text' => $message,
			'seen' => ''
		]) > 0)
		{
			return true;
		}
		return false;
	}

	public static function count_new_inbox()
	{
		return parent::count([
			'm.refid' => '',
			'lm.seen' => '',
			'lm.is_delete[!]' => Auth::$id,
			'lm.to_user_id' => Auth::$id,
			'AND' => [
				'm.is_spam_to[!]' => Auth::$id,
				'm.is_spam_from[!]' => Auth::$id
			]
		], "SELECT COUNT(*)
			FROM <core_messenger> AS m
			JOIN (
				SELECT <refid>, MAX(<time>) AS <max_time>
				FROM <core_messenger>
				WHERE <refid> != ''
				GROUP BY <refid>
			) AS <last_msg> ON <last_msg.refid> = <m.id>
			JOIN <core_messenger> AS <lm> ON <lm.refid> = <last_msg.refid> AND <lm.time> = <last_msg.max_time>");
	}


	public static function count_message_chat($chat_id)
	{
		$chat = is_array($chat_id) ? $chat_id : self::get_chat($chat_id);
		if(!$chat)
		{
			return 0;
		}

		return parent::count([
			'is_delete[!]' => Auth::$id,
			'refid' => $chat['id']
		]);
	}

	public static function list_messages_chat($chat_id, $count = null, $page = 1)
	{
		$chat = is_array($chat_id) ? $chat_id : self::get_chat($chat_id);
		if(!$chat)
		{
			return null;
		}

		if(!$count)
		{
			$count = self::count_message_chat($chat);
		}

		new Pagination($count, self::MAX_ITEM_MESSAGE, $page);
		$pagination = Pagination::get();

		if($page && $pagination['total_page'] < $page)
		{
			return null;
		}
	
		return array_reverse(parent::list([
			'is_delete[!]' => Auth::$id,
			'refid' => $chat['id'],
			"ORDER" => [
				"time" => "DESC"
			],
			"LIMIT" => [
				$pagination['start'], $pagination['limit']
			]
		]));
	}

	public const SEEN = 'seen';
	public const UNSEEN = 'unseen';

	public static function list_inbox($is_spam = false, $type = null, $keyword = null, $page = null)
	{

		$select = "
		SELECT 
			<{table}.*>,
			<core_user_from.id> AS <user_from_id>,
			<core_user_from.name> AS <user_from_name>,
			<core_user_from.username> AS <user_from_username>,
			<core_user_from.avatar> AS <user_from_avatar>,
			<core_user_from.user_ban> AS <user_from_user_ban>,
			(SELECT <color> FROM <core_roles> WHERE <id> = <core_user_from.role_id>) AS <user_from_role_color>,
			<core_user_to.id> AS <user_to_id>,
			<core_user_to.name> AS <user_to_name>,
			<core_user_to.username> AS <user_to_username>,
			<core_user_to.avatar> AS <user_to_avatar>,
			<core_user_to.user_ban> AS <user_to_user_ban>,
			(SELECT <color> FROM <core_roles> WHERE <id> = <core_user_to.role_id>) AS <user_to_role_color>,
			<last_message.text> AS <text>,
			<last_message.time> AS <time>,
			<last_message.seen> AS <seen>
		FROM <{table}> 
			JOIN <{table}> AS <last_message> ON <last_message.id> = (SELECT <last_id.id> FROM <{table}> AS <last_id> WHERE <last_id.refid> = <{table}.id> ORDER BY <time> DESC LIMIT 1) 
			LEFT JOIN <core_users> AS <core_user_from> ON <last_message.from_user_id> = <core_user_from.id>
			LEFT JOIN <core_users> AS <core_user_to> ON <last_message.to_user_id> = <core_user_to.id>
		";

		$select_search = "
		SELECT count(*) FROM <{table}> 
			JOIN <{table}> AS <last_message> ON <last_message.id> = (SELECT <last_id.id> FROM <{table}> AS <last_id> WHERE <last_id.refid> = <{table}.id> ORDER BY <time> DESC LIMIT 1) 
			LEFT JOIN <core_users> AS <core_user_from> ON <last_message.from_user_id> = <core_user_from.id>
			LEFT JOIN <core_users> AS <core_user_to> ON <last_message.to_user_id> = <core_user_to.id>
		";

		$where = [
			'refid' => '',
			'last_message.is_delete[!]' => Auth::$id,
			'OR' => [
				'to_user_id' => Auth::$id,
				'from_user_id' => Auth::$id
			]
		];

		if($is_spam == true)
		{
			$where['OR #spam'] = [
				'is_spam_to' => Auth::$id,
				'is_spam_from' => Auth::$id
			];
		}
		else
		{
			$where['AND #spam'] = [
				'is_spam_to[!]' => Auth::$id,
				'is_spam_from[!]' => Auth::$id
			];
		}

		switch ($type) {
			case self::SEEN:
				$where['OR #seen'] = [
					'AND' => [
						'last_message.seen[!]' => '',
						'core_user_to.id' => Auth::$id
					],
					'core_user_to.id[!]' => Auth::$id
				];
				break;

			case self::UNSEEN:
				$where['AND'] = [
					'last_message.seen' => '',
					'core_user_to.id' => Auth::$id
				];
				break;
		}

		if($keyword != '')
		{
			$where['OR #search'] = [
				'AND #1' => [
					'core_user_to.username[~]' => '%'.$keyword.'%',
					'core_user_from.id' => Auth::$id
				],
				'AND #2' => [
					'core_user_from.username[~]' => '%'.$keyword.'%',
					'core_user_to.id' => Auth::$id
				]
			];
		}

		$count = parent::count($where, $select_search);

		new Pagination($count, self::MAX_ITEM_INBOX, $page);
		$pagination = Pagination::get();
	
		return [
			'items' => parent::list(array_merge($where, [
				"ORDER" => [
					"last_message.time" => "DESC"
				],
				"LIMIT" => [
					$pagination['start'], $pagination['limit']
				]
			]), $select),
			'pagination' => $pagination
		];
	}

	public static function get_chat($id = null)
	{
		return parent::get([
			'id' => $id,
			'refid' => '',
			'OR' => [
				'to_user_id' => Auth::$id,
				'from_user_id' => Auth::$id
			]
		]);
	}

	public static function last_seen($chat_id)
	{
		$chat = is_array($chat_id) ? $chat_id : parent::get($chat_id);
		if(!$chat)
		{
			return null;
		}

		return parent::get([
			'refid' => $chat['id'],
			'from_user_id' => Auth::$id,
			'seen[>]' => 0,
			'ORDER' => [
				'time' => 'DESC'
			]
		]);
	}

	public static function seen($chat_id)
	{
		$chat = is_array($chat_id) ? $chat_id : self::get_chat($chat_id);
		if(!$chat)
		{
			return false;
		}

		if(parent::update([
			'refid' => $chat['id'],
			'is_delete[!]' => Auth::$id,
			'seen' => '',
			'to_user_id' => Auth::$id
		], ['seen' => time()]) > 0)
		{
			return true;
		}
		return false;
	}

	public static function make_spam($chat_id)
	{
		$chat = is_array($chat_id) ? $chat_id : self::get_chat($chat_id);
		if(!$chat)
		{
			return false;
		}
		
		if($chat['from_user_id'] == Auth::$id)
		{
			$data = ['is_spam_from' => Auth::$id];
		}
		else
		{
			$data = ['is_spam_to' => Auth::$id];
		}


		if(parent::update($chat['id'], $data) > 0)
		{
			return true;
		}
		return false;
	}

	public static function make_inbox($chat_id)
	{
		$chat = is_array($chat_id) ? $chat_id : self::get_chat($chat_id);
		if(!$chat)
		{
			return false;
		}
		
		if($chat['from_user_id'] == Auth::$id)
		{
			$data = ['is_spam_from' => ''];
		}
		else
		{
			$data = ['is_spam_to' => ''];
		}


		if(parent::update($chat['id'], $data) > 0)
		{
			return true;
		}
		return false;
	}

	public static function delete_message($chat_id)
	{
		$chat = is_array($chat_id) ? $chat_id : self::get_chat($chat_id);
		if(!$chat)
		{
			return false;
		}
		
		$delete_forever = parent::delete([
			'refid' => $chat['id'],
			'is_delete[!]' => ['', Auth::$id],
		]);

		$delete_temporary = parent::update([
			'refid' => $chat['id'],
			'is_delete' => ''
		], [
			'is_delete' => Auth::$id
		]);

		if($delete_forever > 0 || $delete_temporary > 0);
		{
			return true;
		}

		return false;
	}

	public static function get_chat_from_user_id($user_id = null)
	{
		$user = is_array($user_id) ? $user_id : User::get($user_id);

		if(!$user || $user['id'] == Auth::$id)
		{
			return false;
		}

		return parent::get([
			'refid' => '',
			'OR' => [
				'AND #1' => [
					'to_user_id' => $user['id'],
					'from_user_id' => Auth::$id
				],
				'AND #2' => [
					'to_user_id' => Auth::$id,
					'from_user_id' => $user['id']
				],
			],
		]);
	}


	protected static function onBeforeInsert($data = null)
	{

	}

	protected static function onSuccessInsert($insert_id = null)
	{

	}

	protected static function onErrorInsert()
	{

	}

	protected static function onBeforeUpdate($data = null, $where = null)
	{

	}

	protected static function onSuccessUpdate($count_items = 0)
	{

	}

	protected static function onErrorUpdate()
	{

	}

	protected static function onBeforeDelete($where = null)
	{

	}

	protected static function onSuccessDelete($count_items = 0)
	{

	}

	protected static function onErrorDelete()
	{

	}
}





?>