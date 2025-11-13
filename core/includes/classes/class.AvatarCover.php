<?php

class AvatarCover {
    public const TYPE_AVATAR = 0;
    public const TYPE_COVER = 1;

    public const FOLDER_PROFILE = 'users';
    public const FOLDER_TEAM = 'teams';

    public const TARGET_PROFILE = 'profile';
    public const TARGET_TEAM = 'team';

    private static $allow_image_extensions = [
		'image/jpg',
		'image/jpeg',
		'image/png',
		'image/gif'
	];

	private static $quality_upload = 75; //chất lượng ảnh

	private static function upload_avatar_cover($target, $input_data_image, $type)
	{

		$dataBase64_image = preg_replace("/data\:(.*?);base64,(.*?)/si", "$2", $input_data_image);			

        $column_name = null;

        switch($type) {
            case self::TYPE_AVATAR:
                $column_name = 'avatar';
                break;
            case self::TYPE_COVER:
                $column_name = 'cover';
                break;
            default:
                return [
                    'type' => 'error',
                    'message' => lang('system', 'default_error')
                ];
        }

		if($dataBase64_image && !is_null($type))
		{

			$image = base64_decode($dataBase64_image);
			
			$finfo = finfo_open();
			$mime_type = finfo_buffer($finfo, $image, FILEINFO_MIME_TYPE);
			finfo_close($finfo);
			
			if(!in_array($mime_type, self::$allow_image_extensions))
			{
				return [
					'type' => 'error',
					'message' => lang('system', 'error_invalid_image_format')
				];
			}
			else
			{

				switch (env(DotEnv::PROFILE_UPLOAD_MODE))
				{
					case 'imgur':
						unset($image);
						$link = upload_imgur($dataBase64_image, get_imgur_client());
						if($link === false)
						{
							return [
								'type' => 'warning',
								'message' => lang('system', 'default_error')
							];
						}
						else
						{
                            switch($target) {
                                case self::TARGET_PROFILE:
                                    if(User::update(Auth::$id, [$column_name => "URL=".$link]) > 0)
                                    {
                                        Auth::$data[$column_name] = "URL=".$link;
                                        self::delete_profile_upload($column_name, null, Auth::$id);
                                        return [
                                            'type' => 'success',
                                            'message' => lang('system', 'success_save')
                                        ];
                                    }
                                    else
                                    {
                                        return [
                                            'type' => 'error',
                                            'message' => lang('system', 'error_save')
                                        ];
                                    }
                                    break;
                                case self::TARGET_TEAM:
                                    if(Team::update(Auth::$data['team_id'], [$column_name => "URL=".$link]) > 0)
                                    {
                                        self::delete_team_upload($column_name, null, Auth::$data['team_id']);
                                        return [
                                            'type' => 'success',
                                            'message' => lang('system', 'success_save')
                                        ];
                                    }
                                    else
                                    {
                                        return [
                                            'type' => 'error',
                                            'message' => lang('system', 'error_save')
                                        ];
                                    }
                                    break;
                                default:
                                    return [
                                        'type' => 'error',
                                        'message' => lang('system', 'default_error')
                                    ];
                            }
						}
					break;
					
					default:
						unset($dataBase64_image);

                        $name_image = null;
                        $folder_name = null;

                        switch($target) {
                            case self::TARGET_PROFILE:
                                $name_image = Auth::$id.'-'.time().'.png';
                                $folder_name = self::FOLDER_PROFILE;
                                break;
                            case self::TARGET_TEAM:
                                $name_image = Auth::$data['team_id'].'-'.time().'.png';
                                $folder_name = self::FOLDER_TEAM;
                                break;
                            default:
                                return [
                                    'type' => 'error',
                                    'message' => lang('system', 'default_error')
                                ];
                        }

						$path = STORAGE_PATH.'/'.$folder_name;

						if(!file_exists($path))
						{
							mkdir($path, 0755);
						}

						if(!file_exists($path.'/'.$column_name))
						{
							mkdir($path.'/'.$column_name, 0755);
						}


						$source = $path.'/'.$column_name.'/'.$name_image;
						if(file_put_contents($source, $image))
						{
							unset($image);

                            switch($target) {
                                case self::TARGET_PROFILE:

                                    if(User::update(Auth::$id, [$column_name => "PATH=".$name_image]) > 0)
                                    {
                                        Auth::$data[$column_name] = "PATH=".$name_image;
                                        self::delete_profile_upload($column_name, $name_image, Auth::$id);
                                        self::update_quality($source, $mime_type);
        
                                        return [
                                            'type' => 'success',
                                            'message' => lang('system', 'success_save')
                                        ];
                                    }
                                    else
                                    {
                                        if(is_file($source))
                                        {
                                            unlink($source);
                                        }
                                        return [
                                            'type' => 'error',
                                            'message' => lang('system', 'error_save')
                                        ];
                                    }
                                    break;
                                case self::TARGET_TEAM:
                                    if(Team::update(Auth::$data['team_id'], [$column_name => "PATH=".$name_image]) > 0)
                                    {
                                        self::delete_team_upload($column_name, $name_image, Auth::$data['team_id']);
                                        self::update_quality($source, $mime_type);
        
                                        return [
                                            'type' => 'success',
                                            'message' => lang('system', 'success_save')
                                        ];
                                    }
                                    else
                                    {
                                        if(is_file($source))
                                        {
                                            unlink($source);
                                        }
                                        return [
                                            'type' => 'error',
                                            'message' => lang('system', 'error_save')
                                        ];
                                    }
                                    break;
                                default:
                                    return [
                                        'type' => 'error',
                                        'message' => lang('system', 'default_error')
                                    ];
                            }

						}
						else
						{
							return [
								'type' => 'warning',
								'message' => lang('system', 'default_error')
							];
						}
					break;
				}
			}
		}
	}

