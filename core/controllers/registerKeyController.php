<?php


class registerKeyController {

	public function index()
	{
		$list = RegisterKey::list();
		return View::render('register_key.index', compact('list'));
	}

	public function create()
	{
		$key = trim(Request::post(RegisterKey::INPUT_KEY, ''));
		$note = trim(Request::post(RegisterKey::INPUT_NOTE, ''));

		$success = null;
		$error = null;

		if(isset($_POST['submit']))
		{

			if(RegisterKey::create($key, $note) == true)
			{
				$success = 'Tạo thành công!!!';
			}
			else
			{
				$error = 'Tài thất bại!!!';
			}
		}
		return View::render('register_key.create', compact('key', 'note', 'success', 'error'));
	}

	public function delete()
	{
		echo 'đây là register key';
	}
}





?>