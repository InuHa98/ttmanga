<?php


class notificationController {

	public const SUBMIT_NAME = 'form_submit';

	public const TYPE_SEEN = 'seen';
	public const TYPE_UNSEEN = 'unseen';

	public const NAME_FORM_ACTION = 'form_action';

	public const ACTION_MAKE_SEEN = 'make_seen';
	public const ACTION_MAKE_UNSEEN = 'make_unseen';
	public const ACTION_DELETE = 'delete_notifi';

	public const INPUT_ID = 'id_notifi';

	public const MAX_ITEM = 20; 


	public function __construct()
	{
		if(Auth::$isLogin != true)
		{
			return Router::redirect('*', RouteMap::get('home'));
		}
	}

	public function index($id = null)
	{
		if($id != "" && !in_array($id, [self::TYPE_SEEN, self::TYPE_UNSEEN]))
		{
			return $this->get($id);
		}

		$title = 'Tất cả thông báo'; #edit_lang
		
		$error = null;
		$success = null;

		
		if(Security::validate() == true)
        {
			$id_notification = Request::post(self::INPUT_ID, null);
			$form_action = Request::post(self::NAME_FORM_ACTION, null);
            switch($form_action)
            {
				case self::ACTION_MAKE_SEEN:
					if(Notification::make_seen($id_notification) != true)
					{
						$error = lang('system', 'default_error');
					}
					break;

				case self::ACTION_MAKE_UNSEEN:

					if(Notification::make_unseen($id_notification) != true)
					{
						$error = lang('system', 'default_error');
					}
					break;

                case self::ACTION_DELETE:
					if(Notification::delete($id_notification) == true)
					{
						$success = lang('system', 'success_delete');
					}
					else
					{
						$error = lang('system', 'default_error');
					}
                    break;
            }
			View::addData('_count_notification', Notification::count_new());
			View::addData('_notification', Notification::get_list_new());   
        }

		$where = [
			'user_id' => Auth::$id
		];

		$type = $id;
		switch($type) {
			case self::TYPE_SEEN:
				$where['seen'] = Notification::SEEN;
				$title = 'Thông báo đã xem'; #edit_lang
				break;

			case self::TYPE_UNSEEN:
				$where['seen'] = Notification::UNSEEN;
				$title = 'Thông báo chưa xem'; #edit_lang
				break;
			default:
				$type = null;
				break;
		}

		$count = Notification::count(array_merge($where, [
			'seen' => Notification::UNSEEN
		]));

		if($type != self::TYPE_SEEN && $count > 0)
		{
			$title .= ' ('.$count.')';
		}
		

        new Pagination($count, self::MAX_ITEM);
		$pagination = Pagination::get();

        $notification_data = Notification::list(array_merge($where, [
			'ORDER' => [
				'created_at' => 'DESC',
				'seen' => 'ASC'
			],
            'LIMIT' => [
                $pagination['start'], $pagination['limit']
            ]
        ]));

		return View::render_theme('notification.index', compact('title', 'error', 'success', 'type', 'count', 'notification_data', 'pagination'));
	}
	

	private function get($id = null)
	{
		$notification = Notification::get([
			'id' => $id,
			'user_id' => Auth::$id
		]);

		if(!$notification)
		{
			return Router::redirect('*', RouteMap::get('notification'));
		}

		$user_from = User::get($notification['from_user_id']);

		$title = 'Thông báo'; #edit_lang
		
		$error = null;
		$success = null;

		Notification::make_seen($notification['id']);
		View::addData('_count_notification', Notification::count_new());
		View::addData('_notification', Notification::get_list_new());

		$referrer = Request::referer(RouteMap::get('notification'));

		return View::render_theme('notification.get', compact('title', 'error', 'success', 'referrer', 'notification', 'user_from'));
	}

	public static function insertHiddenAction($action_name)
	{
		return '<input type="hidden" name="'.self::NAME_FORM_ACTION.'" value="'.$action_name.'">';
	}

	public static function insertHiddenID($id)
	{
		return '<input type="hidden" name="'.self::INPUT_ID.'" value="'.$id.'">';
	}
}





?>