    private static function update_quality($source, $mime_type) {
        if(self::$quality_upload > 0)
        {

            if(self::$quality_upload > 100)
            {
                self::$quality_upload = 100;
            }

            $image = null;
            switch($mime_type) {
                case 'image/jpg':
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($source);
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif($source);
                    break;
                case 'image/png';
                    $image = imagecreatefrompng($source);
                    break;
            }

            if ($image != null)
            {
                imagejpeg($image, $source, self::$quality_upload);
                imagedestroy($image);
            }
        }
    }

    private static function delete_upload($target, $column_name = null, $skip_name = null, $id = null) {

        $folder_name = null;

        switch($target) {
            case self::TARGET_PROFILE:
                $target = is_null($id) ? Auth::$id : $id;
                $folder_name = self::FOLDER_PROFILE;
                break;
            case self::TARGET_TEAM:
                $target = is_null($id) ? Auth::$data['team_id'] : $id;
                $folder_name = self::FOLDER_TEAM;
                break;
            default:
                return;
        }
                
		if($id == "")
		{
			return;
		}

		$oldImages = glob(STORAGE_PATH.'/'.$folder_name.'/'.$column_name.'/'.$id.'-*.*', GLOB_BRACE);

		if($oldImages)
		{
			foreach ($oldImages as $oldImage)
			{
				if(basename($oldImage) !== $skip_name)
				{
					unlink($oldImage);
				}
			}
		}
    }

    public static function upload_avatar_cover_profile($input_data_image, $type) {
        return self::upload_avatar_cover(self::TARGET_PROFILE, $input_data_image, $type);
    }

    public static function upload_avatar_cover_team($input_data_image, $type) {
        return self::upload_avatar_cover(self::TARGET_TEAM, $input_data_image, $type);
    }

	public static function delete_profile_upload($column_name = null, $skip_name = null, $id = null)
	{
		self::delete_upload(self::TARGET_PROFILE, $column_name, $skip_name, $id);
	}

    public static function delete_team_upload($column_name = null, $skip_name = null, $id = null)
	{
		self::delete_upload(self::TARGET_TEAM, $column_name, $skip_name, $id);
	}
}


?>