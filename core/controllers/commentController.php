<?php


class commentController {

	private function check_access_login()
	{
		if(Auth::$isLogin != true)
		{
			echo json_api(403, 'Vui lòng đăng nhập'); #edit_lang
			exit;
		}
	}

	private function check_permission_comment()
	{
		if(!UserPermission::has('user_comment'))
		{
			echo json_api(403, 'Bạn đã bị cấm sử dụng chức năng bình luận'); #edit_lang
			exit;
		}
	}

	public function list()
	{
		$page = intval(Request::get('page', 1));
		$refid = trim(Request::get('refid', 0));

		if($refid)
		{
			$comment = Comment::get($refid);
			if(!$comment)
			{
				echo json_api(403, 'Bình luận không tồn tại'); #edit_lang
				exit;
			}

			$reverse = filter_var(Request::get('reverse', false), FILTER_VALIDATE_BOOLEAN);

			$data = Comment::list_comments($page, [
				'reverse' => $reverse,
				'refid' => $comment['id'],
				'manga_id' => $comment['manga_id'],
				'chapter_id' => $comment['chapter_id']
			], true);

			if(!$data['total'])
			{
				echo json_api(404, 'Chưa có phản hồi nào!!!'); #edit_lang
				exit;
			}	

		}
		else
		{
			$manga_id = trim(Request::get('manga_id', ''));
			$chapter_id = trim(Request::get('chapter_id', ''));
			$comment_id = trim(Request::get('comment_id', ''));

			$data = Comment::list_comments($page, [
				'manga_id' => $manga_id,
				'chapter_id' => $chapter_id,
				'comment_id' => $comment_id
			]);

			if(!$data['total'])
			{
				echo json_api(404, 'Chưa có bình luận nào!!!'); #edit_lang
				exit;
			}			
		}

		echo json_api(200, count($data['items']).' items', $data); #edit_lang
		exit;
	}

	public function insert()
	{
		$this->check_access_login();
		$this->check_permission_comment();

		$refid = trim(Request::post('refid', 0));
		$text = trim(Request::post('text', ''));

		$manga_id = trim(Request::post('manga_id', ''));
		$chapter_id = trim(Request::post('chapter_id', ''));	

		$manga = Chapter::get([
			'id' => $manga_id
		]);	

		if(!$manga)
		{
			echo json_api(404, 'Manga không tồn tại'); #edit_lang
			exit;
		}

		$chapter = Chapter::get([
			'manga_id' => $manga['id'],
			'id' => $chapter_id
		]);

		$chapter_id = $chapter ? $chapter['id'] : 0;

		$data = [
			'refid' => 0,
			'manga_id' => $manga['id'],
			'chapter_id' => $chapter_id,
			'text' => $text
		];

		if($refid)
		{
			$comment = Comment::get([
				'id' => $refid,
				'manga_id' => $manga['id'],
				'chapter_id' => $chapter_id
			]);

			if(!$comment)
			{
				echo json_api(404, 'Bình luận không tồn tại'); #edit_lang
				exit;
			}
			$data['refid'] = $comment['id'];
		}

		if(Comment::create($data) == true)
		{

			if(isset($comment)) {
				if ($comment['user_id'] != Auth::$data['id']) {
					Notification::create([
						'user_id' => $comment['user_id'],
						'from_user_id' => Auth::$data['id'],
						'type' => Notification::TYPE_COMMENT_REPLY,
						'data' => [
							'manga_id' => $manga['id'],
							'chapter_id' => $chapter_id,
							'comment_id' => $comment['id']
						]
					]);
				}
			}
			preg_match_all('#\[tag=([0-9]+)\]@([^[]+)\[/tag\]#is', $text, $m);
			if (isset($m[1])) {
				$ids_user_tag = [];
				foreach($m[1] as $user_id) {
					if (isset($comment) && $comment['user_id'] == $user_id) {
						continue;
					}
					$ids_user_tag[] = $user_id;
				}
				Notification::create([
					'user_id' => $ids_user_tag,
					'from_user_id' => Auth::$data['id'],
					'type' => Notification::TYPE_COMMENT_TAG,
					'data' => [
						'manga_id' => $manga['id'],
						'chapter_id' => $chapter_id,
						'comment_id' => $comment['id'] ?? Comment::$insert_id
					]
				]);
			}

			echo json_api(200, 'Successfully'); #edit_lang
			exit;
		}

		echo json_api(403, lang('system', 'default_error')); #edit_lang
		exit;
	}

