<?php

class reportController {

	const COOKIE_ONLY_SHOW_REPORT_PENDING = '_osrp';

	const BLOCK_SUBMIT = 'Submit';
	const BLOCK_LIST = 'List';
	const BLOCK_ALL = 'All';

	const ACTION_SUBMIT = 'Submit';
	const ACTION_MAKE_SUCCESS = 'success';
	const ACTION_MAKE_REJECT = 'reject';
	const ACTION_MAKE_PENDING = 'pending';

	public const INPUT_ACTION = '_action';
	public const INPUT_NOTE = 'note';
	public const INPUT_REASON = 'reason';
	public const INPUT_ID = 'id';

	public const PARAM_MANGA_ID = 'manga';
	public const PARAM_CHAPTER_ID = 'chapter';

	public function index($block = null)
	{

		$notification_report = Report::count([
			'status' => Report::STATUS_PENDING
		]);

		$block_view = null;
		switch($block) {

			case self::BLOCK_ALL:
				$block_view = self::block_all();
				break;

			case self::BLOCK_LIST:
				$block_view = self::block_list();
				break;

			case self::BLOCK_SUBMIT:
			default:
				$block = self::BLOCK_SUBMIT;
				$block_view = self::block_submit();
				break;
		}

		return View::render_theme('report.index', compact(
			'block',
			'block_view',
			'notification_report'
		));
	}


	private function block_all() {

		if (!UserPermission::isAdmin()) {
			return ServerErrorHandler::error_404();
		}

		$success = null;
		$error = null;

		$only_show_report_pending = Request::cookie(reportController::COOKIE_ONLY_SHOW_REPORT_PENDING, null);

		$where = [];

        if ($only_show_report_pending == 'true') {
            $where['status'] = Report::STATUS_PENDING;
        }

		if(Security::validate() == true) {
			$action = trim(Request::post(reportController::INPUT_ACTION, ''));
			$id = intval(Request::post(reportController::INPUT_ID, 0));

			$report = Report::get(['id' => $id]);

			if(!$report) {
				$error = 'Không tìm thấy báo lỗi'; #edit_lang
			} else {
				switch($action) {
					case reportController::ACTION_MAKE_SUCCESS:
						if(Report::update($report['id'], [
							'status' => Report::STATUS_COMPLETE,
							'user_update' => Auth::$data['id']
						])) {
							$success = 'Đánh dấu đã xử lý báo lỗi thành công'; #edit_lang
						} else {
							$error = lang('system', 'default_error');
						}
						break;

					case reportController::ACTION_MAKE_REJECT:
						if(Report::update($report['id'], [
							'status' => Report::STATUS_REJECT,
							'user_update' => Auth::$data['id']
						])) {
							$success = 'Đánh dấu đây không phải là lỗi thành công'; #edit_lang
						} else {
							$error = lang('system', 'default_error');
						}
						break;

					case reportController::ACTION_MAKE_PENDING:
						if(Report::update($report['id'], [
							'status' => Report::STATUS_PENDING,
							'user_update' => Auth::$data['id']
						])) {
							$success = 'Đánh dấu đang chờ xử lý báo lỗi thành công'; #edit_lang
						} else {
							$error = lang('system', 'default_error');
						}
						break;
				}
			}
		}

		$count = Report::count($where);
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();
		$lst_report = Report::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));

		$insertHiddenToken = Security::insertHiddenToken();

		return [
			'title' => 'Tất cả báo lỗi', #edit_lang,
			'view' => 'report.block.all',
			'data' => compact(
				'success',
				'error',
				'only_show_report_pending',
				'insertHiddenToken',
				'count',
				'lst_report',
				'pagination'
			)
		];
	}

	private function block_list() {

		$only_show_report_pending = Request::cookie(reportController::COOKIE_ONLY_SHOW_REPORT_PENDING, null);

		$where = [
			'user_id' => Auth::$data['id'] ?? 0
		];

        if ($only_show_report_pending == 'true') {
            $where['status'] = Report::STATUS_PENDING;
        }

		$count = Report::count($where);
		new Pagination($count, App::$pagination_limit);
		$pagination = Pagination::get();
		$lst_report = Report::list(array_merge($where, [
			'LIMIT' => [
				$pagination['start'], $pagination['limit']
			]
		]));

		return [
			'title' => 'Báo lỗi đã gửi', #edit_lang,
			'view' => 'report.block.list',
			'data' => compact(
				'only_show_report_pending',
				'count',
				'lst_report',
				'pagination'
			)
		];
	}

	private function block_submit() {
		$success = null;
		$error = null;

		$manga = null;
		$chapter = null;
		$lst_chapter = [];

		$id_manga = intval(Request::request(self::PARAM_MANGA_ID, 0));
		$id_chapter = intval(Request::request(self::PARAM_CHAPTER_ID, 0));

		if ($id_chapter) {
			$chapter = Chapter::get(['id' => $id_chapter]);
			if ($chapter) {
				$manga = Manga::get(['id' => $chapter['manga_id']]);
			}
		}
		else if ($id_manga) {
			$manga = Manga::get(['id' => $id_manga]);
			$chapter = null;
		} else {
			$manga = null;
			$chapter = null;
		}

		if ($manga) {
			$lst_chapter = Chapter::list(['manga_id' => $manga['id']]);
		}

		$type = trim(Request::post(self::INPUT_REASON, Report::TYPE_DUPLICATE));
		$note = trim(Request::post(self::INPUT_NOTE, ''));

		if (Security::validate()) {
			$id_manga = !empty($manga['id']) ? $manga['id'] : null;
			$id_chapter = !empty($chapter['id']) ? $chapter['id'] : null;

			if (Report::has([
				'type' => $type,
				'user_id' => Auth::$id,
				'manga_id' => $id_manga,
				'chapter_id' => $id_chapter,
				'note' => $note,
			])) {
				$error = 'Đã tồn tại bản báo cáo lỗi'; #edit_lang
			}
			else if (Report::create($type, $id_manga, $id_chapter, $note)) {
				$where_lst_user = ['OR' => []];
				if ($id_manga) {
					$where_lst_user['OR']['[RAW] EXISTS (SELECT 1 FROM <core_mangas> WHERE <core_mangas.id> = :manga_id AND <core_mangas.user_upload> = <{table}.id>)'] = [
						'manga_id' => $id_manga
					];
				}
				if ($id_chapter) {
					$where_lst_user['OR']['[RAW] EXISTS (SELECT 1 FROM <core_chapters> WHERE <core_chapters.id> = :chapter_id AND <core_chapters.user_upload> = <{table}.id>)'] = [
						'chapter_id' => $id_chapter
					];
				}

				if ($where_lst_user['OR']) {
					$lst_user = User::select(['id'])::list($where_lst_user);
					$user_ids = array_filter(array_map(function($o) {
						return $o['id'];
					}, $lst_user));

					Notification::create([
						'user_id' => $user_ids,
						'from_user_id' => Auth::$data['id'],
						'type' => Notification::TYPE_SUBMIT_REPORT
					]);
				}

				$success = 'Gửi báo lỗi thành công'; #edit_lang
			} else {
				$error = 'Gửi báo lỗi thất bại'; #edit_lang
			}
		}

		return [
			'title' => 'Gửi báo lỗi', #edit_lang,
			'view' => 'report.block.submit',
			'data' => compact(
				'success',
				'error',
				'manga',
				'chapter',
				'type',
				'note',
				'lst_chapter'
			)
		];
	}
}




?>