	public function update()
	{
		$this->check_access_login();
		$this->check_permission_comment();

		parse_str(file_get_contents("php://input"), $data);
		$id = isset($data['id']) ? intval($data['id']) : 0;
		$text = isset($data['text']) ? trim($data['text']) : null;

		if($text == "")
		{
			echo json_api(404, 'Vui lòng nhập nội dung bình luận'); #edit_lang
			exit;
		}

		$comment = Comment::get(['id' => $id]);
		if(!$comment)
		{
			echo json_api(403, 'Bình luận không tồn tại'); #edit_lang
			exit;
		}

		if($comment['user_id'] != Auth::$data['id']) {
			echo json_api(403, 'Không thể sửa bình luận người khác'); #edit_lang
			exit;
		}

		if(Comment::update([
			'user_id' => Auth::$id,
			'id' => $comment['id']
		], [
			'text' => $text
		]))
		{
			preg_match_all('#\[tag=([0-9]+)\]@([^[]+)\[/tag\]#is', $text, $m);
			if (isset($m[1])) {
				$ids_user_tag = [];
				$ids_user_tag_old = [];
				preg_match_all('#\[tag=([0-9]+)\]@([^[]+)\[/tag\]#is', $comment['text'], $m_old);
				foreach($m_old[1] as $user_id) {
					$ids_user_tag_old[] = $user_id;
				}

				foreach($m[1] as $user_id) {
					if (!in_array($user_id, $ids_user_tag_old)) {
						$ids_user_tag[] = $user_id;
					}
				}
				Notification::create([
					'user_id' => $ids_user_tag,
					'from_user_id' => Auth::$data['id'],
					'type' => Notification::TYPE_COMMENT_TAG,
					'data' => [
						'manga_id' => $comment['manga_id'],
						'chapter_id' => $comment['chapter_id'],
						'comment_id' => $comment['id']
					]
				]);
			}
			echo json_api(200, 'Cập nhật bình luận thành công'); #edit_lang
			exit;	
		}

		echo json_api(403, lang('system', 'default_error')); #edit_lang
		exit;	
	}

	public function delete()
	{
		$this->check_access_login();
		$this->check_permission_comment();

		parse_str(file_get_contents("php://input"), $data);
		$id = isset($data['id']) ? intval($data['id']) : 0;
		$reason = isset($data['reason']) ? trim($data['reason']) : null;

		
		$comment = Comment::get($id);
		if(!$comment)
		{
			echo json_api(403, 'Bình luận không tồn tại'); #edit_lang
			exit;
		}

		$where = [
			'user_id' => Auth::$id,
			'id' => $comment['id']
		];
		

		$isDelete = false;
		if($comment['user_id'] == Auth::$data['id']) {
			$isDelete = true;
		} else if(UserPermission::has('admin_delete_comment')) {
			$isDelete = true;
			if(!$reason)
			{
				echo json_api(403, 'Lý do không được bỏ trống'); #edit_lang
				exit;
			}
			unset($where['user_id']);
		}
		

		if(!$isDelete) {
			echo json_api(403, 'Không thể xoá bình luận người khác'); #edit_lang
			exit;
		}

		if(Comment::delete($where))
		{
			Comment::delete(['refid' => $id]);
			if($comment['user_id'] != Auth::$data['id']) {
				Notification::create([
					'user_id' => $comment['user_id'],
					'from_user_id' => Auth::$data['id'],
					'type' => Notification::TYPE_COMMENT_DELETE,
					'data' => [
						'comment' => $comment['text'],
						'reason' => $reason,
						'manga_id' => $comment['manga_id'],
						'chapter_id' => $comment['chapter_id']
					]
				]);
			}
			echo json_api(200, 'Xoá thành công bình luận'); #edit_lang
			exit;	
		}

		echo json_api(403, lang('system', 'default_error')); #edit_lang
		exit;	
	}
}





?>